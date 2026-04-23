<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\UserGroupCategoryApprover;
use Illuminate\Http\Request;

class UserGroupController extends Controller
{
    public function index(string $type)
    {
        $groups = UserGroup::where('type', $type)
            ->with(['creator', 'members', 'groupApprover'])
            ->orderBy('name')
            ->get();

        return view('admin.user-groups.index', compact('groups', 'type'));
    }

    public function create(string $type)
    {
        return view('admin.user-groups.create', compact('type'));
    }

    public function store(Request $request, string $type)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        UserGroup::create([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'type'        => $type,
            'created_by'  => auth()->id(),
        ]);

        $routePrefix = $type === 'fmo' ? 'admin.fmo-groups' : 'admin.po-groups';

        return redirect()->route("{$routePrefix}.index")
            ->with('success', 'Group created successfully.');
    }

    public function edit(UserGroup $userGroup)
    {
        $userGroup->load(['creator', 'members', 'groupApprover', 'categoryApprovers.approver']);

        $memberIds = $userGroup->members->pluck('id');
        $role = $userGroup->type === 'fmo' ? 'fmo_user' : 'po_user';

        // Users of correct role, active, not already in any group of this type
        $alreadyGrouped = UserGroup::where('type', $userGroup->type)
            ->with('members')
            ->get()
            ->flatMap(fn($g) => $g->members->pluck('id'))
            ->unique();

        $availableUsers = User::where('role', $role)
            ->where('is_active', true)
            ->whereNotIn('id', $alreadyGrouped)
            ->orderBy('name')
            ->get();

        // For FMO groups: eligible approvers are active fmo_user or fmo_admin
        $approverCandidates = collect();
        $categories = collect();
        if ($userGroup->type === 'fmo') {
            $approverCandidates = User::whereIn('role', ['fmo_user', 'fmo_admin'])
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
            $categories = Category::active()->get();
        }

        return view('admin.user-groups.edit', compact('userGroup', 'availableUsers', 'approverCandidates', 'categories'));
    }

    public function update(Request $request, UserGroup $userGroup)
    {
        $rules = [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ];

        if ($userGroup->type === 'fmo') {
            $rules['group_approver_id']   = 'nullable|exists:users,id';
            $rules['category_approvers']   = 'nullable|array';
            $rules['category_approvers.*'] = 'nullable|exists:users,id';
        }

        $validated = $request->validate($rules);

        $updateData = [
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
        ];

        if ($userGroup->type === 'fmo') {
            $updateData['group_approver_id'] = $validated['group_approver_id'] ?: null;
        }

        $userGroup->update($updateData);

        if ($userGroup->type === 'fmo') {
            $userGroup->categoryApprovers()->delete();
            foreach ($validated['category_approvers'] ?? [] as $categoryId => $approverId) {
                if ($approverId) {
                    UserGroupCategoryApprover::create([
                        'user_group_id' => $userGroup->id,
                        'category_id'   => (int) $categoryId,
                        'approver_id'   => (int) $approverId,
                    ]);
                }
            }
        }

        return redirect()->route($this->editRoute($userGroup), $userGroup)
            ->with('success', 'Group updated successfully.');
    }

    public function destroy(UserGroup $userGroup)
    {
        $routePrefix = $userGroup->type === 'fmo' ? 'admin.fmo-groups' : 'admin.po-groups';
        $userGroup->delete();

        return redirect()->route("{$routePrefix}.index")
            ->with('success', 'Group deleted successfully.');
    }

    public function addMember(Request $request, UserGroup $userGroup)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $expectedRole = $userGroup->type === 'fmo' ? 'fmo_user' : 'po_user';

        if ($user->role !== $expectedRole) {
            return back()->with('error', 'This user does not have the correct role for this group.');
        }

        if (!$user->is_active) {
            return back()->with('error', 'Cannot add an inactive user to a group.');
        }

        // Check if user is already in any group of this type
        $existingGroup = UserGroup::where('type', $userGroup->type)
            ->whereHas('members', fn($q) => $q->where('users.id', $user->id))
            ->first();

        if ($existingGroup) {
            return back()->with('error', "This user is already in the group \"{$existingGroup->name}\".");
        }

        $userGroup->members()->attach($user->id);

        return redirect()->route($this->editRoute($userGroup), $userGroup)
            ->with('success', "{$user->name} added to the group.");
    }

    public function removeMember(UserGroup $userGroup, User $user)
    {
        $userGroup->members()->detach($user->id);

        return redirect()->route($this->editRoute($userGroup), $userGroup)
            ->with('success', "{$user->name} removed from the group.");
    }

    private function editRoute(UserGroup $userGroup): string
    {
        $prefix = $userGroup->type === 'fmo' ? 'admin.fmo-groups' : 'admin.po-groups';
        return "{$prefix}.edit";
    }
}
