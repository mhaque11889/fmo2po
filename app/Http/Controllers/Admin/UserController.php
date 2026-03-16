<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RequirementRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    private function getAllowedRoles(): array
    {
        $currentUser = auth()->user();

        if ($currentUser->isSuperAdmin()) {
            return ['super_admin', 'fmo_admin', 'fmo_user', 'po_admin', 'po_user'];
        }

        if ($currentUser->isFmoAdmin()) {
            return ['fmo_admin', 'fmo_user'];
        }

        if ($currentUser->isPoAdmin()) {
            return ['po_admin', 'po_user'];
        }

        return [];
    }

    public function index(Request $request)
    {
        $currentUser = auth()->user();
        $allowedRoles = $this->getAllowedRoles();
        $showInactive = $request->query('show_inactive', false) && $currentUser->isSuperAdmin();

        if ($currentUser->isSuperAdmin()) {
            $query = User::query();
            if ($showInactive) {
                $query->where('is_active', false);
            } else {
                $query->where('is_active', true);
            }
            $users = $query->orderBy('name')->paginate(20);
        } else {
            $users = User::where('is_active', true)
                ->whereIn('role', $allowedRoles)
                ->orderBy('name')
                ->paginate(20);
        }

        return view('admin.users.index', compact('users', 'allowedRoles', 'showInactive'));
    }

    public function updateRole(Request $request, User $user)
    {
        $currentUser = auth()->user();
        $allowedRoles = $this->getAllowedRoles();

        // Check if current admin can manage this user
        if (!$currentUser->isSuperAdmin() && !in_array($user->role, $allowedRoles)) {
            abort(403, 'You cannot manage this user.');
        }

        $validated = $request->validate([
            'role' => 'required|in:' . implode(',', $allowedRoles),
        ]);

        $user->update(['role' => $validated['role']]);

        return back()->with('success', "User role updated to {$validated['role']}");
    }

    public function create()
    {
        $allowedRoles = $this->getAllowedRoles();
        return view('admin.users.create', compact('allowedRoles'));
    }

    public function store(Request $request)
    {
        $allowedRoles = $this->getAllowedRoles();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:' . implode(',', $allowedRoles),
        ]);

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $currentUser = auth()->user();
        $allowedRoles = $this->getAllowedRoles();

        // Check if current admin can manage this user
        if (!$currentUser->isSuperAdmin() && !in_array($user->role, $allowedRoles)) {
            abort(403, 'You cannot manage this user.');
        }

        return view('admin.users.edit', compact('user', 'allowedRoles'));
    }

    public function update(Request $request, User $user)
    {
        $currentUser = auth()->user();
        $allowedRoles = $this->getAllowedRoles();

        // Check if current admin can manage this user
        if (!$currentUser->isSuperAdmin() && !in_array($user->role, $allowedRoles)) {
            abort(403, 'You cannot manage this user.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:' . implode(',', $allowedRoles),
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Delete a specific user.
     * Admins can deactivate users within their allowed roles.
     */
    public function destroy(User $user)
    {
        $currentUser = auth()->user();
        $allowedRoles = $this->getAllowedRoles();

        // Check if current admin can manage this user
        if (!$currentUser->isSuperAdmin() && !in_array($user->role, $allowedRoles)) {
            abort(403, 'You cannot deactivate this user.');
        }

        // Prevent deactivating self
        if ($user->id === $currentUser->id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        // Check if user can be deactivated
        [$canDeactivate, $reason] = $user->canBeDeactivated();

        if (!$canDeactivate) {
            return back()->with('error', $reason);
        }

        $userName = $user->name;
        $user->is_active = false;
        $user->save();

        return back()->with('success', "User '{$userName}' has been deactivated successfully.");
    }

    /**
     * Reactivate a deactivated user.
     * Super admin only.
     */
    public function activate(User $user)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'You cannot reactivate users.');
        }

        // Check if already active
        if ($user->is_active) {
            return back()->with('info', 'User is already active.');
        }

        $userName = $user->name;
        $user->is_active = true;
        $user->save();

        return back()->with('success', "User '{$userName}' has been reactivated successfully.");
    }

    /**
     * Delete all users except the current super admin.
     * Super admin only.
     */
    public function deleteAllUsers()
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $currentUserId = auth()->id();

        // Delete all users except current super admin
        $deleted = User::where('id', '!=', $currentUserId)->delete();

        return back()->with('success', "Deleted {$deleted} users.");
    }

    /**
     * Delete all requirement requests and related data.
     * Super admin only.
     */
    public function deleteAllRequests()
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        DB::transaction(function () {
            // Delete attachments from storage
            $attachmentPaths = DB::table('request_attachments')->pluck('file_path');
            foreach ($attachmentPaths as $path) {
                Storage::disk('local')->delete($path);
            }

            // Delete related records (cascades should handle this, but being explicit)
            DB::table('request_attachments')->delete();
            DB::table('request_history')->delete();
            RequirementRequest::query()->delete();
        });

        return back()->with('success', 'All requests and related data have been deleted.');
    }

    /**
     * Show CSV import form.
     * Super admin only.
     */
    public function showImport()
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        return view('admin.users.import');
    }

    /**
     * Import users from CSV file.
     * CSV format: firstname,lastname,email,role
     * Super admin only.
     */
    public function import(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getPathname(), 'r');

        if (!$handle) {
            return back()->with('error', 'Could not open the file.');
        }

        $validRoles = ['super_admin', 'fmo_admin', 'fmo_user', 'po_admin', 'po_user'];
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $lineNumber = 0;

        // Skip header row
        $header = fgetcsv($handle);
        $lineNumber++;

        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;

            // Validate row has enough columns
            if (count($row) < 4) {
                $errors[] = "Line {$lineNumber}: Invalid format (expected 4 columns)";
                $skipped++;
                continue;
            }

            [$firstname, $lastname, $email, $role] = array_map('trim', $row);

            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Line {$lineNumber}: Invalid email '{$email}'";
                $skipped++;
                continue;
            }

            // Validate role
            if (!in_array($role, $validRoles)) {
                $errors[] = "Line {$lineNumber}: Invalid role '{$role}'";
                $skipped++;
                continue;
            }

            // Check if user already exists
            if (User::where('email', $email)->exists()) {
                $errors[] = "Line {$lineNumber}: Email '{$email}' already exists";
                $skipped++;
                continue;
            }

            // Create user
            User::create([
                'name' => trim("{$firstname} {$lastname}"),
                'email' => $email,
                'role' => $role,
            ]);

            $imported++;
        }

        fclose($handle);

        $message = "Imported {$imported} users.";
        if ($skipped > 0) {
            $message .= " Skipped {$skipped} rows.";
        }

        if (!empty($errors)) {
            return back()
                ->with('success', $message)
                ->with('import_errors', array_slice($errors, 0, 10)); // Show first 10 errors
        }

        return back()->with('success', $message);
    }

    /**
     * Download sample CSV template.
     */
    public function downloadTemplate()
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users_import_template.csv"',
        ];

        $content = "firstname,lastname,email,role\n";
        $content .= "John,Doe,john.doe@example.com,fmo_user\n";
        $content .= "Jane,Smith,jane.smith@example.com,fmo_admin\n";
        $content .= "Bob,Wilson,bob.wilson@example.com,po_admin\n";
        $content .= "Alice,Brown,alice.brown@example.com,po_user\n";

        return response($content, 200, $headers);
    }
}
