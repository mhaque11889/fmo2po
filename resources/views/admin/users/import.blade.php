@extends('layouts.app')

@section('title', 'Import Users')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Import Users from CSV</h1>
        <a href="{{ route('admin.users.index') }}" class="text-indigo-600 hover:text-indigo-900">
            &larr; Back to Users
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.users.import.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-6">
                <label for="csv_file" class="block text-sm font-medium text-gray-700 mb-2">
                    CSV File <span class="text-red-500">*</span>
                </label>
                <input type="file" name="csv_file" id="csv_file" accept=".csv,.txt"
                    class="w-full border border-gray-300 rounded-md shadow-sm p-2 @error('csv_file') border-red-500 @enderror"
                    required>
                @error('csv_file')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-between items-center">
                <a href="{{ route('admin.users.import.template') }}"
                   class="text-indigo-600 hover:text-indigo-900 text-sm">
                    Download CSV Template
                </a>
                <button type="submit"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    Import Users
                </button>
            </div>
        </form>
    </div>

    <!-- CSV Format Guide -->
    <div class="mt-6 bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-3">CSV Format</h3>
        <p class="text-sm text-gray-600 mb-4">
            The CSV file should have the following columns (with header row):
        </p>
        <div class="bg-white border border-gray-200 rounded p-4 font-mono text-sm overflow-x-auto">
            <p class="text-gray-500">firstname,lastname,email,role</p>
            <p>John,Doe,john.doe@example.com,fmo_user</p>
            <p>Jane,Smith,jane.smith@example.com,fmo_admin</p>
        </div>

        <h4 class="text-sm font-medium text-gray-900 mt-4 mb-2">Valid Roles:</h4>
        <ul class="text-sm text-gray-600 list-disc list-inside space-y-1">
            <li><code class="bg-gray-100 px-1 rounded">super_admin</code> - Full system access</li>
            <li><code class="bg-gray-100 px-1 rounded">fmo_admin</code> - FMO Administrator</li>
            <li><code class="bg-gray-100 px-1 rounded">fmo_user</code> - FMO User (creates requests)</li>
            <li><code class="bg-gray-100 px-1 rounded">po_admin</code> - Purchase Office Administrator</li>
            <li><code class="bg-gray-100 px-1 rounded">po_user</code> - Purchase Office User</li>
        </ul>
    </div>

    @if(session('import_errors'))
    <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <h4 class="text-sm font-medium text-yellow-800 mb-2">Import Errors:</h4>
        <ul class="text-sm text-yellow-700 list-disc list-inside space-y-1">
            @foreach(session('import_errors') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
</div>
@endsection
