@extends('layouts.app')

@section('title', 'Request Details')

@section('content')
<!-- Action Animation Overlay -->
<div id="action-animation-overlay" class="fixed inset-0 hidden" style="z-index: 9999;">
    <div class="absolute inset-0 bg-gray-900/80 backdrop-blur-sm"></div>
    <div class="relative h-full flex flex-col items-center justify-center">
        <!-- Approve Animation Container (envelope flying) -->
        <div id="approve-animation" class="hidden">
            <div class="relative w-80 h-48">
                <!-- Document/Envelope Icon -->
                <div id="approve-envelope-icon" class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2">
                    <svg id="approve-doc-svg" class="w-20 h-20 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 2l5 5h-5V4zM6 20V4h5v7h7v9H6z"/>
                        <path d="M8 12h8v2H8zm0 4h8v2H8z"/>
                    </svg>
                    <svg id="approve-envelope-svg" class="w-20 h-20 text-white hidden" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                    </svg>
                </div>

                <!-- Trail particles -->
                <div id="approve-trail-container" class="absolute inset-0 pointer-events-none"></div>

                <!-- Destination Icon (PO Admin) -->
                <div id="approve-destination-icon" class="absolute right-0 top-1/2 -translate-y-1/2 opacity-0">
                    <div class="flex flex-col items-center">
                        <div class="w-16 h-16 bg-purple-600 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <span class="text-white text-sm mt-2 font-medium">PO Admin</span>
                    </div>
                </div>

                <!-- Success Checkmark -->
                <div id="approve-success-checkmark" class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 opacity-0 scale-0">
                    <div class="w-24 h-24 bg-green-500 rounded-full flex items-center justify-center">
                        <svg class="w-14 h-14 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Animation Container (simple X) -->
        <div id="reject-animation" class="hidden">
            <div id="reject-icon">
                <div class="w-24 h-24 bg-red-500 rounded-full flex items-center justify-center animate-action-pop">
                    <svg class="w-14 h-14 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Cancel Animation Container (orange X) -->
        <div id="cancel-animation" class="hidden">
            <div class="w-24 h-24 bg-orange-500 rounded-full flex items-center justify-center animate-action-pop">
                <svg class="w-14 h-14 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
        </div>

        <!-- Delete Animation Container (red trash) -->
        <div id="delete-animation" class="hidden">
            <div class="w-24 h-24 bg-red-600 rounded-full flex items-center justify-center animate-action-pop">
                <svg class="w-14 h-14 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
        </div>

        <!-- Assign Animation Container (document to user) -->
        <div id="assign-animation" class="hidden">
            <div class="relative w-80 h-48">
                <!-- Document Icon -->
                <div id="assign-doc-icon" class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2">
                    <svg class="w-20 h-20 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 2l5 5h-5V4zM6 20V4h5v7h7v9H6z"/>
                        <path d="M8 12h8v2H8zm0 4h8v2H8z"/>
                    </svg>
                </div>

                <!-- Trail particles -->
                <div id="assign-trail-container" class="absolute inset-0 pointer-events-none"></div>

                <!-- Destination Icon (User) -->
                <div id="assign-destination-icon" class="absolute right-0 top-1/2 -translate-y-1/2 opacity-0">
                    <div class="flex flex-col items-center">
                        <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <span id="assign-user-name" class="text-white text-sm mt-2 font-medium">User</span>
                    </div>
                </div>

                <!-- Success Checkmark -->
                <div id="assign-success-checkmark" class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 opacity-0 scale-0">
                    <div class="w-24 h-24 bg-blue-500 rounded-full flex items-center justify-center">
                        <svg class="w-14 h-14 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Text -->
        <p id="action-status-text" class="mt-8 text-xl text-white font-medium"></p>
    </div>
</div>

