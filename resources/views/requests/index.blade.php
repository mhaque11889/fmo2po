@extends('layouts.app')

@section('title', 'All Requests')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">All Requests</h1>
        <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-900">
            &larr; Back to Dashboard
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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Workflow</th>
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
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $request->created_at->format('M d, Y') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'approved' => 'bg-blue-100 text-blue-800',
                                'rejected' => 'bg-red-100 text-red-800',
                                'assigned' => 'bg-purple-100 text-purple-800',
                                'completed' => 'bg-green-100 text-green-800',
                            ];
                            $color = $statusColors[$request->status] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $color }}">
                            {{ ucfirst($request->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                        <div class="space-y-1">
                            <div class="flex items-center">
                                <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                                Created: {{ $request->created_at->format('M d, H:i') }}
                            </div>
                            @if($request->approved_at)
                                <div class="flex items-center">
                                    <span class="w-2 h-2 rounded-full {{ $request->status === 'rejected' ? 'bg-red-500' : 'bg-green-500' }} mr-2"></span>
                                    {{ $request->status === 'rejected' ? 'Rejected' : 'Approved' }}: {{ $request->approved_at->format('M d, H:i') }}
                                    @if($request->approver)
                                        <span class="text-gray-400 ml-1">by {{ $request->approver->name }}</span>
                                    @endif
                                </div>
                            @endif
                            @if($request->assigned_at)
                                <div class="flex items-center">
                                    <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                                    Assigned: {{ $request->assigned_at->format('M d, H:i') }}
                                    @if($request->assignee)
                                        <span class="text-gray-400 ml-1">to {{ $request->assignee->name }}</span>
                                    @endif
                                </div>
                            @endif
                            @if($request->status === 'completed')
                                <div class="flex items-center">
                                    <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                                    Completed
                                </div>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('requests.show', $request) }}" class="inline-flex items-center px-3 py-1 border border-indigo-600 text-indigo-600 rounded-md hover:bg-indigo-600 hover:text-white transition">
                            View
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-6 py-4 text-center text-gray-500">
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
