<?php

namespace App\Http\Controllers;

use App\Events\RequestApproved;
use App\Events\RequestAssigned;
use App\Events\RequestClarificationRequested;
use App\Events\RequestCompleted;
use App\Events\RequestMarkedInProgress;
use App\Events\RequestRejected;
use App\Events\RequestResubmitted;
use App\Events\RequestSubmitted;
use App\Models\RequestAttachment;
use App\Models\RequirementRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RequirementRequestController extends Controller
{
    public function index()
    {
        $requests = RequirementRequest::with(['creator', 'approver', 'assignee', 'assigner'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('requests.index', compact('requests'));
    }

    public function myRequests(?string $status = null)
    {
        $query = RequirementRequest::where('created_by', auth()->id());

        $title = 'All My Requests';

        if ($status) {
            $validStatuses = ['pending', 'approved', 'assigned', 'in_progress', 'completed', 'rejected', 'cancelled', 'clarification_needed'];
            if (in_array($status, $validStatuses)) {
                $query->where('status', $status);
                $statusTitles = [
                    'pending' => 'Pending Approval',
                    'approved' => 'Pending on Purchase Office',
                    'assigned' => 'Assigned to PO User',
                    'in_progress' => 'In Progress',
                    'completed' => 'Completed',
                    'rejected' => 'Rejected',
                    'cancelled' => 'Cancelled',
                    'clarification_needed' => 'Needs Clarification',
                ];
                $title = $statusTitles[$status] ?? ucfirst($status) . ' Requests';
            }
        }

        $requests = $query->with(['creator', 'approver', 'assignee', 'assigner'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('requests.my-requests', compact('requests', 'title', 'status'));
    }

    public function myAssignedRequests(?string $status = null)
    {
        $query = RequirementRequest::where('assigned_to', auth()->id());

        $title = 'All My Assigned Requests';

        if ($status) {
            $validStatuses = ['assigned', 'in_progress', 'completed'];
            if (in_array($status, $validStatuses)) {
                $query->where('status', $status);
                $statusTitles = [
                    'assigned' => 'Assigned Requests',
                    'in_progress' => 'In Progress Requests',
                    'completed' => 'Completed Requests',
                ];
                $title = $statusTitles[$status] ?? ucfirst($status) . ' Requests';
            }
        }

        $requests = $query->with(['creator', 'approver', 'assignee', 'assigner'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('requests.my-assigned', compact('requests', 'title', 'status'));
    }

    public function create()
    {
        return view('requests.create');
    }

    public function store(Request $request)
    {
        $isAjax = $request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest';

        try {
            $rules = [
                'item' => 'required|string|max:255',
                'dimensions' => 'nullable|string|max:255',
                'qty' => 'required|integer|min:1',
                'location' => 'required|string|max:255',
                'remarks' => 'nullable|string',
                'attachments' => 'nullable|array|max:2',
                'attachments.*' => 'file|mimes:pdf,jpg,jpeg,png,gif|max:5120', // 5MB max
            ];

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'errors' => $validator->errors()
                    ], 422);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $validated = $validator->validated();

            $validated['created_by'] = auth()->id();

            // Remove attachments from validated data before creating request
            $attachmentFiles = $request->file('attachments', []);
            unset($validated['attachments']);

            $requirementRequest = RequirementRequest::create($validated);

            // Handle file attachments
            if (!empty($attachmentFiles)) {
                $this->storeAttachments($requirementRequest, $attachmentFiles);
            }

            // Log creation in history
            RequirementRequest::logHistory(
                $requirementRequest->id,
                auth()->id(),
                'created'
            );

            event(new RequestSubmitted($requirementRequest));
            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Request submitted successfully.',
                    'redirect' => route('dashboard')
                ]);
            }

            // Fallback for non-JS
            return redirect()->route('dashboard')
                ->with('success', 'Request submitted successfully.');

        } catch (\Throwable $e) {
            report($e);

            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while submitting your request.'
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'An error occurred while submitting your request.')
                ->withInput();
        }
    }

    /**
     * Store uploaded attachments for a request.
     */
    private function storeAttachments(RequirementRequest $requirementRequest, array $files): void
    {
        foreach ($files as $file) {
            $originalFilename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $mimeType = $file->getMimeType();
            $fileSize = $file->getSize();

            // Generate UUID-based filename for security
            $storedFilename = Str::uuid() . '.' . $extension;

            // Determine file type
            $fileType = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']) ? 'image' : 'pdf';

            // Store file in private directory
            $filePath = $file->storeAs('attachments/' . $requirementRequest->id, $storedFilename, 'local');

            // Create attachment record
            RequestAttachment::create([
                'requirement_request_id' => $requirementRequest->id,
                'original_filename' => $originalFilename,
                'stored_filename' => $storedFilename,
                'file_path' => $filePath,
                'file_type' => $fileType,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
                'uploaded_by' => auth()->id(),
            ]);
        }
    }

    public function show(RequirementRequest $request)
    {
        $user = auth()->user();

        // Authorization checks based on role
        $canView = false;

        if ($user->isSuperAdmin() || $user->isFmoAdmin()) {
            // Super Admin and FMO Admin can view all requests
            $canView = true;
        } elseif ($user->isPoAdmin()) {
            // PO Admin can only view approved, assigned, in_progress, or completed requests
            $canView = in_array($request->status, ['approved', 'assigned', 'in_progress', 'completed']);
        } elseif ($user->isFmoUser()) {
            // FMO users can only view requests they created
            $canView = $request->created_by === $user->id;
        } elseif ($user->isPoUser()) {
            // PO users can only view requests assigned to them
            $canView = $request->assigned_to === $user->id;
        }

        if (!$canView) {
            abort(403, 'You are not authorized to view this request.');
        }

        $request->load('creator', 'approver', 'assignee', 'assigner', 'clarificationRequester', 'history.user', 'attachments', 'nudges.sender', 'nudges.target');

        // Get PO users for assignment dropdown (if user is PO Admin or Super Admin)
        $poUsers = collect();
        if ($user->isPoAdmin() || $user->isSuperAdmin()) {
            $poUsers = User::where('role', 'po_user')->where('is_active', true)->get();
        }

        return view('requests.show', compact('request', 'poUsers'));
    }

    public function edit(RequirementRequest $request)
    {
        $user = auth()->user();

        // FMO User can edit their own pending requests
        if ($user->isFmoUser() && $request->created_by === $user->id && $request->canBeEditedByCreator()) {
            return view('requests.edit', compact('request'));
        }

        // FMO Admin can edit any pending request
        if ($user->isFmoAdmin() && $request->canBeEditedByFmoAdmin()) {
            return view('requests.edit', compact('request'));
        }

        // Super Admin has full access
        if ($user->isSuperAdmin() && $request->isPending()) {
            return view('requests.edit', compact('request'));
        }

        abort(403, 'You cannot edit this request.');
    }

    public function update(Request $request, RequirementRequest $requirementRequest)
    {
        $user = auth()->user();

        // Authorization check
        $canEdit = false;
        if ($user->isFmoUser() && $requirementRequest->created_by === $user->id && $requirementRequest->canBeEditedByCreator()) {
            $canEdit = true;
        } elseif (($user->isFmoAdmin() || $user->isSuperAdmin()) && $requirementRequest->canBeEditedByFmoAdmin()) {
            $canEdit = true;
        }

        if (!$canEdit) {
            abort(403, 'You cannot edit this request.');
        }

        $validated = $request->validate([
            'item' => 'required|string|max:255',
            'dimensions' => 'nullable|string|max:255',
            'qty' => 'required|integer|min:1',
            'location' => 'required|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        // Track changes for history
        $changes = [];
        $trackFields = ['item', 'dimensions', 'qty', 'location', 'remarks'];

        foreach ($trackFields as $field) {
            $oldValue = $requirementRequest->{$field};
            $newValue = $validated[$field] ?? null;

            if ($oldValue != $newValue) {
                $changes[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        // Only log if there were actual changes
        if (!empty($changes)) {
            $requirementRequest->update($validated);

            RequirementRequest::logHistory(
                $requirementRequest->id,
                auth()->id(),
                'edited',
                $changes
            );
        }

        return redirect()->route('requests.show', $requirementRequest)
            ->with('success', 'Request updated successfully.');
    }

    public function approve(RequirementRequest $request, Request $httpRequest)
    {
        if (!auth()->user()->isFmoAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        DB::transaction(function () use ($request) {
            $updated = RequirementRequest::whereKey($request->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

            abort_unless($updated === 1, 422, 'Only pending requests can be approved.');

            RequirementRequest::logHistory($request->id, auth()->id(), 'approved');
            event(new RequestApproved($request->fresh()));
        });

        $isAjax = $httpRequest->ajax() || $httpRequest->wantsJson() || $httpRequest->header('X-Requested-With') === 'XMLHttpRequest';

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => 'Request approved successfully.',
                'redirect' => route('dashboard')
            ]);
        }

        return back()->with('success', 'Request approved successfully.');
    }

    public function reject(RequirementRequest $request, Request $httpRequest)
    {
        if (!auth()->user()->isFmoAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $validated = $httpRequest->validate([
            'rejection_remarks' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($request, $validated) {
            $updated = RequirementRequest::whereKey($request->id)
                ->where('status', 'pending')
                ->update([
                    'status'             => 'rejected',
                    'approved_by'        => auth()->id(),
                    'approved_at'        => now(),
                    'rejection_remarks'  => $validated['rejection_remarks'] ?? null,
                ]);

            abort_unless($updated === 1, 422, 'Only pending requests can be rejected.');

            RequirementRequest::logHistory($request->id, auth()->id(), 'rejected');
            event(new RequestRejected($request->fresh()));
        });

        $isAjax = $httpRequest->ajax() || $httpRequest->wantsJson() || $httpRequest->header('X-Requested-With') === 'XMLHttpRequest';

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => 'Request rejected.',
                'redirect' => route('dashboard')
            ]);
        }

        return back()->with('success', 'Request rejected.');
    }

    public function assign(Request $request, RequirementRequest $requirementRequest)
    {
        if (!auth()->user()->isPoAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $isAjax = $request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest';

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $assignee = User::find($validated['assigned_to']);

        // Allow assignment to PO users OR to PO admin themselves
        if (!$assignee->isPoUser() && !$assignee->isPoAdmin()) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only assign to Purchase Office users or admins.'
                ], 422);
            }
            return back()->with('error', 'Can only assign to Purchase Office users or admins.');
        }

        DB::transaction(function () use ($requirementRequest, $validated, $assignee) {
            $updated = RequirementRequest::whereKey($requirementRequest->id)
                ->whereIn('status', ['approved', 'assigned', 'in_progress'])
                ->update([
                    'status' => 'assigned',
                    'assigned_to' => $validated['assigned_to'],
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                ]);

            abort_unless($updated === 1, 422, 'Only approved or in-progress requests can be assigned.');

            RequirementRequest::logHistory(
                $requirementRequest->id,
                auth()->id(),
                'assigned',
                ['assigned_to' => ['old' => null, 'new' => $assignee->name]]
            );
            event(new RequestAssigned($requirementRequest->fresh()));
        });

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => 'Request assigned successfully.',
                'assignee_name' => $assignee->name,
                'redirect' => route('dashboard')
            ]);
        }

        return back()->with('success', 'Request assigned successfully.');
    }

    public function markInProgress(Request $request, RequirementRequest $requirementRequest)
    {
        $user = auth()->user();

        // Check if user is the assignee (PO user or PO admin who self-assigned)
        if ($requirementRequest->assigned_to !== $user->id) {
            abort(403);
        }

        if (!$requirementRequest->isAssigned()) {
            return back()->with('error', 'Request must be in assigned status to mark as in progress.');
        }

        $validated = $request->validate([
            'progress_remarks' => 'nullable|string|max:1000',
        ]);

        $requirementRequest->update([
            'status' => 'in_progress',
            'progress_remarks' => $validated['progress_remarks'] ?? null,
            'progress_at' => now(),
        ]);

        RequirementRequest::logHistory(
            $requirementRequest->id,
            auth()->id(),
            'in_progress',
            null,
            $validated['progress_remarks'] ?? null
        );

        event(new RequestMarkedInProgress($requirementRequest));

        return back()->with('success', 'Request marked as in progress.');
    }

    public function complete(Request $request, RequirementRequest $requirementRequest)
    {
        $user = auth()->user();

        // Check if user is the assignee
        if ($requirementRequest->assigned_to !== $user->id) {
            abort(403);
        }

        // Can complete from 'assigned' or 'in_progress' status
        if (!$requirementRequest->isAssigned() && !$requirementRequest->isInProgress()) {
            return back()->with('error', 'Request must be assigned or in progress to mark as complete.');
        }

        $validated = $request->validate([
            'completion_remarks' => 'nullable|string|max:1000',
        ]);

        $requirementRequest->update([
            'status' => 'completed',
            'completion_remarks' => $validated['completion_remarks'] ?? null,
            'completed_at' => now(),
        ]);

        RequirementRequest::logHistory(
            $requirementRequest->id,
            auth()->id(),
            'completed',
            null,
            $validated['completion_remarks'] ?? null
        );

        event(new RequestCompleted($requirementRequest));

        return back()->with('success', 'Request marked as completed.');
    }

    public function cancel(Request $request, RequirementRequest $requirementRequest)
    {
        $user = auth()->user();
        $isAjax = $request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest';

        // Only creator can cancel their own pending or clarification_needed requests
        if ($requirementRequest->created_by !== $user->id) {
            abort(403, 'You can only cancel your own requests.');
        }

        if (!$requirementRequest->isPending() && !$requirementRequest->needsClarification()) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending or clarification needed requests can be cancelled.'
                ], 422);
            }
            return back()->with('error', 'Only pending or clarification needed requests can be cancelled.');
        }

        $requirementRequest->update([
            'status' => 'cancelled',
            'clarification_remarks' => null,
            'clarification_requested_by' => null,
            'clarification_requested_at' => null,
        ]);

        RequirementRequest::logHistory(
            $requirementRequest->id,
            auth()->id(),
            'cancelled'
        );

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => 'Request cancelled successfully.',
                'redirect' => route('dashboard')
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Request cancelled successfully.');
    }

    public function destroy(Request $request, RequirementRequest $requirementRequest)
    {
        $user = auth()->user();
        $isAjax = $request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest';

        // Only creator can delete their own rejected requests
        if ($requirementRequest->created_by !== $user->id) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only delete your own requests.'
                ], 403);
            }
            abort(403, 'You can only delete your own requests.');
        }

        if (!$requirementRequest->isRejected()) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only rejected requests can be deleted.'
                ], 422);
            }
            return back()->with('error', 'Only rejected requests can be deleted.');
        }

        // Delete attachments first
        foreach ($requirementRequest->attachments as $attachment) {
            Storage::disk('local')->delete($attachment->file_path);
            $attachment->delete();
        }

        // Delete history
        $requirementRequest->history()->delete();

        // Delete the request
        $requirementRequest->delete();

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => 'Request deleted successfully.',
                'redirect' => route('dashboard')
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Request deleted successfully.');
    }

    public function requestClarification(Request $request, RequirementRequest $requirementRequest)
    {
        if (!auth()->user()->isFmoAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $isAjax = $request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest';

        if (!$requirementRequest->isPending()) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending requests can be sent for clarification.'
                ], 422);
            }
            return back()->with('error', 'Only pending requests can be sent for clarification.');
        }

        $validated = $request->validate([
            'clarification_remarks' => 'required|string|max:1000',
        ]);

        $requirementRequest->update([
            'status' => 'clarification_needed',
            'clarification_remarks' => $validated['clarification_remarks'],
            'clarification_requested_by' => auth()->id(),
            'clarification_requested_at' => now(),
        ]);

        RequirementRequest::logHistory(
            $requirementRequest->id,
            auth()->id(),
            'clarification_requested',
            null,
            $validated['clarification_remarks']
        );

        event(new RequestClarificationRequested($requirementRequest));

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => 'Request sent back for clarification.',
                'redirect' => route('dashboard')
            ]);
        }

        return back()->with('success', 'Request sent back for clarification.');
    }

    public function resubmit(Request $request, RequirementRequest $requirementRequest)
    {
        $user = auth()->user();
        $isAjax = $request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest';

        // Only the creator can resubmit their request
        if ($requirementRequest->created_by !== $user->id) {
            abort(403, 'You can only resubmit your own requests.');
        }

        if (!$requirementRequest->needsClarification()) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only requests needing clarification can be resubmitted.'
                ], 422);
            }
            return back()->with('error', 'Only requests needing clarification can be resubmitted.');
        }

        $validated = $request->validate([
            'item' => 'required|string|max:255',
            'dimensions' => 'nullable|string|max:255',
            'qty' => 'required|integer|min:1',
            'location' => 'required|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        // Track changes for history
        $changes = [];
        $trackFields = ['item', 'dimensions', 'qty', 'location', 'remarks'];

        foreach ($trackFields as $field) {
            $oldValue = $requirementRequest->{$field};
            $newValue = $validated[$field] ?? null;

            if ($oldValue != $newValue) {
                $changes[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        // Update request and change status back to pending
        $requirementRequest->update(array_merge($validated, [
            'status' => 'pending',
            'clarification_remarks' => null,
            'clarification_requested_by' => null,
            'clarification_requested_at' => null,
        ]));

        RequirementRequest::logHistory(
            $requirementRequest->id,
            auth()->id(),
            'resubmitted',
            !empty($changes) ? $changes : null
        );

        event(new RequestResubmitted($requirementRequest));

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => 'Request resubmitted successfully.',
                'redirect' => route('dashboard')
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Request resubmitted successfully.');
    }
}
