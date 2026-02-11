@extends('layouts.app')

@section('title', 'My Assigned Requests')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">My Assigned Requests</h1>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dimensions</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($assignedRequests as $request)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $request->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->item }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $request->dimensions ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->qty }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->location }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ $request->remarks ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded-full
                            @if($request->status === 'assigned') bg-purple-100 text-purple-800
                            @else bg-green-100 text-green-800
                            @endif">
                            {{ ucfirst($request->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $request->assigned_at ? $request->assigned_at->format('M d, Y') : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                        <a href="{{ route('requests.show', $request) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                        @if($request->status === 'assigned')
                            <form action="{{ route('requests.complete', $request) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-900">
                                    Mark Complete
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                        No requests assigned to you yet
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $assignedRequests->links() }}
</div>
@endsection
