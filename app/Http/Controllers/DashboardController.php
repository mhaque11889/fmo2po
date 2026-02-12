<?php

namespace App\Http\Controllers;

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

        $poUsers = User::where('role', 'po_user')->get();

        return view('dashboard.super-admin', compact('pendingRequests', 'approvedRequests', 'assignedRequests', 'poUsers'));
    }

    private function fmoUserDashboard()
    {
        $userId = auth()->id();

        // Stats for the user's requests
        $stats = [
            'total' => RequirementRequest::where('created_by', $userId)->count(),
            'pending' => RequirementRequest::where('created_by', $userId)->where('status', 'pending')->count(),
            'pending_on_po' => RequirementRequest::where('created_by', $userId)->where('status', 'approved')->count(),
            'assigned' => RequirementRequest::where('created_by', $userId)->whereIn('status', ['assigned', 'in_progress'])->count(),
            'completed' => RequirementRequest::where('created_by', $userId)->where('status', 'completed')->count(),
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

        // Stats for cards
        $stats = [
            'all_mtd' => RequirementRequest::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'pending' => RequirementRequest::where('status', 'pending')->count(),
            'pending_on_po' => RequirementRequest::where('status', 'approved')->count(),
            'po_in_progress' => RequirementRequest::whereIn('status', ['assigned', 'in_progress'])->count(),
            'completed' => RequirementRequest::where('status', 'completed')->count(),
        ];

        return view('dashboard.fmo-admin', compact('pendingRequests', 'stats'));
    }

    private function poAdminDashboard()
    {
        $approvedRequests = RequirementRequest::where('status', 'approved')
            ->with('creator', 'approver')
            ->latest()
            ->paginate(10, ['*'], 'approved_page');

        $poUsers = User::where('role', 'po_user')->get();

        // Stats for cards
        $stats = [
            'ready_to_assign' => RequirementRequest::where('status', 'approved')->count(),
            'assigned' => RequirementRequest::where('status', 'assigned')->count(),
            'in_progress' => RequirementRequest::where('status', 'in_progress')->count(),
            'completed' => RequirementRequest::where('status', 'completed')->count(),
        ];

        return view('dashboard.po-admin', compact('approvedRequests', 'poUsers', 'stats'));
    }

    private function poUserDashboard()
    {
        $userId = auth()->id();

        // Stats for the user's assigned requests (only assigned and in_progress)
        $stats = [
            'total' => RequirementRequest::where('assigned_to', $userId)
                ->whereIn('status', ['assigned', 'in_progress'])->count(),
            'assigned' => RequirementRequest::where('assigned_to', $userId)->where('status', 'assigned')->count(),
            'in_progress' => RequirementRequest::where('assigned_to', $userId)->where('status', 'in_progress')->count(),
        ];

        // Only show assigned and in_progress requests
        $assignedRequests = RequirementRequest::where('assigned_to', $userId)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->with('creator', 'approver', 'assigner')
            ->latest()
            ->limit(10)
            ->get();

        return view('dashboard.po-user', compact('assignedRequests', 'stats'));
    }
}
