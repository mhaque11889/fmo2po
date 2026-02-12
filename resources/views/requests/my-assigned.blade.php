@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">{{ $title }}</h1>
        <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-900">
            &larr; Back to Dashboard
        </a>
    </div>

    <!-- Filter Tabs -->
    <div class="mt-4 flex flex-wrap gap-2">
        <a href="{{ route('requests.my-assigned') }}"
           class="px-4 py-2 rounded-md text-sm font-medium {{ !$status ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            All
        </a>
        <a href="{{ route('requests.my-assigned', 'assigned') }}"
           class="px-4 py-2 rounded-md text-sm font-medium {{ $status === 'assigned' ? 'bg-purple-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Assigned
        </a>
        <a href="{{ route('requests.my-assigned', 'in_progress') }}"
           class="px-4 py-2 rounded-md text-sm font-medium {{ $status === 'in_progress' ? 'bg-orange-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            In Progress
        </a>
        <a href="{{ route('requests.my-assigned', 'completed') }}"
           class="px-4 py-2 rounded-md text-sm font-medium {{ $status === 'completed' ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            Completed
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($requests as $request)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $request->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->item }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->qty }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->location }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->creator->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $request->assigned_at ? $request->assigned_at->format('M d, Y') : '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $statusColors = [
                                'assigned' => 'bg-purple-100 text-purple-800',
                                'in_progress' => 'bg-orange-100 text-orange-800',
                                'completed' => 'bg-green-100 text-green-800',
                            ];
                            $color = $statusColors[$request->status] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $color }}">
                            {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('requests.show', $request) }}" class="inline-flex items-center px-3 py-1 border border-indigo-600 text-indigo-600 rounded-md hover:bg-indigo-600 hover:text-white transition">
                            View
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                        No requests found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $requests->links() }}
</div>
@endsection