<style>
    @keyframes action-pop {
        0% { transform: scale(0); opacity: 0; }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); opacity: 1; }
    }
    .animate-action-pop {
        animation: action-pop 0.4s ease-out forwards;
    }

    @keyframes pulse-scale {
        0%, 100% { transform: translate(-50%, -50%) scale(1); }
        50% { transform: translate(-50%, -50%) scale(1.1); }
    }

    @keyframes doc-to-envelope {
        0% { transform: translate(-50%, -50%) scale(1) rotateY(0deg); }
        50% { transform: translate(-50%, -50%) scale(0.8) rotateY(90deg); }
        100% { transform: translate(-50%, -50%) scale(1) rotateY(0deg); }
    }

    @keyframes fly-to-destination {
        0% { left: 50%; top: 50%; transform: translate(-50%, -50%) scale(1); }
        30% { left: 55%; top: 35%; transform: translate(-50%, -50%) scale(1.1) rotate(-10deg); }
        70% { left: 75%; top: 40%; transform: translate(-50%, -50%) scale(0.9) rotate(5deg); }
        100% { left: 85%; top: 50%; transform: translate(-50%, -50%) scale(0.6); opacity: 0; }
    }

    @keyframes fade-in-up {
        0% { opacity: 0; transform: translate(-50%, -50%) scale(0); }
        50% { transform: translate(-50%, -50%) scale(1.2); }
        100% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
    }

    @keyframes particle-trail {
        0% { opacity: 1; transform: scale(1); }
        100% { opacity: 0; transform: scale(0); }
    }

    @keyframes destination-appear {
        0% { opacity: 0; transform: translateY(-50%) scale(0.5); }
        100% { opacity: 1; transform: translateY(-50%) scale(1); }
    }

    .animate-pulse-doc { animation: pulse-scale 0.8s ease-in-out infinite; }
    .animate-transform { animation: doc-to-envelope 0.5s ease-in-out forwards; }
    .animate-fly { animation: fly-to-destination 1s ease-in-out forwards; }
    .animate-success { animation: fade-in-up 0.5s ease-out forwards; }
    .animate-destination { animation: destination-appear 0.3s ease-out forwards; }

    .particle {
        position: absolute;
        width: 8px;
        height: 8px;
        background: linear-gradient(135deg, #a78bfa, #7c3aed);
        border-radius: 50%;
        animation: particle-trail 0.5s ease-out forwards;
    }
    .particle-blue {
        position: absolute;
        width: 8px;
        height: 8px;
        background: linear-gradient(135deg, #60a5fa, #2563eb);
        border-radius: 50%;
        animation: particle-trail 0.5s ease-out forwards;
    }
</style>

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
            @if($request->status === 'group_pending') bg-indigo-50 border-b border-indigo-200
            @elseif($request->status === 'pending') bg-yellow-50 border-b border-yellow-200
            @elseif($request->status === 'approved') bg-blue-50 border-b border-blue-200
            @elseif($request->status === 'assigned') bg-purple-50 border-b border-purple-200
            @elseif($request->status === 'in_progress') bg-orange-50 border-b border-orange-200
            @elseif($request->status === 'completed') bg-green-50 border-b border-green-200
            @elseif($request->status === 'cancelled') bg-gray-50 border-b border-gray-200
            @elseif($request->status === 'clarification_needed') bg-amber-50 border-b border-amber-200
            @else bg-red-50 border-b border-red-200
            @endif">
            <div class="flex items-center justify-between">
                <span class="px-3 py-1 text-sm font-semibold rounded-full
                    @if($request->status === 'group_pending') bg-indigo-100 text-indigo-800
                    @elseif($request->status === 'pending') bg-yellow-100 text-yellow-800
                    @elseif($request->status === 'approved') bg-blue-100 text-blue-800
                    @elseif($request->status === 'assigned') bg-purple-100 text-purple-800
                    @elseif($request->status === 'in_progress') bg-orange-100 text-orange-800
                    @elseif($request->status === 'completed') bg-green-100 text-green-800
                    @elseif($request->status === 'cancelled') bg-gray-100 text-gray-800
                    @elseif($request->status === 'clarification_needed') bg-amber-100 text-amber-800
                    @else bg-red-100 text-red-800
                    @endif">
                    @if($request->status === 'group_pending') Pending Group Approval
                    @else {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                    @endif
                </span>
                <div class="flex items-center gap-3">
                    @if($request->priority === 'urgent')
                        <span class="px-2.5 py-1 text-xs font-bold bg-red-100 text-red-700 rounded-full border border-red-300 uppercase tracking-wide">
                            ⚠ Urgent
                        </span>
                    @endif
                    <span class="text-sm text-gray-500">
                        Created {{ $request->created_at ? $request->created_at->format('M d, Y \a\t h:i A') : 'N/A' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Clarification Needed Alert -->
        @if($request->needsClarification())
            <div class="mx-6 mt-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                <div class="flex">
                    <svg class="w-5 h-5 text-amber-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <h4 class="text-sm font-medium text-amber-800">Clarification Requested</h4>
                        <p class="mt-1 text-sm text-amber-700">{{ $request->clarificationRequester->name ?? 'Admin' }} requested clarification on {{ $request->clarification_requested_at ? $request->clarification_requested_at->format('M d, Y \a\t h:i A') : '' }}</p>
                        <div class="mt-2 p-3 bg-white rounded border border-amber-100">
                            <p class="text-sm text-gray-700">{{ $request->clarification_remarks }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Rejected Alert -->
        @if($request->isRejected() && $request->rejection_remarks)
            <div class="mx-6 mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex">
                    <svg class="w-5 h-5 text-red-400 mr-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <h4 class="text-sm font-medium text-red-800">Request Rejected</h4>
                        <p class="mt-1 text-sm text-red-700">{{ $request->approver->name ?? 'Admin' }} rejected this request on {{ $request->approved_at ? $request->approved_at->format('M d, Y \a\t h:i A') : '' }}</p>
                        <div class="mt-2 p-3 bg-white rounded border border-red-100">
                            <p class="text-sm text-gray-700">{{ $request->rejection_remarks }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Request Details -->
        <div class="p-6">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if($request->items->isNotEmpty())
                <div class="md:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 mb-2">Items</dt>
                    <dd class="mt-1">
                        <div class="overflow-x-auto border border-gray-200 rounded-md">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-8">#</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-16">Qty</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-36">Specifications</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($request->items as $i => $lineItem)
                                        <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                                            <td class="px-3 py-2 text-gray-500">{{ $i + 1 }}</td>
                                            <td class="px-3 py-2 text-gray-900">{{ $lineItem->item }}</td>
                                            <td class="px-3 py-2 text-gray-900">{{ $lineItem->qty }}</td>
                                            <td class="px-3 py-2 text-gray-500">{{ $lineItem->specifications ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </dd>
                </div>
                @endif

                <div>
                    <dt class="text-sm font-medium text-gray-500">Category</dt>
                    <dd class="mt-1 text-lg text-gray-900">
                        @if($request->category)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                                {{ $request->category->name }}
                            </span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </dd>
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
                            @elseif($entry->action === 'cancelled') bg-orange-100
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
                                @elseif($entry->action === 'cancelled') text-orange-600
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

        <!-- Edit/Cancel Buttons for FMO User (own pending requests) -->
        @if(auth()->user()->isFmoUser() && $request->created_by === auth()->id() && $request->isPending())
            <div class="border-t border-gray-200 p-6">
                <div class="flex space-x-3">
                    <a href="{{ route('requests.edit', $request) }}"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Edit Request
                    </a>
                    <form id="cancel-form" action="{{ route('requests.cancel', $request) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 transition transform hover:scale-105">
                            Cancel Request
                        </button>
                    </form>
                </div>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const overlay = document.getElementById('action-animation-overlay');
                const cancelAnimation = document.getElementById('cancel-animation');
                const statusText = document.getElementById('action-status-text');
                const cancelForm = document.getElementById('cancel-form');

                function playCancelAnimation(redirectUrl) {
                    // Hide other animations
                    document.querySelectorAll('#action-animation-overlay > div > div[id$="-animation"]').forEach(el => el.classList.add('hidden'));

                    overlay.classList.remove('hidden');
                    cancelAnimation.classList.remove('hidden');
                    statusText.textContent = 'Cancelling...';

                    setTimeout(() => {
                        statusText.textContent = 'Cancelled!';
                    }, 400);

                    setTimeout(() => {
                        sessionStorage.setItem('flash_success', 'Request cancelled successfully.');
                        window.location.href = redirectUrl;
                    }, 1200);
                }

                if (cancelForm) {
                    cancelForm.addEventListener('submit', async function(e) {
                        e.preventDefault();

                        if (!confirm('Are you sure you want to cancel this request?')) {
                            return;
                        }

                        const submitBtn = this.querySelector('button[type="submit"]');
                        submitBtn.disabled = true;

                        try {
                            const formData = new FormData(this);
                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                            const response = await fetch(this.action, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                }
                            });

                            const contentType = response.headers.get('content-type');
                            if (!contentType || !contentType.includes('application/json')) {
                                this.submit();
                                return;
                            }

                            const data = await response.json();

                            if (response.ok && data.success) {
                                playCancelAnimation(data.redirect);
                            } else {
                                alert(data.message || 'An error occurred.');
                                submitBtn.disabled = false;
                            }
                        } catch (error) {
                            this.submit();
                        }
                    });
                }
            });
            </script>
        @endif

        <!-- Update and Resubmit for FMO User (own clarification_needed requests) -->
        @if(auth()->user()->isFmoUser() && $request->created_by === auth()->id() && $request->needsClarification())
            <div class="border-t border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Update and Resubmit</h3>
                <p class="text-sm text-gray-600 mb-4">Please review the clarification request above and update your request accordingly.</p>

                <form id="resubmit-form" action="{{ route('requests.resubmit', $request) }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Line Items Table -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-700">
                                Items <span class="text-red-500">*</span>
                            </label>
                            <button type="button" id="resubmit-add-item-btn"
                                class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 transition">
                                + Add Item
                            </button>
                        </div>
                        <div class="overflow-x-auto border border-gray-200 rounded-md">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-8">#</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item Name *</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-24">Qty *</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-40">Specifications</th>
                                        <th class="px-3 py-2 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody id="resubmit-items-tbody">
                                    @forelse($request->items as $idx => $lineItem)
                                        <tr class="resubmit-item-row" data-index="{{ $idx }}">
                                            <td class="px-3 py-2 text-sm text-gray-500 row-num">{{ $idx + 1 }}</td>
                                            <td class="px-3 py-2">
                                                <input type="text" name="items[{{ $idx }}][item]" required
                                                    value="{{ old('items.' . $idx . '.item', $lineItem->item) }}"
                                                    class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="number" name="items[{{ $idx }}][qty]" min="1" required
                                                    value="{{ old('items.' . $idx . '.qty', $lineItem->qty) }}"
                                                    class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="text" name="items[{{ $idx }}][specifications]"
                                                    value="{{ old('items.' . $idx . '.specifications', $lineItem->specifications) }}"
                                                    placeholder="e.g. 10x20 cm"
                                                    class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            </td>
                                            <td class="px-3 py-2">
                                                <button type="button" class="resubmit-remove-row text-gray-300 hover:text-red-500 transition">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="resubmit-item-row" data-index="0">
                                            <td class="px-3 py-2 text-sm text-gray-500 row-num">1</td>
                                            <td class="px-3 py-2">
                                                <input type="text" name="items[0][item]" required
                                                    class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="number" name="items[0][qty]" min="1" value="1" required
                                                    class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="text" name="items[0][specifications]" placeholder="e.g. 10x20 cm"
                                                    class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            </td>
                                            <td class="px-3 py-2">
                                                <button type="button" class="resubmit-remove-row text-gray-300 hover:text-red-500 transition">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <p id="resubmit-items-min-error" class="mt-1 text-sm text-red-500 hidden">At least one item is required.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="resubmit_location" class="block text-sm font-medium text-gray-700">Location *</label>
                            <input type="text" name="location" id="resubmit_location" value="{{ old('location', $request->location) }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="md:col-span-2">
                            <label for="resubmit_remarks" class="block text-sm font-medium text-gray-700">Remarks</label>
                            <textarea name="remarks" id="resubmit_remarks" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('remarks', $request->remarks) }}</textarea>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition transform hover:scale-105">
                            Resubmit Request
                        </button>
                    </div>
                </form>

                <script>
                (function() {
                    const tbody = document.getElementById('resubmit-items-tbody');
                    const addBtn = document.getElementById('resubmit-add-item-btn');
                    let rowCount = {{ $request->items->count() > 0 ? $request->items->count() : 1 }};

                    function buildRow(idx) {
                        const tr = document.createElement('tr');
                        tr.className = 'resubmit-item-row';
                        tr.dataset.index = idx;
                        tr.innerHTML = `
                            <td class="px-3 py-2 text-sm text-gray-500 row-num"></td>
                            <td class="px-3 py-2"><input type="text" name="items[${idx}][item]" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm"></td>
                            <td class="px-3 py-2"><input type="number" name="items[${idx}][qty]" min="1" value="1" required class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm"></td>
                            <td class="px-3 py-2"><input type="text" name="items[${idx}][specifications]" placeholder="e.g. 10x20 cm" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm"></td>
                            <td class="px-3 py-2"><button type="button" class="resubmit-remove-row text-gray-300 hover:text-red-500 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></td>`;
                        return tr;
                    }

                    function renumber() {
                        tbody.querySelectorAll('.resubmit-item-row').forEach((r, i) => r.querySelector('.row-num').textContent = i + 1);
                    }

                    addBtn.addEventListener('click', function() {
                        const tr = buildRow(rowCount++);
                        tbody.appendChild(tr);
                        renumber();
                        tr.querySelector('input[type="text"]').focus();
                    });

                    tbody.addEventListener('click', function(e) {
                        const btn = e.target.closest('.resubmit-remove-row');
                        if (!btn) return;
                        if (tbody.querySelectorAll('.resubmit-item-row').length <= 1) {
                            document.getElementById('resubmit-items-min-error').classList.remove('hidden');
                            return;
                        }
                        document.getElementById('resubmit-items-min-error').classList.add('hidden');
                        btn.closest('.resubmit-item-row').remove();
                        renumber();
                    });
                })();
                </script>
                <form id="cancel-clarification-form" action="{{ route('requests.cancel', $request) }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" onclick="return confirm('Are you sure you want to cancel this request?')"
                        class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700">
                        Cancel Request
                    </button>
                </form>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const overlay = document.getElementById('action-animation-overlay');
                const statusText = document.getElementById('action-status-text');
                const resubmitForm = document.getElementById('resubmit-form');

                if (resubmitForm) {
                    resubmitForm.addEventListener('submit', async function(e) {
                        e.preventDefault();

                        const submitBtn = this.querySelector('button[type="submit"]');
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Resubmitting...';

                        try {
                            const formData = new FormData(this);
                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                            const response = await fetch(this.action, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                }
                            });

                            const contentType = response.headers.get('content-type');
                            if (!contentType || !contentType.includes('application/json')) {
                                this.submit();
                                return;
                            }

                            const data = await response.json();

                            if (response.ok && data.success) {
                                sessionStorage.setItem('flash_success', data.message);
                                window.location.href = data.redirect;
                            } else {
                                alert(data.message || 'An error occurred.');
                                submitBtn.disabled = false;
                                submitBtn.textContent = 'Resubmit Request';
                            }
                        } catch (error) {
                            this.submit();
                        }
                    });
                }
            });
            </script>
        @endif

        <!-- Delete Button for FMO User (own rejected requests) -->
        @if(auth()->user()->isFmoUser() && $request->created_by === auth()->id() && $request->isRejected())
            <div class="border-t border-gray-200 p-6">
                <form id="delete-form" action="{{ route('requests.destroy', $request) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition transform hover:scale-105">
                        Delete Request
                    </button>
                </form>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const overlay = document.getElementById('action-animation-overlay');
                const deleteAnimation = document.getElementById('delete-animation');
                const statusText = document.getElementById('action-status-text');
                const deleteForm = document.getElementById('delete-form');

                function playDeleteAnimation(redirectUrl) {
                    // Hide other animations
                    document.querySelectorAll('#action-animation-overlay > div > div[id$="-animation"]').forEach(el => el.classList.add('hidden'));

                    overlay.classList.remove('hidden');
                    deleteAnimation.classList.remove('hidden');
                    statusText.textContent = 'Deleting...';

                    setTimeout(() => {
                        statusText.textContent = 'Deleted!';
                    }, 400);

                    setTimeout(() => {
                        sessionStorage.setItem('flash_success', 'Request deleted successfully.');
                        window.location.href = redirectUrl;
                    }, 1200);
                }

                if (deleteForm) {
                    deleteForm.addEventListener('submit', async function(e) {
                        e.preventDefault();

                        if (!confirm('Are you sure you want to permanently delete this request? This action cannot be undone.')) {
                            return;
                        }

                        const submitBtn = this.querySelector('button[type="submit"]');
                        submitBtn.disabled = true;

                        try {
                            const formData = new FormData(this);
                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                            const response = await fetch(this.action, {
                                method: 'DELETE',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                }
                            });

                            const contentType = response.headers.get('content-type');
                            if (!contentType || !contentType.includes('application/json')) {
                                this.submit();
                                return;
                            }

                            const data = await response.json();

                            if (response.ok && data.success) {
                                playDeleteAnimation(data.redirect);
                            } else {
                                alert(data.message || 'An error occurred.');
                                submitBtn.disabled = false;
                            }
                        } catch (error) {
                            this.submit();
                        }
                    });
                }
            });
            </script>
        @endif

        <!-- Actions for Group Approver (group_pending or pending-but-unapproved requests) -->
        @php
            // Show approver actions if user is the effective approver for this request's category
            $isGroupApprover = false;
            if ($request->isGroupPending() || ($request->isPending() && !$request->approved_by)) {
                $approverGroup = \App\Models\UserGroup::where('type', 'fmo')
                    ->whereHas('members', fn($q) => $q->where('users.id', $request->created_by))
                    ->with('categoryApprovers')
                    ->first();
                if ($approverGroup) {
                    $isGroupApprover = $approverGroup->getApproverForCategory($request->category_id) === auth()->id();
                }
            }
        @endphp
        @if($isGroupApprover)
            <div class="border-t border-gray-200 p-6 bg-blue-50">
                <h4 class="text-sm font-semibold text-blue-800 mb-3">Group Approval Required</h4>
                <div class="flex space-x-3">
                    <form action="{{ route('requests.group-approve', $request) }}" method="POST" id="group-approve-form">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition transform hover:scale-105">
                            Approve &amp; Forward to FMO Admin
                        </button>
                    </form>
                    <a href="{{ route('requests.edit', $request) }}"
                        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition text-sm font-medium inline-flex items-center">
                        Edit Request
                    </a>
                    <button type="button" onclick="document.getElementById('group-reject-modal').classList.remove('hidden')"
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition transform hover:scale-105">
                        Reject
                    </button>
                    <button type="button" onclick="document.getElementById('group-clarification-modal').classList.remove('hidden')"
                        class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition transform hover:scale-105">
                        Need Clarification
                    </button>
                </div>
            </div>

            <!-- Group Clarification Modal -->
            <div id="group-clarification-modal" class="fixed inset-0 z-50 hidden" aria-modal="true">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" onclick="document.getElementById('group-clarification-modal').classList.add('hidden')"></div>
                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Request Clarification</h3>
                            <p class="text-sm text-gray-600 mb-4">Provide details about what clarification is needed from the initiator.</p>
                            <form action="{{ route('requests.group-clarification', $request) }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label for="group_clarification_remarks" class="block text-sm font-medium text-gray-700 mb-1">Remarks *</label>
                                    <textarea name="clarification_remarks" id="group_clarification_remarks" rows="4" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="What clarification is needed?"></textarea>
                                </div>
                                <div class="flex justify-end space-x-3">
                                    <button type="button" onclick="document.getElementById('group-clarification-modal').classList.add('hidden')"
                                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</button>
                                    <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">Send for Clarification</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Group Reject Modal -->
            <div id="group-reject-modal" class="fixed inset-0 z-50 hidden" aria-modal="true">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" onclick="document.getElementById('group-reject-modal').classList.add('hidden')"></div>
                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Request</h3>
                            <p class="text-sm text-gray-600 mb-4">Optionally provide a reason for rejection.</p>
                            <form action="{{ route('requests.group-reject', $request) }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label for="group_rejection_remarks" class="block text-sm font-medium text-gray-700 mb-1">Reason for rejection</label>
                                    <textarea name="rejection_remarks" id="group_rejection_remarks" rows="4"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                        placeholder="Optional — describe why this request is being rejected."></textarea>
                                </div>
                                <div class="flex justify-end space-x-3">
                                    <button type="button" onclick="document.getElementById('group-reject-modal').classList.add('hidden')"
                                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</button>
                                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Confirm Rejection</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
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
                    <form id="approve-form" action="{{ route('requests.approve', $request) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition transform hover:scale-105">
                            Approve Request
                        </button>
                    </form>
                    <button type="button" onclick="document.getElementById('reject-modal').classList.remove('hidden')"
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition transform hover:scale-105">
                        Reject Request
                    </button>
                    <button type="button" onclick="document.getElementById('clarification-modal').classList.remove('hidden')"
                        class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition transform hover:scale-105">
                        Need Clarification
                    </button>
                </div>
            </div>

            <!-- Clarification Modal -->
            <div id="clarification-modal" class="fixed inset-0 z-50 hidden" aria-modal="true">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" onclick="document.getElementById('clarification-modal').classList.add('hidden')"></div>
                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Request Clarification</h3>
                            <p class="text-sm text-gray-600 mb-4">Please provide details about what clarification is needed from the initiator.</p>
                            <form id="clarification-form" action="{{ route('requests.clarification', $request) }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label for="clarification_remarks" class="block text-sm font-medium text-gray-700 mb-1">Remarks *</label>
                                    <textarea name="clarification_remarks" id="clarification_remarks" rows="4" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="What clarification is needed?"></textarea>
                                </div>
                                <div class="flex justify-end space-x-3">
                                    <button type="button" onclick="document.getElementById('clarification-modal').classList.add('hidden')"
                                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                        Cancel
                                    </button>
                                    <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                                        Send for Clarification
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reject Modal -->
            <div id="reject-modal" class="fixed inset-0 z-50 hidden" aria-modal="true">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" onclick="document.getElementById('reject-modal').classList.add('hidden')"></div>
                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Request</h3>
                            <p class="text-sm text-gray-600 mb-4">Optionally provide a reason for rejection. This will be included in the notification sent to the initiator.</p>
                            <form id="reject-form" action="{{ route('requests.reject', $request) }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label for="rejection_remarks" class="block text-sm font-medium text-gray-700 mb-1">Reason for rejection</label>
                                    <textarea name="rejection_remarks" id="rejection_remarks" rows="4"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                        placeholder="Optional — describe why this request is being rejected."></textarea>
                                </div>
                                <div class="flex justify-end space-x-3">
                                    <button type="button" onclick="document.getElementById('reject-modal').classList.add('hidden')"
                                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                        Cancel
                                    </button>
                                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                        Confirm Rejection
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const overlay = document.getElementById('action-animation-overlay');
                const approveAnimation = document.getElementById('approve-animation');
                const rejectAnimation = document.getElementById('reject-animation');
                const statusText = document.getElementById('action-status-text');
                const approveForm = document.getElementById('approve-form');
                const rejectForm = document.getElementById('reject-form');

                // Approve animation elements
                const envelopeIcon = document.getElementById('approve-envelope-icon');
                const docSvg = document.getElementById('approve-doc-svg');
                const envelopeSvg = document.getElementById('approve-envelope-svg');
                const destinationIcon = document.getElementById('approve-destination-icon');
                const successCheckmark = document.getElementById('approve-success-checkmark');
                const trailContainer = document.getElementById('approve-trail-container');

                function createTrailParticles() {
                    const positions = [
                        { left: '50%', top: '50%', delay: 0 },
                        { left: '55%', top: '40%', delay: 100 },
                        { left: '60%', top: '35%', delay: 200 },
                        { left: '65%', top: '38%', delay: 300 },
                        { left: '70%', top: '42%', delay: 400 },
                        { left: '75%', top: '45%', delay: 500 },
                    ];

                    positions.forEach(pos => {
                        setTimeout(() => {
                            const particle = document.createElement('div');
                            particle.className = 'particle';
                            particle.style.left = pos.left;
                            particle.style.top = pos.top;
                            trailContainer.appendChild(particle);
                            setTimeout(() => particle.remove(), 500);
                        }, pos.delay);
                    });
                }

                function playApproveAnimation(redirectUrl) {
                    // Show overlay and approve animation
                    overlay.classList.remove('hidden');
                    approveAnimation.classList.remove('hidden');
                    rejectAnimation.classList.add('hidden');
                    statusText.textContent = 'Sending to PO Admin...';

                    // Reset states
                    docSvg.classList.remove('hidden');
                    envelopeSvg.classList.add('hidden');
                    successCheckmark.style.opacity = '0';
                    successCheckmark.style.transform = 'translate(-50%, -50%) scale(0)';
                    successCheckmark.classList.remove('animate-success');
                    destinationIcon.style.opacity = '0';
                    destinationIcon.classList.remove('animate-destination');
                    envelopeIcon.style.cssText = '';
                    envelopeIcon.style.display = '';
                    envelopeIcon.classList.remove('animate-pulse-doc', 'animate-transform', 'animate-fly');
                    trailContainer.innerHTML = '';

                    // Step 1: Document pulses
                    envelopeIcon.classList.add('animate-pulse-doc');

                    setTimeout(() => {
                        // Step 2: Transform to envelope
                        envelopeIcon.classList.remove('animate-pulse-doc');
                        envelopeIcon.classList.add('animate-transform');
                        setTimeout(() => {
                            docSvg.classList.add('hidden');
                            envelopeSvg.classList.remove('hidden');
                        }, 250);
                    }, 800);

                    setTimeout(() => {
                        // Step 3: Show destination
                        destinationIcon.classList.add('animate-destination');
                    }, 1100);

                    setTimeout(() => {
                        // Step 4: Fly to destination
                        envelopeIcon.classList.remove('animate-transform');
                        envelopeIcon.classList.add('animate-fly');
                        createTrailParticles();
                    }, 1300);

                    setTimeout(() => {
                        // Step 5: Show success
                        envelopeIcon.style.display = 'none';
                        destinationIcon.style.opacity = '0';
                        successCheckmark.classList.add('animate-success');
                        statusText.textContent = 'Approved & Sent!';
                    }, 2300);

                    setTimeout(() => {
                        sessionStorage.setItem('flash_success', 'Request approved successfully.');
                        window.location.href = redirectUrl;
                    }, 3000);
                }

                function playRejectAnimation(redirectUrl) {
                    // Show overlay and reject animation
                    overlay.classList.remove('hidden');
                    approveAnimation.classList.add('hidden');
                    rejectAnimation.classList.remove('hidden');
                    statusText.textContent = 'Rejecting...';

                    setTimeout(() => {
                        statusText.textContent = 'Rejected!';
                    }, 400);

                    setTimeout(() => {
                        sessionStorage.setItem('flash_success', 'Request rejected.');
                        window.location.href = redirectUrl;
                    }, 1200);
                }

                async function handleActionSubmit(form, type) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    submitBtn.disabled = true;

                    try {
                        const formData = new FormData(form);
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });

                        const contentType = response.headers.get('content-type');
                        if (!contentType || !contentType.includes('application/json')) {
                            form.submit();
                            return;
                        }

                        const data = await response.json();

                        if (response.ok && data.success) {
                            if (type === 'approve') {
                                playApproveAnimation(data.redirect);
                            } else {
                                playRejectAnimation(data.redirect);
                            }
                        } else {
                            alert(data.message || 'An error occurred.');
                            submitBtn.disabled = false;
                        }
                    } catch (error) {
                        form.submit();
                    }
                }

                if (approveForm) {
                    approveForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        handleActionSubmit(this, 'approve');
                    });
                }

                if (rejectForm) {
                    rejectForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        handleActionSubmit(this, 'reject');
                    });
                }
            });
            </script>
        @endif

        <!-- Escalation to Purchase Office for FMO Admin (approved/assigned/in_progress) -->
        @if((auth()->user()->isFmoAdmin() || auth()->user()->isSuperAdmin()) &&
            ($request->isApproved() || $request->isAssigned() || $request->isInProgress()))
            <div class="border-t border-gray-200 p-6 bg-red-50">
                <h4 class="text-md font-medium text-gray-900 mb-4">Escalation to Purchase Office</h4>

                @if(!$request->assignee || !$request->assigner)
                    <div class="p-4 bg-yellow-100 border border-yellow-300 rounded-lg mb-4">
                        <p class="text-sm text-yellow-800">
                            This request has not been assigned to a Purchase Office team member yet.
                            Assignment is required to escalate.
                        </p>
                    </div>
                    <button disabled class="px-4 py-2 bg-gray-400 text-white rounded-md cursor-not-allowed">
                        Send Clarification Request (Not Assigned)
                    </button>
                @else
                    <p class="text-sm text-gray-600 mb-4">
                        Send a clarification request directly to the assigned PO team member
                        (<strong>{{ $request->assignee->name }}</strong>) and
                        PO Admin (<strong>{{ $request->assigner->name }}</strong>).
                    </p>
                    <a href="{{ $request->getEscalationMailtoLink() }}"
                       target="_blank"
                       class="inline-block px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">
                        Send Clarification Request
                    </a>
                    <p class="text-xs text-gray-500 mt-2">
                        This will open Gmail in a new tab with pre-filled recipient addresses and request details.
                    </p>
                @endif
            </div>
        @endif

        <!-- Actions for PO Admin (approved requests - ready to assign) -->
        @if((auth()->user()->isPoAdmin() || auth()->user()->isSuperAdmin()) && $request->isApproved())
            <div class="border-t border-gray-200 p-6">
                <h4 class="text-md font-medium text-gray-900 mb-4">Assign Request</h4>
                <form id="assign-form" action="{{ route('requests.assign', $request) }}" method="POST" class="bg-blue-50 p-4 rounded-lg max-w-md">
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
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition transform hover:scale-105">
                        Assign Request
                    </button>
                </form>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const overlay = document.getElementById('action-animation-overlay');
                const assignAnimation = document.getElementById('assign-animation');
                const statusText = document.getElementById('action-status-text');
                const assignForm = document.getElementById('assign-form');

                // Assign animation elements
                const assignDocIcon = document.getElementById('assign-doc-icon');
                const assignDestinationIcon = document.getElementById('assign-destination-icon');
                const assignSuccessCheckmark = document.getElementById('assign-success-checkmark');
                const assignTrailContainer = document.getElementById('assign-trail-container');
                const assignUserName = document.getElementById('assign-user-name');

                function createBlueTrailParticles() {
                    const positions = [
                        { left: '50%', top: '50%', delay: 0 },
                        { left: '55%', top: '40%', delay: 100 },
                        { left: '60%', top: '35%', delay: 200 },
                        { left: '65%', top: '38%', delay: 300 },
                        { left: '70%', top: '42%', delay: 400 },
                        { left: '75%', top: '45%', delay: 500 },
                    ];

                    positions.forEach(pos => {
                        setTimeout(() => {
                            const particle = document.createElement('div');
                            particle.className = 'particle-blue';
                            particle.style.left = pos.left;
                            particle.style.top = pos.top;
                            assignTrailContainer.appendChild(particle);
                            setTimeout(() => particle.remove(), 500);
                        }, pos.delay);
                    });
                }

                function playAssignAnimation(redirectUrl, assigneeName) {
                    // Hide other animations, show assign
                    const approveAnim = document.getElementById('approve-animation');
                    const rejectAnim = document.getElementById('reject-animation');
                    if (approveAnim) approveAnim.classList.add('hidden');
                    if (rejectAnim) rejectAnim.classList.add('hidden');

                    overlay.classList.remove('hidden');
                    assignAnimation.classList.remove('hidden');
                    statusText.textContent = 'Assigning...';
                    assignUserName.textContent = assigneeName;

                    // Reset states
                    assignDocIcon.style.cssText = '';
                    assignDocIcon.style.display = '';
                    assignDocIcon.classList.remove('animate-pulse-doc', 'animate-fly');
                    assignSuccessCheckmark.style.opacity = '0';
                    assignSuccessCheckmark.style.transform = 'translate(-50%, -50%) scale(0)';
                    assignSuccessCheckmark.classList.remove('animate-success');
                    assignDestinationIcon.style.opacity = '0';
                    assignDestinationIcon.classList.remove('animate-destination');
                    assignTrailContainer.innerHTML = '';

                    // Step 1: Document pulses
                    assignDocIcon.classList.add('animate-pulse-doc');

                    setTimeout(() => {
                        // Step 2: Show destination
                        assignDestinationIcon.classList.add('animate-destination');
                    }, 600);

                    setTimeout(() => {
                        // Step 3: Fly to destination
                        assignDocIcon.classList.remove('animate-pulse-doc');
                        assignDocIcon.classList.add('animate-fly');
                        createBlueTrailParticles();
                    }, 800);

                    setTimeout(() => {
                        // Step 4: Show success
                        assignDocIcon.style.display = 'none';
                        assignDestinationIcon.style.opacity = '0';
                        assignSuccessCheckmark.classList.add('animate-success');
                        statusText.textContent = 'Assigned!';
                    }, 1800);

                    setTimeout(() => {
                        sessionStorage.setItem('flash_success', 'Request assigned successfully.');
                        window.location.href = redirectUrl;
                    }, 2500);
                }

                if (assignForm) {
                    assignForm.addEventListener('submit', async function(e) {
                        e.preventDefault();

                        const submitBtn = this.querySelector('button[type="submit"]');
                        const selectEl = this.querySelector('select[name="assigned_to"]');
                        const assigneeName = selectEl.options[selectEl.selectedIndex].text.replace(' (Self)', '');

                        submitBtn.disabled = true;

                        try {
                            const formData = new FormData(this);
                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                            const response = await fetch(this.action, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                }
                            });

                            const contentType = response.headers.get('content-type');
                            if (!contentType || !contentType.includes('application/json')) {
                                this.submit();
                                return;
                            }

                            const data = await response.json();

                            if (response.ok && data.success) {
                                playAssignAnimation(data.redirect, data.assignee_name || assigneeName);
                            } else {
                                alert(data.message || 'An error occurred.');
                                submitBtn.disabled = false;
                            }
                        } catch (error) {
                            this.submit();
                        }
                    });
                }
            });
            </script>
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

        <!-- Nudge / Update Request Section -->
        @php
            $user = auth()->user();
            $canSendNudge = ($user->isFmoUser() && $request->created_by === $user->id) || $user->isFmoAdmin() || $user->isSuperAdmin();
            $isAssignedToPo = in_array($request->status, ['assigned', 'in_progress']) && $request->assigned_to;
            $nudges = $request->nudges;
            $myUnreadNudges = $nudges->filter(fn($n) => $n->target_user_id === $user->id && !$n->isAcknowledged());
        @endphp

        {{-- PO side: show unread nudges as an alert at the top of this section --}}
        @if(($user->isPoUser() || $user->isPoAdmin() || $user->isSuperAdmin()) && $myUnreadNudges->isNotEmpty())
            <div class="border-t-4 border-amber-400 p-6 bg-amber-50">
                <h3 class="text-base font-semibold text-amber-800 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                    </svg>
                    {{ $myUnreadNudges->count() }} Unread Update {{ $myUnreadNudges->count() === 1 ? 'Request' : 'Requests' }}
                </h3>
                @foreach($myUnreadNudges as $nudge)
                    <div class="mb-4 bg-white border border-amber-200 rounded-lg p-4" id="nudge-block-{{ $nudge->id }}">
                        <p class="text-xs text-gray-500 mb-1">From <span class="font-medium">{{ $nudge->sender->name }}</span> · {{ $nudge->created_at->format('M d, Y \a\t h:i A') }}</p>
                        <p class="text-sm text-gray-800 mb-3">{{ $nudge->message }}</p>
                        <div class="flex gap-2 items-start flex-wrap">
                            <div class="flex-1 min-w-48">
                                <form id="reply-form-{{ $nudge->id }}" action="{{ route('nudges.reply', $nudge) }}" method="POST" class="flex gap-2">
                                    @csrf
                                    <textarea name="reply" rows="1" placeholder="Type a reply..."
                                        class="flex-1 text-sm rounded-md border-gray-300 shadow-sm focus:border-amber-300 focus:ring-amber-200 focus:ring-opacity-50 resize-none"></textarea>
                                    <button type="submit" class="px-3 py-1.5 bg-amber-600 text-white text-sm rounded-md hover:bg-amber-700 whitespace-nowrap">
                                        Send Reply
                                    </button>
                                </form>
                            </div>
                            <form action="{{ route('nudges.acknowledge', $nudge) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 bg-gray-100 text-gray-700 text-sm rounded-md hover:bg-gray-200">
                                    Acknowledge
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('[id^="reply-form-"]').forEach(form => {
                    form.addEventListener('submit', async function(e) {
                        e.preventDefault();
                        const submitBtn = this.querySelector('button[type="submit"]');
                        submitBtn.disabled = true;
                        try {
                            const formData = new FormData(this);
                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                            const response = await fetch(this.action, {
                                method: 'POST',
                                body: formData,
                                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                            });
                            const data = await response.json();
                            if (response.ok && data.success) {
                                sessionStorage.setItem('flash_success', 'Reply sent.');
                                window.location.reload();
                            } else {
                                alert(data.message || 'Failed to send reply.');
                                submitBtn.disabled = false;
                            }
                        } catch (err) {
                            this.submit();
                        }
                    });
                });
            });
            </script>
        @endif

        {{-- FMO side: show nudge send form when request is assigned/in_progress --}}
        @if($canSendNudge && $isAssignedToPo)
            <div class="border-t border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-medium text-gray-900">Request Update from PO</h3>
                    <button type="button" onclick="document.getElementById('nudge-form-area').classList.toggle('hidden')"
                        class="text-sm text-indigo-600 hover:underline">
                        + Send Update Request
                    </button>
                </div>

                {{-- Send nudge form (hidden by default) --}}
                <div id="nudge-form-area" class="hidden mb-4 bg-indigo-50 border border-indigo-100 rounded-lg p-4">
                    <p class="text-sm text-gray-600 mb-3">Send a message to <strong>{{ $request->assignee->name ?? 'the assigned user' }}</strong> requesting a status update.</p>
                    <form id="send-nudge-form" action="{{ route('nudges.store', $request) }}" method="POST" class="space-y-3">
                        @csrf
                        <textarea name="message" rows="3" required maxlength="500"
                            class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Please provide an update on this request. We need it by [date]. Any blockers?"></textarea>
                        <div class="flex gap-2">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">
                                Send Request
                            </button>
                            <button type="button" onclick="document.getElementById('nudge-form-area').classList.add('hidden')"
                                class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-md hover:bg-gray-200">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>

                {{-- History of nudges sent by FMO on this request --}}
                @php $fmoNudges = $nudges->filter(fn($n) => $n->sent_by === $user->id || $user->isFmoAdmin() || $user->isSuperAdmin()); @endphp
                @if($fmoNudges->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($fmoNudges as $nudge)
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs text-gray-500">Sent by <strong>{{ $nudge->sender->name }}</strong> to <strong>{{ $nudge->target->name }}</strong> · {{ $nudge->created_at->format('M d, Y \a\t h:i A') }}</span>
                                    @if($nudge->hasReply())
                                        <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded-full">Replied</span>
                                    @elseif($nudge->isAcknowledged())
                                        <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-full">Acknowledged</span>
                                    @else
                                        <span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-xs rounded-full">Awaiting Response</span>
                                    @endif
                                </div>
                                <p class="text-gray-700">{{ $nudge->message }}</p>
                                @if($nudge->hasReply())
                                    <div class="mt-2 pl-3 border-l-2 border-green-400">
                                        <p class="text-xs text-gray-500 mb-0.5">Reply from <strong>{{ $nudge->target->name }}</strong> · {{ $nudge->replied_at->format('M d, Y \a\t h:i A') }}</p>
                                        <p class="text-gray-700">{{ $nudge->reply }}</p>
                                    </div>
                                @elseif($nudge->isAcknowledged())
                                    <div class="mt-2 pl-3 border-l-2 border-gray-300">
                                        <p class="text-xs text-gray-500">Acknowledged on {{ $nudge->acknowledged_at->format('M d, Y \a\t h:i A') }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400">No update requests sent yet.</p>
                @endif
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const sendNudgeForm = document.getElementById('send-nudge-form');
                if (sendNudgeForm) {
                    sendNudgeForm.addEventListener('submit', async function(e) {
                        e.preventDefault();
                        const submitBtn = this.querySelector('button[type="submit"]');
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Sending...';

                        try {
                            const formData = new FormData(this);
                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                            const response = await fetch(this.action, {
                                method: 'POST',
                                body: formData,
                                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                            });
                            const data = await response.json();
                            if (response.ok && data.success) {
                                // Reset and hide form, reload page to show new nudge
                                sessionStorage.setItem('flash_success', 'Update request sent successfully.');
                                window.location.reload();
                            } else {
                                alert(data.message || 'Failed to send.');
                                submitBtn.disabled = false;
                                submitBtn.textContent = 'Send Request';
                            }
                        } catch (err) {
                            this.submit();
                        }
                    });
                }

            });
            </script>
        @endif
    </div>
</div>
@endsection
