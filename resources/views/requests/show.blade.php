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
            @elseif($request->status === 'in_progress') bg-orange-50 border-b border-orange-200
            @elseif($request->status === 'completed') bg-green-50 border-b border-green-200
            @else bg-red-50 border-b border-red-200
            @endif">
            <div class="flex items-center justify-between">
                <span class="px-3 py-1 text-sm font-semibold rounded-full
                    @if($request->status === 'pending') bg-yellow-100 text-yellow-800
                    @elseif($request->status === 'approved') bg-blue-100 text-blue-800
                    @elseif($request->status === 'assigned') bg-purple-100 text-purple-800
                    @elseif($request->status === 'in_progress') bg-orange-100 text-orange-800
                    @elseif($request->status === 'completed') bg-green-100 text-green-800
                    @else bg-red-100 text-red-800
                    @endif">
                    {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                </span>
                <span class="text-sm text-gray-500">
                    Created {{ $request->created_at ? $request->created_at->format('M d, Y \a\t h:i A') : 'N/A' }}
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

                <!-- Attachments Section -->
                @if($request->attachments->count() > 0)
                <div class="md:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 mb-2">Attachments</dt>
                    <dd class="mt-1">
                        <div class="flex flex-wrap gap-3">
                            @foreach($request->attachments as $index => $attachment)
                                <a href="{{ route('attachments.show', $attachment) }}"
                                   target="_blank"
                                   class="flex items-center px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 hover:border-indigo-300 transition group">
                                    @if($attachment->isPdf())
                                        <svg class="w-8 h-8 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                        </svg>
                                    @else
                                        <svg class="w-8 h-8 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                                        </svg>
                                    @endif
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 group-hover:text-indigo-600">
                                            Attachment {{ $index + 1 }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ strtoupper($attachment->extension) }} - {{ $attachment->human_file_size }}
                                        </p>
                                    </div>
                                    <svg class="w-4 h-4 text-gray-400 ml-3 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            @endforeach
                        </div>
                    </dd>
                </div>
                @endif
            </dl>
        </div>

        <!-- Workflow History -->
        <div class="border-t border-gray-200 p-6 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Workflow History</h3>
            <dl class="space-y-4">
                @php
                    $historyItems = $request->history->reverse()->values();
                @endphp
                @forelse($historyItems as $index => $entry)
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center
                            @if($entry->action === 'created') bg-blue-100
                            @elseif($entry->action === 'edited') bg-yellow-100
                            @elseif($entry->action === 'approved') bg-green-100
                            @elseif($entry->action === 'rejected') bg-red-100
                            @elseif($entry->action === 'assigned') bg-purple-100
                            @elseif($entry->action === 'in_progress') bg-orange-100
                            @elseif($entry->action === 'completed') bg-green-100
                            @else bg-gray-100
                            @endif">
                            <span class="text-sm font-medium
                                @if($entry->action === 'created') text-blue-600
                                @elseif($entry->action === 'edited') text-yellow-600
                                @elseif($entry->action === 'approved') text-green-600
                                @elseif($entry->action === 'rejected') text-red-600
                                @elseif($entry->action === 'assigned') text-purple-600
                                @elseif($entry->action === 'in_progress') text-orange-600
                                @elseif($entry->action === 'completed') text-green-600
                                @else text-gray-600
                                @endif">{{ $index + 1 }}</span>
                        </div>
                        <div class="ml-4 flex-1">
                            <dt class="text-sm font-medium text-gray-900">{{ $entry->action_description }}</dt>
                            <dd class="text-sm text-gray-500">
                                {{ $entry->user->name }} on {{ $entry->created_at->format('M d, Y \a\t h:i A') }}
                            </dd>

                            {{-- Show changes for edits --}}
                            @if($entry->action === 'edited' && $entry->changes)
                                <div class="mt-2 text-xs bg-white border border-gray-200 rounded p-2">
                                    <p class="font-medium text-gray-700 mb-1">Changes made:</p>
                                    @foreach($entry->changes as $field => $change)
                                        <p class="text-gray-600">
                                            <span class="font-medium">{{ ucfirst($field) }}:</span>
                                            <span class="line-through text-red-600">{{ $change['old'] ?? 'empty' }}</span>
                                            &rarr;
                                            <span class="text-green-600">{{ $change['new'] ?? 'empty' }}</span>
                                        </p>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Show assignment details --}}
                            @if($entry->action === 'assigned' && $entry->changes && isset($entry->changes['assigned_to']))
                                <div class="mt-1 text-sm text-gray-600">
                                    Assigned to: <span class="font-medium">{{ $entry->changes['assigned_to']['new'] }}</span>
                                </div>
                            @endif

                            {{-- Show remarks for in_progress and completed --}}
                            @if($entry->remarks)
                                <div class="mt-2 text-sm text-gray-600 bg-white border border-gray-200 rounded p-2 italic">
                                    "{{ $entry->remarks }}"
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    {{-- Fallback if no history exists --}}
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
                @endforelse
            </dl>
        </div>

        <!-- Edit Button for FMO User (own pending requests) -->
        @if(auth()->user()->isFmoUser() && $request->created_by === auth()->id() && $request->isPending())
            <div class="border-t border-gray-200 p-6">
                <a href="{{ route('requests.edit', $request) }}"
                    class="inline-block px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Edit Request
                </a>
            </div>
        @endif

        <!-- Actions for FMO Admin (pending requests) -->
        @if((auth()->user()->isFmoAdmin() || auth()->user()->isSuperAdmin()) && $request->isPending())
            <div class="border-t border-gray-200 p-6">
                <div class="flex space-x-3">
                    <a href="{{ route('requests.edit', $request) }}"
                        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                        Edit Request
                    </a>
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

        <!-- Actions for PO Admin (approved requests - ready to assign) -->
        @if((auth()->user()->isPoAdmin() || auth()->user()->isSuperAdmin()) && $request->isApproved())
            <div class="border-t border-gray-200 p-6">
                <h4 class="text-md font-medium text-gray-900 mb-4">Assign Request</h4>
                <form action="{{ route('requests.assign', $request) }}" method="POST" class="bg-blue-50 p-4 rounded-lg max-w-md">
                    @csrf
                    <div class="mb-3">
                        <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1">
                            Assign To
                        </label>
                        <select name="assigned_to" id="assigned_to" required
                            class="w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2">
                            <option value="">Select User</option>
                            <option value="{{ auth()->id() }}">{{ auth()->user()->name }} (Self)</option>
                            @foreach($poUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Assign Request
                    </button>
                </form>
            </div>
        @endif

        <!-- Actions for assigned user (PO User or self-assigned PO Admin) - Assigned status: Show In Progress first -->
        @if($request->assigned_to === auth()->id() && $request->isAssigned())
            <div class="border-t border-gray-200 p-6">
                <h4 class="text-md font-medium text-gray-900 mb-4">Update Status</h4>
                <form action="{{ route('requests.in-progress', $request) }}" method="POST" class="bg-orange-50 p-4 rounded-lg max-w-md">
                    @csrf
                    <h5 class="font-medium text-orange-800 mb-2">Mark as In Progress</h5>
                    <div class="mb-3">
                        <label for="progress_remarks" class="block text-sm font-medium text-gray-700 mb-1">
                            Remarks (optional)
                        </label>
                        <textarea name="progress_remarks" id="progress_remarks" rows="2"
                            class="w-full border border-gray-300 rounded-md shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200 focus:ring-opacity-50 px-3 py-2 text-sm"
                            placeholder="Add any remarks..."></textarea>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700">
                        Mark as In Progress
                    </button>
                </form>
            </div>
        @endif

        <!-- Actions for in_progress status: Show Complete -->
        @if($request->assigned_to === auth()->id() && $request->isInProgress())
            <div class="border-t border-gray-200 p-6">
                <h4 class="text-md font-medium text-gray-900 mb-4">Complete Request</h4>
                <form action="{{ route('requests.complete', $request) }}" method="POST" class="bg-green-50 p-4 rounded-lg max-w-md">
                    @csrf
                    <h5 class="font-medium text-green-800 mb-2">Mark as Completed</h5>
                    <div class="mb-3">
                        <label for="completion_remarks" class="block text-sm font-medium text-gray-700 mb-1">
                            Remarks (optional)
                        </label>
                        <textarea name="completion_remarks" id="completion_remarks" rows="2"
                            class="w-full border border-gray-300 rounded-md shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50 px-3 py-2 text-sm"
                            placeholder="Add any remarks..."></textarea>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Mark as Completed
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
