@extends('layouts.app')

@section('title', 'Purchase Office Admin Dashboard')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Purchase Office Admin Dashboard</h1>

@if($unreadNudges->isNotEmpty())
<!-- Unread Update Requests / Nudges Banner -->
<div class="mb-6 bg-amber-50 border border-amber-300 rounded-lg shadow-sm overflow-hidden">
    <div class="flex items-center gap-2 px-4 py-3 bg-amber-100 border-b border-amber-300">
        <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        <span class="font-semibold text-amber-800 text-sm">{{ $unreadNudges->count() }} Pending Update {{ Str::plural('Request', $unreadNudges->count()) }}</span>
    </div>
    <div class="divide-y divide-amber-200">
        @foreach($unreadNudges as $nudge)
        <div class="flex items-start justify-between px-4 py-3 hover:bg-amber-100 transition-colors">
            <div class="flex-1 min-w-0 mr-4">
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('requests.show', $nudge->requirement_request_id) }}" class="font-medium text-amber-900 hover:underline text-sm">
                        Request #{{ $nudge->requirement_request_id }}: {{ Str::limit($nudge->request->item ?? 'N/A', 40) }}
                    </a>
                    <span class="text-xs text-gray-400">{{ $nudge->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-sm text-gray-700 mt-1">
                    <span class="font-medium text-gray-600">From {{ $nudge->sender->name ?? 'Unknown' }}:</span>
                    {{ Str::limit($nudge->message, 120) }}
                </p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('requests.show', $nudge->requirement_request_id) }}" class="text-xs bg-amber-600 text-white px-3 py-1.5 rounded hover:bg-amber-700 transition-colors">
                    View &amp; Reply
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Stats Cards Section -->
<div class="mb-8">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Request Statistics</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Ready to Assign -->
        <a href="{{ route('reports.index', ['status' => 'approved']) }}" class="bg-white rounded-lg shadow p-6 block hover:shadow-lg transition cursor-pointer">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Ready to Assign</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $stats['ready_to_assign'] }}</p>
                </div>
            </div>
        </a>

        <!-- Assigned -->
        <a href="{{ route('reports.index', ['status' => 'assigned']) }}" class="bg-white rounded-lg shadow p-6 block hover:shadow-lg transition cursor-pointer">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Assigned</p>
                    <p class="text-3xl font-bold text-purple-600">{{ $stats['assigned'] }}</p>
                </div>
            </div>
        </a>

        <!-- In Progress -->
        <a href="{{ route('reports.index', ['status' => 'in_progress']) }}" class="bg-white rounded-lg shadow p-6 block hover:shadow-lg transition cursor-pointer">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">In Progress</p>
                    <p class="text-3xl font-bold text-orange-600">{{ $stats['in_progress'] }}</p>
                </div>
            </div>
        </a>

        <!-- Completed -->
        <a href="{{ route('reports.index', ['status' => 'completed']) }}" class="bg-white rounded-lg shadow p-6 block hover:shadow-lg transition cursor-pointer">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Completed</p>
                    <p class="text-3xl font-bold text-green-600">{{ $stats['completed'] }}</p>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Current Tasks (Assigned to Admin) -->
@if($myAssignedRequests->isNotEmpty())
<div class="mb-8">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Your Current Tasks</h2>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-orange-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dimensions</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($myAssignedRequests as $request)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $request->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->item }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $request->dimensions ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->qty }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->location }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->creator->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'assigned' => 'bg-purple-100 text-purple-800',
                                    'in_progress' => 'bg-orange-100 text-orange-800',
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
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Approved Requests (Ready to Assign) -->
<div>
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Requests Ready to Assign</h2>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dimensions</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approved By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($approvedRequests as $request)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $request->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->item }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $request->dimensions ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->qty }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->location }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->creator->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->approver->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('requests.show', $request) }}" class="inline-flex items-center px-3 py-1 border border-indigo-600 text-indigo-600 rounded-md hover:bg-indigo-600 hover:text-white transition">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                            No approved requests waiting for assignment
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $approvedRequests->links() }}
    </div>
</div>
@endsection
