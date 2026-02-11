@extends('layouts.app')

@section('title', 'Super Admin Dashboard')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Super Admin Dashboard</h1>
    <a href="{{ route('requests.create') }}"
       class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
        + New Request
    </a>
</div>

<!-- Pending Requests Section -->
<div class="mb-8">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Pending Requests (Awaiting Approval)</h2>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-yellow-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->item }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->qty }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->location }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->creator->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $request->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                            <a href="{{ route('requests.show', $request) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                            <form action="{{ route('requests.approve', $request) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-900">Approve</button>
                            </form>
                            <form action="{{ route('requests.reject', $request) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-red-600 hover:text-red-900">Reject</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No pending requests</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-2">{{ $pendingRequests->links() }}</div>
</div>

<!-- Approved Requests (Ready to Assign) -->
<div class="mb-8">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Approved Requests (Ready to Assign)</h2>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assign To</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($approvedRequests as $request)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $request->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->item }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->qty }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->location }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->creator->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <form action="{{ route('requests.assign', $request) }}" method="POST" class="flex items-center space-x-2">
                                @csrf
                                <select name="assigned_to" class="text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">Select User</option>
                                    @foreach($poUsers as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-700">Assign</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No approved requests waiting</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-2">{{ $approvedRequests->links() }}</div>
</div>

<!-- Assigned/Completed Requests -->
<div>
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Assigned & Completed Requests</h2>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($assignedRequests as $request)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $request->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->item }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->qty }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->assignee->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full {{ $request->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }}">
                                {{ ucfirst($request->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('requests.show', $request) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No assigned or completed requests</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-2">{{ $assignedRequests->links() }}</div>
</div>
@endsection
