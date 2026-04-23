@extends('layouts.app')

@section('title', 'FMO Admin Dashboard')

@section('content')
<div class="flex flex-wrap gap-3 justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">FMO Admin Dashboard</h1>
    <a href="{{ route('requests.create') }}"
       class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition whitespace-nowrap">
        + New Request
    </a>
</div>

<!-- Stats Cards Section -->
<div class="mb-8">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Request Statistics</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
        <!-- All Requests (MTD) -->
        <a href="{{ route('reports.index', ['status' => 'pending']) }}" class="bg-white rounded-lg shadow p-6 block hover:shadow-lg transition cursor-pointer">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">All Requests (MTD)</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['all_mtd'] }}</p>
                </div>
            </div>
        </a>

        <!-- Pending -->
        <a href="{{ route('reports.index', ['status' => 'pending']) }}" class="bg-white rounded-lg shadow p-6 block hover:shadow-lg transition cursor-pointer">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Pending</p>
                    <p class="text-3xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
                </div>
            </div>
        </a>

        <!-- Pending on PO Dashboard -->
        <a href="{{ route('reports.index', ['status' => 'approved']) }}" class="bg-white rounded-lg shadow p-6 block hover:shadow-lg transition cursor-pointer">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Pending on PO</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $stats['pending_on_po'] }}</p>
                </div>
            </div>
        </a>

        <!-- PO In Progress -->
        <a href="{{ route('reports.index', ['status' => 'in_progress']) }}" class="bg-white rounded-lg shadow p-6 block hover:shadow-lg transition cursor-pointer">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">PO In Progress</p>
                    <p class="text-3xl font-bold text-purple-600">{{ $stats['po_in_progress'] }}</p>
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

<!-- Pending Requests Section -->
<div>
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Pending Requests (Awaiting Approval)</h2>

    {{-- Desktop table --}}
    <div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-yellow-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($pendingRequests as $request)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $request->id }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $request->display_item }}
                                @if($request->priority === 'urgent')
                                    <span class="ml-1 px-1.5 py-0.5 text-xs font-semibold bg-red-100 text-red-700 rounded">Urgent</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->location }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->creator->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $request->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('requests.show', $request) }}" class="inline-flex items-center px-3 py-1 border border-indigo-600 text-indigo-600 rounded-md hover:bg-indigo-600 hover:text-white transition">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">No pending requests</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile cards --}}
    <div class="md:hidden space-y-3">
        @forelse($pendingRequests as $request)
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-xs font-semibold text-gray-400 uppercase">#{{ $request->id }}</span>
                    <span class="text-xs text-gray-400">{{ $request->created_at->format('M d, Y') }}</span>
                </div>
                <p class="text-sm font-semibold text-gray-900 mb-1">
                    {{ $request->display_item }}
                    @if($request->priority === 'urgent')
                        <span class="ml-1 px-1.5 py-0.5 text-xs font-semibold bg-red-100 text-red-700 rounded">Urgent</span>
                    @endif
                </p>
                @if($request->specifications)
                    <p class="text-xs text-gray-500 mb-2">{{ $request->specifications }}</p>
                @endif
                <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-600 mb-3">
                    <span><span class="font-medium">Qty:</span> {{ $request->qty }}</span>
                    <span><span class="font-medium">Location:</span> {{ $request->location }}</span>
                    <span><span class="font-medium">By:</span> {{ $request->creator->name }}</span>
                </div>
                <a href="{{ route('requests.show', $request) }}"
                   class="inline-flex items-center px-3 py-1.5 border border-indigo-600 text-indigo-600 rounded-md hover:bg-indigo-600 hover:text-white transition text-sm">
                    View
                </a>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-4 text-center text-gray-500 text-sm">No pending requests</div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $pendingRequests->links() }}
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
    {{-- Mobile --}}
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
