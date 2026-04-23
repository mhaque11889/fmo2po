<?php

namespace App\Http\Controllers;

use App\Models\RequestNudge;
use App\Models\RequirementRequest;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return match ($user->role) {
            'super_admin' => $this->superAdminDashboard(),
            'fmo_user' => $this->fmoUserDashboard(),
            'fmo_admin' => $this->fmoAdminDashboard(),
            'po_admin' => $this->poAdminDashboard(),
            'po_user' => $this->poUserDashboard(),
            default => redirect('/'),
        };
    }

    private function superAdminDashboard()
    {
        $pendingRequests = RequirementRequest::where('status', 'pending')
            ->with('creator')
            ->orderByRaw("FIELD(priority, 'urgent', 'normal')")->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'pending_page');

        $approvedRequests = RequirementRequest::where('status', 'approved')
            ->with('creator', 'approver')
            ->orderByRaw("FIELD(priority, 'urgent', 'normal')")->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'approved_page');

        $assignedRequests = RequirementRequest::whereIn('status', ['assigned', 'in_progress', 'completed'])
            ->with('creator', 'approver', 'assignee', 'assigner')
            ->orderByRaw("FIELD(priority, 'urgent', 'normal')")->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'assigned_page');

        $counts = RequirementRequest::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'group_pending' => $counts['group_pending'] ?? 0,
            'pending'       => $counts['pending'] ?? 0,
            'approved'      => $counts['approved'] ?? 0,
            'assigned'      => $counts['assigned'] ?? 0,
            'in_progress'   => $counts['in_progress'] ?? 0,
            'completed'     => $counts['completed'] ?? 0,
            'rejected'      => $counts['rejected'] ?? 0,
            'total'         => $counts->sum(),
        ];

        $poUsers = User::where('role', 'po_user')->where('is_active', true)->get();

        return view('dashboard.super-admin', compact('pendingRequests', 'approvedRequests', 'assignedRequests', 'poUsers', 'stats'));
    }

    private function fmoUserDashboard()
    {
        $user = auth()->user();
        $memberIds = $user->getGroupMemberIds();

        $counts = RequirementRequest::whereIn('created_by', $memberIds)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'total'                => $counts->sum(),
            'group_pending'        => $counts['group_pending'] ?? 0,
            'pending'              => $counts['pending'] ?? 0,
            'clarification_needed' => $counts['clarification_needed'] ?? 0,
            'pending_on_po'        => $counts['approved'] ?? 0,
            'assigned'             => ($counts['assigned'] ?? 0) + ($counts['in_progress'] ?? 0),
            'completed'            => $counts['completed'] ?? 0,
        ];

        // Last 10 requests (own group)
        $requests = RequirementRequest::whereIn('created_by', $memberIds)
            ->with(['items', 'category'])
            ->orderByRaw("FIELD(priority, 'urgent', 'normal')")->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Group approver queue: group_pending requests from this user's managed group
        $groupPendingRequests = $this->getGroupPendingForApprover($user->id);

        return view('dashboard.fmo-user', compact('requests', 'stats', 'groupPendingRequests'));
    }

    private function fmoAdminDashboard()
    {
        $userId = auth()->id();

        // Exclude pending requests that still need group approval
        $pendingRequests = RequirementRequest::where('status', 'pending')
            ->where(function ($q) {
                $q->whereNotNull('group_approved_by')
                  ->orWhere(function ($q2) {
                      // Not in a group with a default approver, AND no category override pending
                      $q2->whereNotIn('created_by', function ($sub) {
                              $sub->select('ugm.user_id')
                                  ->from('user_group_members as ugm')
                                  ->join('user_groups as ug', 'ug.id', '=', 'ugm.user_group_id')
                                  ->where('ug.type', 'fmo')
                                  ->whereNotNull('ug.group_approver_id');
                          })
                          ->whereNotIn('id', function ($sub) {
                              $sub->select('rr.id')
                                  ->from('requirement_requests as rr')
                                  ->join('user_group_members as ugm', 'ugm.user_id', '=', 'rr.created_by')
                                  ->join('user_group_category_approvers as ugca', function ($join) {
                                      $join->on('ugca.user_group_id', '=', 'ugm.user_group_id')
                                           ->whereColumn('ugca.category_id', 'rr.category_id');
                                  })
                                  ->whereNull('rr.group_approved_by');
                          });
                  });
            })
            ->with('creator', 'items')
            ->orderByRaw("FIELD(priority, 'urgent', 'normal')")->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'pending_page');

        $counts = RequirementRequest::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // Count only pending requests that have cleared group approval
        $pendingForFmoAdmin = RequirementRequest::where('status', 'pending')
            ->where(function ($q) {
                $q->whereNotNull('group_approved_by')
                  ->orWhere(function ($q2) {
                      $q2->whereNotIn('created_by', function ($sub) {
                              $sub->select('ugm.user_id')
                                  ->from('user_group_members as ugm')
                                  ->join('user_groups as ug', 'ug.id', '=', 'ugm.user_group_id')
                                  ->where('ug.type', 'fmo')
                                  ->whereNotNull('ug.group_approver_id');
                          })
                          ->whereNotIn('id', function ($sub) {
                              $sub->select('rr.id')
                                  ->from('requirement_requests as rr')
                                  ->join('user_group_members as ugm', 'ugm.user_id', '=', 'rr.created_by')
                                  ->join('user_group_category_approvers as ugca', function ($join) {
                                      $join->on('ugca.user_group_id', '=', 'ugm.user_group_id')
                                           ->whereColumn('ugca.category_id', 'rr.category_id');
                                  })
                                  ->whereNull('rr.group_approved_by');
                          });
                  });
            })
            ->count();

        $stats = [
            'all_mtd'        => RequirementRequest::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'group_pending'  => $counts['group_pending'] ?? 0,
            'pending'        => $pendingForFmoAdmin,
            'pending_on_po'  => $counts['approved'] ?? 0,
            'po_in_progress' => ($counts['assigned'] ?? 0) + ($counts['in_progress'] ?? 0),
            'completed'      => $counts['completed'] ?? 0,
        ];

        // If this FMO admin is also a group approver, show their queue
        $groupPendingRequests = $this->getGroupPendingForApprover($userId);

        return view('dashboard.fmo-admin', compact('pendingRequests', 'stats', 'groupPendingRequests'));
    }

    private function poAdminDashboard()
    {
        $userId = auth()->id();

        $approvedRequests = RequirementRequest::where('status', 'approved')
            ->with('creator', 'approver')
            ->orderByRaw("FIELD(priority, 'urgent', 'normal')")->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'approved_page');

        // Get tasks assigned to the PO Admin themselves
        $myAssignedRequests = RequirementRequest::where('assigned_to', $userId)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->with('creator', 'approver', 'assigner')
            ->orderByRaw("FIELD(priority, 'urgent', 'normal')")->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $poUsers = User::where('role', 'po_user')->where('is_active', true)->get();

        $counts = RequirementRequest::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'ready_to_assign' => $counts['approved'] ?? 0,
            'assigned'        => $counts['assigned'] ?? 0,
            'in_progress'     => $counts['in_progress'] ?? 0,
            'completed'       => $counts['completed'] ?? 0,
        ];

        $unreadNudges = RequestNudge::where('target_user_id', $userId)
            ->whereNull('acknowledged_at')
            ->with(['request', 'sender'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.po-admin', compact('approvedRequests', 'myAssignedRequests', 'poUsers', 'stats', 'unreadNudges'));
    }

    private function poUserDashboard()
    {
        $userId = auth()->id();

        $counts = RequirementRequest::where('assigned_to', $userId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'total'       => ($counts['assigned'] ?? 0) + ($counts['in_progress'] ?? 0),
            'assigned'    => $counts['assigned'] ?? 0,
            'in_progress' => $counts['in_progress'] ?? 0,
        ];

        // Only show assigned and in_progress requests
        $assignedRequests = RequirementRequest::where('assigned_to', $userId)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->with('creator', 'approver', 'assigner')
            ->orderByRaw("FIELD(priority, 'urgent', 'normal')")->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $unreadNudges = RequestNudge::where('target_user_id', $userId)
            ->whereNull('acknowledged_at')
            ->with(['request', 'sender'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.po-user', compact('assignedRequests', 'stats', 'unreadNudges'));
    }

    /**
     * Returns group_pending requests that the given user is responsible for approving.
     * Includes groups where user is the default approver OR a category-specific approver.
     */
    private function getGroupPendingForApprover(int $userId)
    {
        $group = UserGroup::where('type', 'fmo')
            ->where(function ($q) use ($userId) {
                $q->where('group_approver_id', $userId)
                  ->orWhereHas('categoryApprovers', fn($q2) => $q2->where('approver_id', $userId));
            })
            ->with('members')
            ->first();

        if (!$group) {
            return collect();
        }

        $memberIds = $group->members->pluck('id');

        return RequirementRequest::where('status', 'group_pending')
            ->whereIn('created_by', $memberIds)
            ->with(['creator', 'items'])
            ->orderByRaw("FIELD(priority, 'urgent', 'normal')")->orderBy('created_at', 'desc')
            ->get();
    }
}
