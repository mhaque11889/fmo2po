<?php

namespace App\Http\Controllers;

use App\Models\RequestNudge;
use App\Models\RequirementRequest;
use App\Models\User;
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
            ->latest()
            ->paginate(10, ['*'], 'pending_page');

        $approvedRequests = RequirementRequest::where('status', 'approved')
            ->with('creator', 'approver')
            ->latest()
            ->paginate(10, ['*'], 'approved_page');

        $assignedRequests = RequirementRequest::whereIn('status', ['assigned', 'in_progress', 'completed'])
            ->with('creator', 'approver', 'assignee', 'assigner')
            ->latest()
            ->paginate(10, ['*'], 'assigned_page');

        $counts = RequirementRequest::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'pending'     => $counts['pending'] ?? 0,
            'approved'    => $counts['approved'] ?? 0,
            'assigned'    => $counts['assigned'] ?? 0,
            'in_progress' => $counts['in_progress'] ?? 0,
            'completed'   => $counts['completed'] ?? 0,
            'rejected'    => $counts['rejected'] ?? 0,
            'total'       => $counts->sum(),
        ];

        $poUsers = User::where('role', 'po_user')->where('is_active', true)->get();

        return view('dashboard.super-admin', compact('pendingRequests', 'approvedRequests', 'assignedRequests', 'poUsers', 'stats'));
    }

    private function fmoUserDashboard()
    {
        $userId = auth()->id();

        $counts = RequirementRequest::where('created_by', $userId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'total'                => $counts->sum(),
            'pending'              => $counts['pending'] ?? 0,
            'clarification_needed' => $counts['clarification_needed'] ?? 0,
            'pending_on_po'        => $counts['approved'] ?? 0,
            'assigned'             => ($counts['assigned'] ?? 0) + ($counts['in_progress'] ?? 0),
            'completed'            => $counts['completed'] ?? 0,
        ];

        // Last 10 requests
        $requests = RequirementRequest::where('created_by', $userId)
            ->latest()
            ->limit(10)
            ->get();

        return view('dashboard.fmo-user', compact('requests', 'stats'));
    }

    private function fmoAdminDashboard()
    {
        $pendingRequests = RequirementRequest::where('status', 'pending')
            ->with('creator')
            ->latest()
            ->paginate(10, ['*'], 'pending_page');

        $counts = RequirementRequest::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'all_mtd'        => RequirementRequest::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'pending'        => $counts['pending'] ?? 0,
            'pending_on_po'  => $counts['approved'] ?? 0,
            'po_in_progress' => ($counts['assigned'] ?? 0) + ($counts['in_progress'] ?? 0),
            'completed'      => $counts['completed'] ?? 0,
        ];

        return view('dashboard.fmo-admin', compact('pendingRequests', 'stats'));
    }

    private function poAdminDashboard()
    {
        $userId = auth()->id();

        $approvedRequests = RequirementRequest::where('status', 'approved')
            ->with('creator', 'approver')
            ->latest()
            ->paginate(10, ['*'], 'approved_page');

        // Get tasks assigned to the PO Admin themselves
        $myAssignedRequests = RequirementRequest::where('assigned_to', $userId)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->with('creator', 'approver', 'assigner')
            ->latest()
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
            ->latest()
            ->limit(10)
            ->get();

        $unreadNudges = RequestNudge::where('target_user_id', $userId)
            ->whereNull('acknowledged_at')
            ->with(['request', 'sender'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.po-user', compact('assignedRequests', 'stats', 'unreadNudges'));
    }
}
