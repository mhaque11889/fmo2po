@extends('layouts.app')

@section('title', 'Create Request')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Create New Requirement Request</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('requests.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="item" class="block text-sm font-medium text-gray-700 mb-1">
                    Item <span class="text-red-500">*</span>
                </label>
                <input type="text" name="item" id="item" value="{{ old('item') }}"
                    class="w-full border border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 px-3 py-2 @error('item') border-red-500 @enderror"
                    required>
                @error('item')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="dimensions" class="block text-sm font-medium text-gray-700 mb-1">
                    Dimensions
                </label>
                <input type="text" name="dimensions" id="dimensions" value="{{ old('dimensions') }}"
                    placeholder="e.g., 10x20x30 cm"
                    class="w-full border border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 px-3 py-2 @error('dimensions') border-red-500 @enderror">
                @error('dimensions')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="qty" class="block text-sm font-medium text-gray-700 mb-1">
                    Quantity <span class="text-red-500">*</span>
                </label>
                <input type="number" name="qty" id="qty" value="{{ old('qty', 1) }}" min="1"
                    class="w-full border border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 px-3 py-2 @error('qty') border-red-500 @enderror"
                    required>
                @error('qty')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="location" class="block text-sm font-medium text-gray-700 mb-1">
                    Location <span class="text-red-500">*</span>
                </label>
                <input type="text" name="location" id="location" value="{{ old('location') }}"
                    placeholder="e.g., Building A, Room 101"
                    class="w-full border border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 px-3 py-2 @error('location') border-red-500 @enderror"
                    required>
                @error('location')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1">
                    Remarks
                </label>
                <textarea name="remarks" id="remarks" rows="3"
                    placeholder="Any additional information..."
                    class="w-full border border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 px-3 py-2 @error('remarks') border-red-500 @enderror">{{ old('remarks') }}</textarea>
                @error('remarks')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('dashboard') }}"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
