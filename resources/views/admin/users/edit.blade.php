@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit User</h1>
        <a href="{{ route('admin.users.index') }}" class="text-indigo-600 hover:text-indigo-900">
            &larr; Back to Users
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                    Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('name') border-red-500 @enderror"
                    required>
                @error('name')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('email') border-red-500 @enderror"
                    required>
                @error('email')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                    Role <span class="text-red-500">*</span>
                </label>
                <select name="role" id="role"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('role') border-red-500 @enderror"
                    required>
                    @foreach($allowedRoles as $role)
                        <option value="{{ $role }}" {{ old('role', $user->role) === $role ? 'selected' : '' }}>
                            {{ str_replace('_', ' ', strtoupper($role)) }}
                        </option>
                    @endforeach
                </select>
                @error('role')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.users.index') }}"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Update User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
