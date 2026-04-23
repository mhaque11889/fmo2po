@extends('layouts.app')

@section('title', 'Create ' . ($type === 'fmo' ? 'FMO' : 'PO') . ' Group')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex flex-wrap gap-3 items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Create {{ $type === 'fmo' ? 'FMO' : 'PO' }} Group</h1>
        <a href="{{ route('admin.' . $type . '-groups.index') }}"
           class="text-sm text-gray-500 hover:text-gray-700">← Back to Groups</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.' . $type . '-groups.store') }}" method="POST">
            @csrf

            <div class="mb-5">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                    Group Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name') }}"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror"
                       placeholder="e.g. Procurement Team A">
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                    Description <span class="text-gray-400 font-normal">(optional)</span>
                </label>
                <textarea id="description" name="description" rows="3"
                          class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') border-red-500 @enderror"
                          placeholder="Brief description of this group's purpose">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                        class="bg-indigo-600 text-white px-5 py-2 rounded-md hover:bg-indigo-700 transition text-sm font-medium">
                    Create Group
                </button>
                <a href="{{ route('admin.' . $type . '-groups.index') }}"
                   class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
