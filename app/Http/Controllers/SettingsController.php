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
            $counts = [
                'pending' => \App\Models\RequirementRequest::where('created_by', $user->id)
                    ->where('status', 'pending')->count(),
                'approved' => \App\Models\RequirementRequest::where('created_by', $user->id)
                    ->where('status', 'approved')->count(),
                'assigned' => \App\Models\RequirementRequest::where('created_by', $user->id)
                    ->whereIn('status', ['assigned', 'in_progress'])->count(),
                'completed' => \App\Models\RequirementRequest::where('created_by', $user->id)
                    ->where('status', 'completed')->count(),
            ];
        } elseif ($user->isFmoAdmin() || $user->isSuperAdmin()) {
            $counts = [
                'pending' => \App\Models\RequirementRequest::where('status', 'pending')->count(),
                'approved' => \App\Models\RequirementRequest::where('status', 'approved')->count(),
                'assigned' => \App\Models\RequirementRequest::whereIn('status', ['assigned', 'in_progress'])->count(),
                'completed' => \App\Models\RequirementRequest::where('status', 'completed')->count(),
            ];
        } elseif ($user->isPoAdmin()) {
            $counts = [
                'approved' => \App\Models\RequirementRequest::where('status', 'approved')->count(),
                'assigned' => \App\Models\RequirementRequest::whereIn('status', ['assigned', 'in_progress'])->count(),
                'completed' => \App\Models\RequirementRequest::where('status', 'completed')->count(),
            ];
        } elseif ($user->isPoUser()) {
            $counts = [
                'assigned' => \App\Models\RequirementRequest::where('assigned_to', $user->id)
                    ->where('status', 'assigned')->count(),
                'in_progress' => \App\Models\RequirementRequest::where('assigned_to', $user->id)
                    ->where('status', 'in_progress')->count(),
                'completed' => \App\Models\RequirementRequest::where('assigned_to', $user->id)
                    ->where('status', 'completed')->count(),
            ];
        }

        return response()->json([
            'counts' => $counts,
            'total' => array_sum($counts),
        ]);
    }
}
