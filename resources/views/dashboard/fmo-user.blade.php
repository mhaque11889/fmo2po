@extends('layouts.app')

@section('title', 'My Requests')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">My Requirement Requests</h1>
    <a href="{{ route('requests.create') }}"
       class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
        + New Request
    </a>
</div>

<!-- Stats Cards Section -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-8">
    <!-- Total Requests -->
    <a href="{{ route('requests.my') }}" class="bg-white rounded-lg shadow p-5 block hover:shadow-lg transition cursor-pointer">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-xs font-medium text-gray-500">Total Requests</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
            </div>
        </div>
    </a>

    <!-- Pending Approval -->
    <a href="{{ route('requests.my', 'pending') }}" class="bg-white rounded-lg shadow p-5 block hover:shadow-lg transition cursor-pointer">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-xs font-medium text-gray-500">Pending Approval</p>
                <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
            </div>
        </div>
    </a>

    <!-- Needs Clarification -->
    <a href="{{ route('requests.my', 'clarification_needed') }}" class="bg-white rounded-lg shadow p-5 block hover:shadow-lg transition cursor-pointer {{ $stats['clarification_needed'] > 0 ? 'ring-2 ring-amber-400' : '' }}">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-amber-100 text-amber-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-xs font-medium text-gray-500">Needs Clarification</p>
                <p class="text-2xl font-bold text-amber-600">{{ $stats['clarification_needed'] }}</p>
            </div>
        </div>
    </a>

    <!-- Pending on PO -->
    <a href="{{ route('requests.my', 'approved') }}" class="bg-white rounded-lg shadow p-5 block hover:shadow-lg transition cursor-pointer">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-xs font-medium text-gray-500">Pending on PO</p>
                <p class="text-2xl font-bold text-blue-600">{{ $stats['pending_on_po'] }}</p>
            </div>
        </div>
    </a>

    <!-- Assigned to PO User -->
    <a href="{{ route('requests.my', 'assigned') }}" class="bg-white rounded-lg shadow p-5 block hover:shadow-lg transition cursor-pointer">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-xs font-medium text-gray-500">Assigned to PO</p>
                <p class="text-2xl font-bold text-purple-600">{{ $stats['assigned'] }}</p>
            </div>
        </div>
    </a>

    <!-- Completed -->
    <a href="{{ route('requests.my', 'completed') }}" class="bg-white rounded-lg shadow p-5 block hover:shadow-lg transition cursor-pointer">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-xs font-medium text-gray-500">Completed</p>
                <p class="text-2xl font-bold text-green-600">{{ $stats['completed'] }}</p>
            </div>
        </div>
    </a>
</div>

<!-- Recent Requests Section -->
<div>
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Recent Requests</h2>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($requests as $request)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $request->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $request->category?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $request->display_item }}
                            @if($request->priority === 'urgent')
                                <span class="ml-1 px-1.5 py-0.5 text-xs font-semibold bg-red-100 text-red-700 rounded">Urgent</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->location }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'approved' => 'bg-blue-100 text-blue-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    'assigned' => 'bg-purple-100 text-purple-800',
                                    'in_progress' => 'bg-orange-100 text-orange-800',
                                    'completed' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-gray-100 text-gray-800',
                                    'clarification_needed' => 'bg-amber-100 text-amber-800',
                                ];
                                $color = $statusColors[$request->status] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $color }}">
                                {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $request->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('requests.show', $request) }}" class="inline-flex items-center px-3 py-1 border border-indigo-600 text-indigo-600 rounded-md hover:bg-indigo-600 hover:text-white transition">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No requests found. <a href="{{ route('requests.create') }}" class="text-indigo-600 hover:underline">Create your first request</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($groupPendingRequests->isNotEmpty())
<!-- Group Approver Queue -->
<div class="mt-8">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">
        <span class="inline-flex items-center gap-2">
            Group Approval Queue
            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                {{ $groupPendingRequests->count() }} pending
            </span>
        </span>
    </h2>
    <div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-indigo-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($groupPendingRequests as $req)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $req->id }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $req->display_item }}
                            @if($req->priority === 'urgent')
                                <span class="ml-1 px-1.5 py-0.5 text-xs font-semibold bg-red-100 text-red-700 rounded">Urgent</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $req->location }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $req->creator->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $req->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('requests.show', $req) }}"
                               class="inline-flex items-center px-3 py-1 border border-indigo-600 text-indigo-600 rounded-md hover:bg-indigo-600 hover:text-white transition">
                                Review
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="md:hidden space-y-3">
        @foreach($groupPendingRequests as $req)
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-xs font-semibold text-gray-400 uppercase">#{{ $req->id }}</span>
                    <span class="text-xs text-gray-400">{{ $req->created_at->format('M d, Y') }}</span>
                </div>
                <p class="text-sm font-semibold text-gray-900 mb-1">{{ $req->display_item }}</p>
                <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-600 mb-3">
                    <span><span class="font-medium">Location:</span> {{ $req->location }}</span>
                    <span><span class="font-medium">By:</span> {{ $req->creator->name }}</span>
                </div>
                <a href="{{ route('requests.show', $req) }}"
                   class="inline-flex items-center px-3 py-1.5 border border-indigo-600 text-indigo-600 rounded-md hover:bg-indigo-600 hover:text-white transition text-sm">
                    Review
                </a>
            </div>
        @endforeach
    </div>
</div>
@endif
@endsection
