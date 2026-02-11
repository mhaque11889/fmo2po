@extends('layouts.app')

@section('title', 'Request Details')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Request #{{ $request->id }}</h1>
        <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-900">
            &larr; Back to Dashboard
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <!-- Status Banner -->
        <div class="px-6 py-4
            @if($request->status === 'pending') bg-yellow-50 border-b border-yellow-200
            @elseif($request->status === 'approved') bg-blue-50 border-b border-blue-200
            @elseif($request->status === 'assigned') bg-purple-50 border-b border-purple-200
            @elseif($request->status === 'completed') bg-green-50 border-b border-green-200
            @else bg-red-50 border-b border-red-200
            @endif">
            <div class="flex items-center justify-between">
                <span class="px-3 py-1 text-sm font-semibold rounded-full
                    @if($request->status === 'pending') bg-yellow-100 text-yellow-800
                    @elseif($request->status === 'approved') bg-blue-100 text-blue-800
                    @elseif($request->status === 'assigned') bg-purple-100 text-purple-800
                    @elseif($request->status === 'completed') bg-green-100 text-green-800
                    @else bg-red-100 text-red-800
                    @endif">
                    {{ ucfirst($request->status) }}
                </span>
                <span class="text-sm text-gray-500">
                    Created {{ $request->created_at->format('M d, Y \a\t h:i A') }}
                </span>
            </div>
        </div>

        <!-- Request Details -->
        <div class="p-6">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Item</dt>
                    <dd class="mt-1 text-lg text-gray-900">{{ $request->item }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Quantity</dt>
                    <dd class="mt-1 text-lg text-gray-900">{{ $request->qty }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Dimensions</dt>
                    <dd class="mt-1 text-lg text-gray-900">{{ $request->dimensions ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Location</dt>
                    <dd class="mt-1 text-lg text-gray-900">{{ $request->location }}</dd>
                </div>

                <div class="md:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Remarks</dt>
                    <dd class="mt-1 text-gray-900">{{ $request->remarks ?? 'No remarks' }}</dd>
                </div>
            </dl>
        </div>

        <!-- Workflow Information -->
        <div class="border-t border-gray-200 p-6 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Workflow History</h3>
            <dl class="space-y-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                        <span class="text-blue-600 text-sm font-medium">1</span>
                    </div>
                    <div class="ml-4">
                        <dt class="text-sm font-medium text-gray-900">Requested By</dt>
                        <dd class="text-sm text-gray-500">
                            {{ $request->creator->name }} on {{ $request->created_at->format('M d, Y \a\t h:i A') }}
                        </dd>
                    </div>
                </div>

                @if($request->approved_by)
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full
                            @if($request->status === 'rejected') bg-red-100 @else bg-green-100 @endif
                            flex items-center justify-center">
                            <span class="@if($request->status === 'rejected') text-red-600 @else text-green-600 @endif text-sm font-medium">2</span>
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-gray-900">
                                @if($request->status === 'rejected') Rejected @else Approved @endif By
                            </dt>
                            <dd class="text-sm text-gray-500">
                                {{ $request->approver->name }} on {{ $request->approved_at->format('M d, Y \a\t h:i A') }}
                            </dd>
                        </div>
                    </div>
                @endif

                @if($request->assigned_to)
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center">
                            <span class="text-purple-600 text-sm font-medium">3</span>
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-gray-900">Assigned To</dt>
                            <dd class="text-sm text-gray-500">
                                {{ $request->assignee->name }} by {{ $request->assigner->name }} on {{ $request->assigned_at->format('M d, Y \a\t h:i A') }}
                            </dd>
                        </div>
                    </div>
                @endif

                @if($request->status === 'completed')
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                            <span class="text-green-600 text-sm font-medium">4</span>
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-gray-900">Completed</dt>
                            <dd class="text-sm text-gray-500">
                                Marked as completed
                            </dd>
                        </div>
                    </div>
                @endif
            </dl>
        </div>

        <!-- Actions -->
        @if(auth()->user()->isFmoAdmin() && $request->status === 'pending')
            <div class="border-t border-gray-200 p-6">
                <div class="flex space-x-3">
                    <form action="{{ route('requests.approve', $request) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Approve Request
                        </button>
                    </form>
                    <form action="{{ route('requests.reject', $request) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Reject Request
                        </button>
                    </form>
                </div>
            </div>
        @endif

        @if(auth()->user()->isPoUser() && $request->assigned_to === auth()->id() && $request->status === 'assigned')
            <div class="border-t border-gray-200 p-6">
                <form action="{{ route('requests.complete', $request) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Mark as Completed
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
