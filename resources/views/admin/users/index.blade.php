@extends('layouts.app')

@section('title', 'Manage Users')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
    <a href="{{ route('admin.users.create') }}"
       class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
        + Add User
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
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
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            @if($user->avatar)
                                <img src="{{ $user->avatar }}" alt="Avatar" class="w-8 h-8 rounded-full mr-3">
                            @else
                                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                    <span class="text-gray-500 text-sm">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <span class="text-sm font-medium text-gray-900">{{ $user->name }}</span>
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
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900">
                            Edit
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $users->links() }}
</div>
@endsection
