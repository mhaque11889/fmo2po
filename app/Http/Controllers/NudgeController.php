<?php

namespace App\Http\Controllers;

use App\Models\RequestNudge;
use App\Models\RequirementRequest;
use Illuminate\Http\Request;

class NudgeController extends Controller
{
    /**
     * Send a nudge to the PO side requesting an update.
     * Available to FMO User (own requests), FMO Admin, and Super Admin.
     */
    public function store(Request $request, RequirementRequest $requirementRequest)
    {
        $user = auth()->user();

        // Must be an assigned or in_progress request with someone assigned
        if (!in_array($requirementRequest->status, ['assigned', 'in_progress']) || !$requirementRequest->assigned_to) {
            return response()->json([
                'success' => false,
                'message' => 'Can only send update requests for assigned or in-progress tasks.'
            ], 422);
        }

        // FMO User can only nudge their own requests; FMO Admin/Super Admin can nudge any
        if ($user->isFmoUser() && $requirementRequest->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only send update requests for your own requests.'
            ], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $nudge = RequestNudge::create([
            'requirement_request_id' => $requirementRequest->id,
            'sent_by' => $user->id,
            'target_user_id' => $requirementRequest->assigned_to,
            'message' => $validated['message'],
        ]);

        $nudge->load('sender', 'target');

        return response()->json([
            'success' => true,
            'message' => 'Update request sent successfully.',
            'nudge' => [
                'id' => $nudge->id,
                'message' => $nudge->message,
                'sender_name' => $nudge->sender->name,
                'target_name' => $nudge->target->name,
                'sent_at' => $nudge->created_at->format('M d, Y \a\t h:i A'),
            ],
        ]);
    }

    /**
     * Acknowledge a nudge (PO side - no reply needed, just confirm receipt).
     */
    public function acknowledge(RequestNudge $nudge)
    {
        if ($nudge->target_user_id !== auth()->id()) {
            abort(403);
        }

        $nudge->update(['acknowledged_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Acknowledged.',
        ]);
    }

    /**
     * Reply to a nudge (PO side - send a message back and auto-acknowledge).
     */
    public function reply(Request $request, RequestNudge $nudge)
    {
        if ($nudge->target_user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'reply' => 'required|string|max:500',
        ]);

        $nudge->update([
            'reply' => $validated['reply'],
            'replied_at' => now(),
            'acknowledged_at' => $nudge->acknowledged_at ?? now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reply sent.',
            'reply' => $validated['reply'],
            'replied_at' => $nudge->replied_at->format('M d, Y \a\t h:i A'),
        ]);
    }

    /**
     * Get count of unread nudges for the current user (PO side).
     * Also returns the latest nudges for the bell dropdown.
     * For FMO users, returns unseen replies instead.
     */
    public function getUnreadCount()
    {
        $user = auth()->user();

        // FMO users see unseen replies to nudges they sent + unseen completions of their requests
        if ($user->isFmoUser() || $user->isFmoAdmin()) {
            // Unseen replies
            $replyQuery = RequestNudge::where('sent_by', $user->id)
                ->whereNotNull('replied_at')
                ->whereNull('reply_seen_at');

            $replies = $replyQuery->with(['request', 'target'])
                ->orderBy('replied_at', 'desc')
                ->limit(5)
                ->get();

            // Unseen completions
            $completionQuery = RequirementRequest::where('status', 'completed')
                ->whereNull('completed_seen_at');

            // FMO User only sees their own; FMO Admin sees all
            if ($user->isFmoUser()) {
                $completionQuery->where('created_by', $user->id);
            }

            $completions = $completionQuery->with(['creator', 'assignee'])
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get();

            $count = $replyQuery->count() + $completionQuery->count();

            // Merge into a single notifications list sorted by time
            $notifications = collect();

            foreach ($replies as $n) {
                $notifications->push([
                    'id' => $n->id,
                    'type' => 'reply',
                    'request_id' => $n->requirement_request_id,
                    'request_item' => $n->request->item ?? 'N/A',
                    'sender_name' => $n->target->name ?? 'Unknown',
                    'message' => $n->reply,
                    'sent_at' => $n->replied_at->diffForHumans(),
                    'request_url' => route('requests.show', $n->requirement_request_id),
                ]);
            }

            foreach ($completions as $r) {
                $notifications->push([
                    'id' => $r->id,
                    'type' => 'completion',
                    'request_id' => $r->id,
                    'request_item' => $r->item ?? 'N/A',
                    'sender_name' => $r->assignee->name ?? 'Unknown',
                    'message' => $r->completion_remarks ?? 'No remarks provided.',
                    'sent_at' => $r->updated_at->diffForHumans(),
                    'request_url' => route('requests.show', $r->id),
                ]);
            }

            return response()->json([
                'count' => $count,
                'mode' => 'fmo',
                'nudges' => $notifications->take(5)->values(),
            ]);
        }

        // PO users and admins see unread incoming nudges
        $nudges = RequestNudge::where('target_user_id', $user->id)
            ->whereNull('acknowledged_at')
            ->with(['request', 'sender'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'count' => RequestNudge::where('target_user_id', $user->id)->whereNull('acknowledged_at')->count(),
            'mode' => 'nudges',
            'nudges' => $nudges->map(fn($n) => [
                'id' => $n->id,
                'type' => 'nudge',
                'request_id' => $n->requirement_request_id,
                'request_item' => $n->request->item ?? 'N/A',
                'sender_name' => $n->sender->name ?? 'Unknown',
                'message' => $n->message,
                'sent_at' => $n->created_at->diffForHumans(),
                'request_url' => route('requests.show', $n->requirement_request_id),
            ]),
        ]);
    }

    /**
     * Mark a nudge reply as seen (FMO side).
     */
    public function markReplySeen(RequestNudge $nudge)
    {
        if ($nudge->sent_by !== auth()->id()) {
            abort(403);
        }

        $nudge->update(['reply_seen_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Mark a completed request as seen by the FMO user.
     */
    public function markCompletedSeen(RequirementRequest $requirementRequest)
    {
        $user = auth()->user();

        // FMO Admins and Super Admins have oversight of all requests.
        // FMO Users may only mark their own requests as seen.
        if (!$user->isFmoAdmin() && !$user->isSuperAdmin()) {
            if ($requirementRequest->created_by !== $user->id) {
                abort(403);
            }
        }

        $requirementRequest->update(['completed_seen_at' => now()]);

        return response()->json(['success' => true]);
    }
}