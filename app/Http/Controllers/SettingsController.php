<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = auth()->user()->getAllSettings();

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'refresh_interval'         => 'required|integer|min:0|max:600',
            'notification_sound'       => 'required|in:chime,bell,ping,none',
            'notify_on_new_request'    => 'boolean',
            'notify_on_status_change'  => 'boolean',
            'notify_on_task_assigned'  => 'boolean',
            'email_notifications'      => 'required|in:all,key_only,custom,none',
        ]);

        // In-browser notification checkboxes
        $validated['notify_on_new_request']   = $request->has('notify_on_new_request');
        $validated['notify_on_status_change'] = $request->has('notify_on_status_change');
        $validated['notify_on_task_assigned'] = $request->has('notify_on_task_assigned');

        // Email notification custom flags
        $emailBooleans = [
            'email_on_approved', 'email_on_rejected', 'email_on_clarification',
            'email_on_assigned', 'email_on_in_progress', 'email_on_completed',
            'email_on_new_request', 'email_on_resubmitted', 'email_on_po_assigned',
            'email_on_ready_to_assign', 'email_on_assigned_to_me',
        ];
        foreach ($emailBooleans as $key) {
            $validated[$key] = $request->has($key);
        }

        auth()->user()->update(['settings' => $validated]);

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }

    /**
     * API endpoint to get current task counts for notification checking
     */
    public function getTaskCounts()
    {
        $user = auth()->user();
        $counts = [];

        if ($user->isFmoUser()) {
            $raw = \App\Models\RequirementRequest::where('created_by', $user->id)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $counts = [
                'pending'   => $raw['pending'] ?? 0,
                'approved'  => $raw['approved'] ?? 0,
                'assigned'  => ($raw['assigned'] ?? 0) + ($raw['in_progress'] ?? 0),
                'completed' => $raw['completed'] ?? 0,
            ];
        } elseif ($user->isFmoAdmin() || $user->isSuperAdmin()) {
            $raw = \App\Models\RequirementRequest::selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $counts = [
                'pending'   => $raw['pending'] ?? 0,
                'approved'  => $raw['approved'] ?? 0,
                'assigned'  => ($raw['assigned'] ?? 0) + ($raw['in_progress'] ?? 0),
                'completed' => $raw['completed'] ?? 0,
            ];
        } elseif ($user->isPoAdmin()) {
            $raw = \App\Models\RequirementRequest::selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $counts = [
                'approved'  => $raw['approved'] ?? 0,
                'assigned'  => ($raw['assigned'] ?? 0) + ($raw['in_progress'] ?? 0),
                'completed' => $raw['completed'] ?? 0,
            ];
        } elseif ($user->isPoUser()) {
            $raw = \App\Models\RequirementRequest::where('assigned_to', $user->id)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $counts = [
                'assigned'    => $raw['assigned'] ?? 0,
                'in_progress' => $raw['in_progress'] ?? 0,
                'completed'   => $raw['completed'] ?? 0,
            ];
        }

        return response()->json([
            'counts' => $counts,
            'total' => array_sum($counts),
        ]);
    }
}
