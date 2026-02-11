<?php

namespace App\Http\Controllers;

use App\Models\RequirementRequest;
use App\Models\User;
use Illuminate\Http\Request;

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
            $validStatuses = ['pending', 'approved', 'assigned', 'in_progress', 'completed', 'rejected'];
            if (in_array($status, $validStatuses)) {
                $query->where('status', $status);
                $statusTitles = [
                    'pending' => 'Pending Approval',
                    'approved' => 'Pending on Purchase Office',
                    'assigned' => 'Assigned to PO User',
                    'in_progress' => 'In Progress',
                    'completed' => 'Completed',
                    'rejected' => 'Rejected',
                ];
                $title = $statusTitles[$status] ?? ucfirst($status) . ' Requests';
            }
        }

        $requests = $query->with(['creator', 'approver', 'assignee', 'assigner'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('requests.my-requests', compact('requests', 'title', 'status'));
    }

    public function create()
    {
        return view('requests.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item' => 'required|string|max:255',
            'dimensions' => 'nullable|string|max:255',
            'qty' => 'required|integer|min:1',
            'location' => 'required|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();

        $requirementRequest = RequirementRequest::create($validated);

        // Log creation in history
        RequirementRequest::logHistory(
            $requirementRequest->id,
            auth()->id(),
            'created'
        );

        return redirect()->route('dashboard')
            ->with('success', 'Request submitted successfully.');
    }

    public function show(RequirementRequest $request)
    {
        $request->load('creator', 'approver', 'assignee', 'assigner', 'history.user');
        return view('requests.show', compact('request'));
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

    public function approve(RequirementRequest $request)
    {
        if (!auth()->user()->isFmoAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $request->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        RequirementRequest::logHistory(
            $request->id,
            auth()->id(),
            'approved'
        );

        return back()->with('success', 'Request approved successfully.');
    }

    public function reject(RequirementRequest $request)
    {
        if (!auth()->user()->isFmoAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $request->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        RequirementRequest::logHistory(
            $request->id,
            auth()->id(),
            'rejected'
        );

        return back()->with('success', 'Request rejected.');
    }

    public function assign(Request $request, RequirementRequest $requirementRequest)
    {
        if (!auth()->user()->isPoAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $assignee = User::find($validated['assigned_to']);

        // Allow assignment to PO users OR to PO admin themselves
        if (!$assignee->isPoUser() && !$assignee->isPoAdmin()) {
            return back()->with('error', 'Can only assign to Purchase Office users or admins.');
        }

        $requirementRequest->update([
            'status' => 'assigned',
            'assigned_to' => $validated['assigned_to'],
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
        ]);

        RequirementRequest::logHistory(
            $requirementRequest->id,
            auth()->id(),
            'assigned',
            ['assigned_to' => ['old' => null, 'new' => $assignee->name]]
        );

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

        return back()->with('success', 'Request marked as completed.');
    }
}
