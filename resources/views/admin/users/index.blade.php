@extends('layouts.app')

@section('title', 'Manage Users')

@section('content')
<div class="flex flex-wrap gap-3 justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
    <div class="flex items-center gap-3">
        @if(auth()->user()->isSuperAdmin())
            <a href="{{ route('admin.users.import') }}"
               class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition whitespace-nowrap">
                Import CSV
            </a>
        @endif
        <a href="{{ route('admin.users.create') }}"
           class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition whitespace-nowrap">
            + Add User
        </a>
    </div>
</div>

@if(auth()->user()->isSuperAdmin())
<!-- Super Admin Actions -->
<div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
    <h3 class="text-sm font-medium text-red-800 mb-3">Super Admin Actions</h3>
    <div class="flex flex-wrap gap-3 mb-4">
        <form action="{{ route('admin.users.delete-all') }}" method="POST"
              onsubmit="return confirm('Are you sure you want to delete ALL users except yourself? This cannot be undone!');">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700">
                Delete All Users
            </button>
        </form>
        <form action="{{ route('admin.requests.delete-all') }}" method="POST"
              onsubmit="return confirm('Are you sure you want to delete ALL requests and their attachments? This cannot be undone!');">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700">
                Delete All Requests
            </button>
        </form>
    </div>
    <div class="flex items-center gap-2">
        <input type="checkbox" id="show_inactive" name="show_inactive"
               {{ $showInactive ? 'checked' : '' }}
               onchange="toggleInactiveUsers()"
               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
        <label for="show_inactive" class="text-sm text-red-800 font-medium cursor-pointer">
            Show Deactivated Users
        </label>
    </div>
</div>
@endif

{{-- Desktop table --}}
<div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quick Role Change</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($users as $user)
                    <tr @if(!$user->is_active) class="bg-gray-50 opacity-70" @endif>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                @if($user->avatar)
                                    <img src="{{ $user->avatar }}" alt="Avatar" class="w-8 h-8 rounded-full">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                                        <span class="text-gray-500 text-sm">{{ substr($user->name, 0, 1) }}</span>
                                    </div>
                                @endif
                                <span class="text-sm font-medium text-gray-900">{{ $user->name }}</span>
                                @if(!$user->is_active)
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-600 bg-gray-200 px-2 py-1 rounded">
                                        Inactive
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($user->role === 'super_admin') bg-red-100 text-red-800
                                @elseif($user->role === 'fmo_user') bg-blue-100 text-blue-800
                                @elseif($user->role === 'fmo_admin') bg-green-100 text-green-800
                                @elseif($user->role === 'po_admin') bg-purple-100 text-purple-800
                                @else bg-orange-100 text-orange-800
                                @endif">
                                {{ str_replace('_', ' ', strtoupper($user->role)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($user->is_active)
                                <form action="{{ route('admin.users.update-role', $user) }}" method="POST" class="flex items-center space-x-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="role" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        @foreach($allowedRoles as $role)
                                            <option value="{{ $role }}" {{ $user->role === $role ? 'selected' : '' }}>
                                                {{ str_replace('_', ' ', strtoupper($role)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-700">
                                        Update
                                    </button>
                                </form>
                            @else
                                <span class="text-sm text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm flex gap-3">
                            @if($user->is_active)
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900">
                                    Edit
                                </a>
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                          onsubmit="return confirm('Are you sure you want to deactivate this user? They will no longer appear in any dropdowns or lists.');"
                                          style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 font-medium">
                                            Deactivate
                                        </button>
                                    </form>
                                @endif
                            @else
                                @if(auth()->user()->isSuperAdmin())
                                    <form action="{{ route('admin.users.activate', $user) }}" method="POST"
                                          onsubmit="return confirm('Are you sure you want to reactivate this user?');"
                                          style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-green-600 hover:text-green-900 font-medium">
                                            Reactivate
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Mobile cards --}}
<div class="md:hidden space-y-3">
    @foreach($users as $user)
        <div class="bg-white rounded-lg shadow p-4 @if(!$user->is_active) opacity-70 @endif">
            <div class="flex items-start gap-3 mb-3">
                @if($user->avatar)
                    <img src="{{ $user->avatar }}" alt="Avatar" class="w-10 h-10 rounded-full flex-shrink-0">
                @else
                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                        <span class="text-gray-500 font-medium">{{ substr($user->name, 0, 1) }}</span>
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-semibold text-gray-900">{{ $user->name }}</span>
                        @if(!$user->is_active)
                            <span class="text-xs font-medium text-gray-600 bg-gray-200 px-2 py-0.5 rounded">Inactive</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                    <span class="mt-1 inline-block px-2 py-0.5 text-xs rounded-full
                        @if($user->role === 'super_admin') bg-red-100 text-red-800
                        @elseif($user->role === 'fmo_user') bg-blue-100 text-blue-800
                        @elseif($user->role === 'fmo_admin') bg-green-100 text-green-800
                        @elseif($user->role === 'po_admin') bg-purple-100 text-purple-800
                        @else bg-orange-100 text-orange-800
                        @endif">
                        {{ str_replace('_', ' ', strtoupper($user->role)) }}
                    </span>
                </div>
            </div>

            @if($user->is_active)
                {{-- Quick role change --}}
                <form action="{{ route('admin.users.update-role', $user) }}" method="POST" class="flex items-center gap-2 mb-3">
                    @csrf
                    @method('PATCH')
                    <select name="role" class="flex-1 text-sm border border-gray-300 rounded-md px-2 py-1.5 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        @foreach($allowedRoles as $role)
                            <option value="{{ $role }}" {{ $user->role === $role ? 'selected' : '' }}>
                                {{ str_replace('_', ' ', strtoupper($role)) }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-indigo-600 text-white px-3 py-1.5 rounded text-sm hover:bg-indigo-700 whitespace-nowrap">
                        Update Role
                    </button>
                </form>

                <div class="flex gap-3">
                    <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                        Edit
                    </a>
                    @if($user->id !== auth()->id())
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to deactivate this user?');"
                              style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">
                                Deactivate
                            </button>
                        </form>
                    @endif
                </div>
            @else
                @if(auth()->user()->isSuperAdmin())
                    <form action="{{ route('admin.users.activate', $user) }}" method="POST"
                          onsubmit="return confirm('Are you sure you want to reactivate this user?');"
                          style="display: inline;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="text-green-600 hover:text-green-900 text-sm font-medium">
                            Reactivate
                        </button>
                    </form>
                @endif
            @endif
        </div>
    @endforeach
</div>

<div class="mt-4">
    {{ $users->links() }}
</div>

@if(auth()->user()->isSuperAdmin())
<script>
function toggleInactiveUsers() {
    const checkbox = document.getElementById('show_inactive');
    const url = new URL(window.location);
    if (checkbox.checked) {
        url.searchParams.set('show_inactive', '1');
    } else {
        url.searchParams.delete('show_inactive');
    }
    window.location.href = url.toString();
}
</script>
@endif
@endsection
