<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

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

    public function index()
    {
        $currentUser = auth()->user();
        $allowedRoles = $this->getAllowedRoles();

        if ($currentUser->isSuperAdmin()) {
            $users = User::orderBy('name')->paginate(20);
        } else {
            $users = User::whereIn('role', $allowedRoles)
                ->orderBy('name')
                ->paginate(20);
        }

        return view('admin.users.index', compact('users', 'allowedRoles'));
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
}
