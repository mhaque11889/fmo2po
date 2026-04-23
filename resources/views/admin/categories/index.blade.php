@extends('layouts.app')

@section('title', 'Categories')

@section('content')
<div class="flex flex-wrap gap-3 justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Categories</h1>
    <a href="{{ route('admin.categories.create') }}"
       class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition whitespace-nowrap">
        + Add Category
    </a>
</div>

@if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
        {{ session('error') }}
    </div>
@endif

{{-- Desktop table --}}
<div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sort</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requests</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($categories as $category)
                <tr class="{{ $category->is_active ? '' : 'bg-gray-50 opacity-60' }}">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $category->name }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $category->description ?: '—' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $category->sort_order }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                            {{ $category->requirements_count }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if($category->is_active)
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">Active</span>
                        @else
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.categories.edit', $category) }}"
                               class="text-indigo-600 hover:text-indigo-900 font-medium">Edit</a>
                            @if($category->requirements_count === 0)
                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST"
                                      onsubmit="return confirm('Delete category \'{{ $category->name }}\'?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Delete</button>
                                </form>
                            @else
                                <span class="text-gray-300 text-xs">Has requests</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400">
                        No categories yet. <a href="{{ route('admin.categories.create') }}" class="text-indigo-600 hover:underline">Add one</a>.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Mobile cards --}}
<div class="md:hidden space-y-3">
    @forelse($categories as $category)
        <div class="bg-white rounded-lg shadow p-4 {{ $category->is_active ? '' : 'opacity-60' }}">
            <div class="flex justify-between items-start mb-1">
                <span class="text-sm font-semibold text-gray-900">{{ $category->name }}</span>
                @if($category->is_active)
                    <span class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">Active</span>
                @else
                    <span class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">Inactive</span>
                @endif
            </div>
            @if($category->description)
                <p class="text-xs text-gray-500 mb-2">{{ $category->description }}</p>
            @endif
            <p class="text-xs text-gray-400 mb-3">{{ $category->requirements_count }} request(s)</p>
            <div class="flex gap-4">
                <a href="{{ route('admin.categories.edit', $category) }}"
                   class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Edit</a>
                @if($category->requirements_count === 0)
                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST"
                          onsubmit="return confirm('Delete \'{{ $category->name }}\'?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">Delete</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="bg-white rounded-lg shadow p-6 text-center text-sm text-gray-400">
            No categories yet.
        </div>
    @endforelse
</div>

<div class="mt-4">
    <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Dashboard</a>
</div>
@endsection
