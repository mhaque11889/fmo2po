@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
        <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-900">
            &larr; Back to Dashboard
        </a>
    </div>
</div>

<form action="{{ route('settings.update') }}" method="POST">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

        <!-- Left column: Dashboard Refresh + Notification Sound -->
        <div class="space-y-6">

        <!-- Dashboard Refresh Settings -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Dashboard Refresh</h2>

            <div class="mb-4">
                <label for="refresh_interval" class="block text-sm font-medium text-gray-700 mb-1">
                    Auto-refresh Interval
                </label>
                <select name="refresh_interval" id="refresh_interval"
                        class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="0" {{ $settings['refresh_interval'] == 0 ? 'selected' : '' }}>Disabled</option>
                    <option value="30" {{ $settings['refresh_interval'] == 30 ? 'selected' : '' }}>30 seconds</option>
                    <option value="60" {{ $settings['refresh_interval'] == 60 ? 'selected' : '' }}>1 minute</option>
                    <option value="120" {{ $settings['refresh_interval'] == 120 ? 'selected' : '' }}>2 minutes</option>
                    <option value="300" {{ $settings['refresh_interval'] == 300 ? 'selected' : '' }}>5 minutes</option>
                    <option value="600" {{ $settings['refresh_interval'] == 600 ? 'selected' : '' }}>10 minutes</option>
                </select>
                <p class="mt-1 text-sm text-gray-500">How often the dashboard should automatically refresh to show new data.</p>
            </div>
        </div>

        <!-- Notification Settings -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Notification Sound</h2>

            <div class="mb-4">
                <label for="notification_sound" class="block text-sm font-medium text-gray-700 mb-1">
                    Sound Effect
                </label>
                <div class="flex items-center gap-2">
                    <select name="notification_sound" id="notification_sound"
                            class="flex-1 border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="none" {{ $settings['notification_sound'] === 'none' ? 'selected' : '' }}>None (Silent)</option>
                        <option value="chime" {{ $settings['notification_sound'] === 'chime' ? 'selected' : '' }}>Chime</option>
                        <option value="bell" {{ $settings['notification_sound'] === 'bell' ? 'selected' : '' }}>Bell</option>
                        <option value="ping" {{ $settings['notification_sound'] === 'ping' ? 'selected' : '' }}>Ping</option>
                    </select>
                    <button type="button" id="testSound"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition">
                        Test
                    </button>
                </div>
                <p class="mt-1 text-sm text-gray-500">Sound to play when notifications are triggered.</p>
            </div>

            <h3 class="text-md font-medium text-gray-800 mb-3 mt-6">Notify me when:</h3>

            <div class="space-y-3">
                <label class="flex items-center">
                    <input type="checkbox" name="notify_on_new_request" value="1"
                           {{ $settings['notify_on_new_request'] ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">New request is created</span>
                </label>

                <label class="flex items-center">
                    <input type="checkbox" name="notify_on_status_change" value="1"
                           {{ $settings['notify_on_status_change'] ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">Request status changes</span>
                </label>

                <label class="flex items-center">
                    <input type="checkbox" name="notify_on_task_assigned" value="1"
                           {{ $settings['notify_on_task_assigned'] ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">Task is assigned to me</span>
                </label>
            </div>
        </div>

        </div><!-- end left column -->

        <!-- Right column: Email Notifications -->
        <div>

        <!-- Email Notification Settings -->
        <div class="bg-white rounded-lg shadow p-6"
             x-data="{ emailPref: '{{ $settings['email_notifications'] }}' }">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Email Notifications</h2>
            <p class="text-sm text-gray-500 mb-4">Choose when you want to receive email updates about requests.</p>

            <div class="space-y-3 mb-4">
                <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition"
                       :class="emailPref === 'all' ? 'border-indigo-400 bg-indigo-50' : ''">
                    <input type="radio" name="email_notifications" value="all"
                           x-model="emailPref"
                           class="mt-0.5 text-indigo-600">
                    <div>
                        <span class="text-sm font-medium text-gray-900">All status updates</span>
                        <p class="text-xs text-gray-500 mt-0.5">Email me at every stage of a request's lifecycle.</p>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition"
                       :class="emailPref === 'key_only' ? 'border-indigo-400 bg-indigo-50' : ''">
                    <input type="radio" name="email_notifications" value="key_only"
                           x-model="emailPref"
                           class="mt-0.5 text-indigo-600">
                    <div>
                        <span class="text-sm font-medium text-gray-900">Key updates only <span class="text-xs font-normal text-indigo-600 ml-1">Default</span></span>
                        <p class="text-xs text-gray-500 mt-0.5">
                            @if(auth()->user()->isFmoUser())
                                Approved, rejected, clarification needed, and completed.
                            @elseif(auth()->user()->isFmoAdmin())
                                New submissions, resubmissions, and completions.
                            @elseif(auth()->user()->isPoAdmin())
                                New requests ready to assign, and completions.
                            @elseif(auth()->user()->isPoUser())
                                When a request is assigned to you.
                            @endif
                        </p>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition"
                       :class="emailPref === 'custom' ? 'border-indigo-400 bg-indigo-50' : ''">
                    <input type="radio" name="email_notifications" value="custom"
                           x-model="emailPref"
                           class="mt-0.5 text-indigo-600">
                    <div>
                        <span class="text-sm font-medium text-gray-900">Custom</span>
                        <p class="text-xs text-gray-500 mt-0.5">Choose exactly which events trigger an email.</p>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition"
                       :class="emailPref === 'none' ? 'border-indigo-400 bg-indigo-50' : ''">
                    <input type="radio" name="email_notifications" value="none"
                           x-model="emailPref"
                           class="mt-0.5 text-indigo-600">
                    <div>
                        <span class="text-sm font-medium text-gray-900">None</span>
                        <p class="text-xs text-gray-500 mt-0.5">Don't send me any email notifications.</p>
                    </div>
                </label>
            </div>

            {{-- Custom checkboxes — role-specific, only visible when Custom selected --}}
            <div x-show="emailPref === 'custom'"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="border-t border-gray-200 pt-4 mt-2 space-y-2">
                <p class="text-xs font-medium text-gray-600 uppercase tracking-wide mb-3">Email me when:</p>

                @php $cb = 'rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-200'; @endphp

                @if(auth()->user()->isFmoUser())
                    @foreach([
                        ['email_on_approved',      'My request is approved by FMO Admin'],
                        ['email_on_rejected',       'My request is rejected'],
                        ['email_on_clarification',  'FMO Admin requests clarification'],
                        ['email_on_assigned',       'My request is assigned to Purchase Office'],
                        ['email_on_in_progress',    'Purchase Office starts working on my request'],
                        ['email_on_completed',      'My request is completed'],
                    ] as [$key, $label])
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="{{ $key }}" value="1" class="{{ $cb }}"
                                   {{ $settings[$key] ?? false ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach

                @elseif(auth()->user()->isFmoAdmin())
                    @foreach([
                        ['email_on_new_request',  'A new request is submitted for review'],
                        ['email_on_resubmitted',  'An FMO user resubmits after clarification'],
                        ['email_on_po_assigned',  'A request is assigned to a PO team member'],
                        ['email_on_completed',    'A request is completed'],
                    ] as [$key, $label])
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="{{ $key }}" value="1" class="{{ $cb }}"
                                   {{ $settings[$key] ?? false ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach

                @elseif(auth()->user()->isPoAdmin())
                    @foreach([
                        ['email_on_ready_to_assign', 'A new approved request is ready to assign'],
                        ['email_on_po_assigned',     'A request is assigned to a PO team member'],
                        ['email_on_in_progress',     'A PO team member starts working on a request'],
                        ['email_on_completed',       'A request is completed'],
                    ] as [$key, $label])
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="{{ $key }}" value="1" class="{{ $cb }}"
                                   {{ $settings[$key] ?? false ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach

                @elseif(auth()->user()->isPoUser())
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="email_on_assigned_to_me" value="1" class="{{ $cb }}"
                               {{ $settings['email_on_assigned_to_me'] ?? false ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700">A request is assigned to me</span>
                    </label>
                @endif
            </div>
        </div><!-- end email card -->

        </div><!-- end right column -->
    </div><!-- end grid -->

    <div class="flex justify-end mt-4">
        <button type="submit"
                class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 transition">
            Save Settings
        </button>
    </div>
</form>

<script>
// Test notification sound
document.getElementById('testSound').addEventListener('click', function() {
    const sound = document.getElementById('notification_sound').value;
    if (sound !== 'none') {
        playNotificationSound(sound);
    }
});

function playNotificationSound(sound) {
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();

    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);

    // Different sounds
    switch(sound) {
        case 'chime':
            oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
            oscillator.frequency.setValueAtTime(1000, audioContext.currentTime + 0.1);
            oscillator.frequency.setValueAtTime(1200, audioContext.currentTime + 0.2);
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialDecayTo && gainNode.gain.exponentialDecayTo(0.01, audioContext.currentTime + 0.5);
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.5);
            break;
        case 'bell':
            oscillator.frequency.setValueAtTime(600, audioContext.currentTime);
            oscillator.type = 'sine';
            gainNode.gain.setValueAtTime(0.4, audioContext.currentTime);
            gainNode.gain.linearRampToValueAtTime(0.01, audioContext.currentTime + 0.8);
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.8);
            break;
        case 'ping':
            oscillator.frequency.setValueAtTime(1400, audioContext.currentTime);
            oscillator.type = 'sine';
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.linearRampToValueAtTime(0.01, audioContext.currentTime + 0.15);
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.15);
            break;
    }
}
</script>
@endsection
