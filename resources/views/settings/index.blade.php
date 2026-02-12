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

<div class="max-w-2xl">
    <form action="{{ route('settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Dashboard Refresh Settings -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
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
        <div class="bg-white rounded-lg shadow p-6 mb-6">
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

        <div class="flex justify-end">
            <button type="submit"
                    class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 transition">
                Save Settings
            </button>
        </div>
    </form>
</div>

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
