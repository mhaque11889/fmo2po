@extends('layouts.app')

@section('title', 'Create Request')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Create New Requirement Request</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('requests.store') }}" method="POST" enctype="multipart/form-data">
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

            <div class="mb-4">
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

            <!-- File Attachments -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Attachments
                    <span class="text-gray-500 font-normal">(Optional - Max 2 files, 5MB each)</span>
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-indigo-400 transition"
                     id="dropzone">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="attachments" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none">
                                <span>Upload files</span>
                                <input id="attachments" name="attachments[]" type="file" class="sr-only" multiple accept=".pdf,.jpg,.jpeg,.png,.gif">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">PDF, PNG, JPG, GIF up to 5MB each</p>
                    </div>
                </div>
                <!-- Selected files preview -->
                <div id="file-preview" class="mt-3 space-y-2 hidden">
                    <p class="text-sm font-medium text-gray-700">Selected files:</p>
                    <ul id="file-list" class="text-sm text-gray-600 space-y-1"></ul>
                </div>
                @error('attachments')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
                @error('attachments.*')
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('attachments');
    const filePreview = document.getElementById('file-preview');
    const fileList = document.getElementById('file-list');
    const dropzone = document.getElementById('dropzone');

    // File input change handler
    fileInput.addEventListener('change', function() {
        updateFileList(this.files);
    });

    // Drag and drop handlers
    dropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('border-indigo-500', 'bg-indigo-50');
    });

    dropzone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('border-indigo-500', 'bg-indigo-50');
    });

    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('border-indigo-500', 'bg-indigo-50');

        const dt = new DataTransfer();
        const files = e.dataTransfer.files;

        // Limit to 2 files
        for (let i = 0; i < Math.min(files.length, 2); i++) {
            dt.items.add(files[i]);
        }

        fileInput.files = dt.files;
        updateFileList(dt.files);
    });

    function updateFileList(files) {
        fileList.innerHTML = '';

        if (files.length > 0) {
            filePreview.classList.remove('hidden');

            if (files.length > 2) {
                alert('Maximum 2 files allowed. Only the first 2 files will be uploaded.');
            }

            for (let i = 0; i < Math.min(files.length, 2); i++) {
                const file = files[i];
                const li = document.createElement('li');
                li.className = 'flex items-center justify-between bg-gray-50 px-3 py-2 rounded';

                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                const icon = file.type.includes('pdf') ?
                    '<svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M4 18h12a2 2 0 002-2V6l-4-4H6a2 2 0 00-2 2v12a2 2 0 002 2zm8-14v4h4l-4-4z"/></svg>' :
                    '<svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"/></svg>';

                li.innerHTML = `
                    <span class="flex items-center">
                        ${icon}
                        <span class="truncate max-w-xs">${file.name}</span>
                    </span>
                    <span class="text-gray-500 text-xs ml-2">${fileSize} MB</span>
                `;
                fileList.appendChild(li);
            }
        } else {
            filePreview.classList.add('hidden');
        }
    }
});
</script>
@endsection
