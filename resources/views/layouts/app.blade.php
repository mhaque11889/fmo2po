<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'FMO2PO') }} - @yield('title', 'Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-300 min-h-screen">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-6">
                    <a href="{{ route('dashboard') }}" class="text-xl font-bold text-indigo-600">
                        FMO2PO
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">
                            Dashboard
                        </a>
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->isFmoAdmin() || auth()->user()->isPoAdmin())
                            <a href="{{ route('reports.index') }}" class="text-gray-600 hover:text-gray-900">
                                Reports
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="text-gray-600 hover:text-gray-900">
                                Manage Users
                            </a>
                        @endif
                    @endauth
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        <!-- User Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" @click.away="open = false"
                                    class="flex items-center space-x-3 focus:outline-none hover:bg-gray-50 rounded-lg px-3 py-2 transition">
                                @if(auth()->user()->avatar)
                                    <img src="{{ auth()->user()->avatar }}" alt="Avatar" class="w-8 h-8 rounded-full">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <span class="text-indigo-600 font-medium text-sm">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                    </div>
                                @endif
                                <span class="text-gray-700">{{ auth()->user()->name }}</span>
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">

                                <!-- Role Badge -->
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <p class="text-xs text-gray-500">Signed in as</p>
                                    <p class="font-medium text-gray-900">{{ auth()->user()->email }}</p>
                                    <span class="mt-1 inline-block px-2 py-1 text-xs rounded-full
                                        @if(auth()->user()->role === 'super_admin') bg-red-100 text-red-800
                                        @elseif(auth()->user()->role === 'fmo_user') bg-blue-100 text-blue-800
                                        @elseif(auth()->user()->role === 'fmo_admin') bg-green-100 text-green-800
                                        @elseif(auth()->user()->role === 'po_admin') bg-purple-100 text-purple-800
                                        @else bg-orange-100 text-orange-800
                                        @endif">
                                        {{ str_replace('_', ' ', ucwords(auth()->user()->role)) }}
                                    </span>
                                </div>

                                <!-- Settings Link -->
                                <a href="{{ route('settings.index') }}"
                                   class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 transition">
                                    <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Settings
                                </a>

                                <!-- Logout -->
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100 transition">
                                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    @auth
    <script>
        // User settings from server
        const userSettings = @json(auth()->user()->getAllSettings());
        let previousCounts = null;
        let refreshTimer = null;

        // Play notification sound
        function playNotificationSound(sound) {
            if (sound === 'none') return;

            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            switch(sound) {
                case 'chime':
                    oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
                    oscillator.frequency.setValueAtTime(1000, audioContext.currentTime + 0.1);
                    oscillator.frequency.setValueAtTime(1200, audioContext.currentTime + 0.2);
                    oscillator.type = 'sine';
                    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                    gainNode.gain.linearRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
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

        // Check for task count changes
        async function checkForChanges() {
            try {
                const response = await fetch('{{ route("api.task-counts") }}');
                const data = await response.json();

                if (previousCounts !== null) {
                    let shouldNotify = false;

                    // Check for new requests
                    if (userSettings.notify_on_new_request && data.counts.pending > (previousCounts.pending || 0)) {
                        shouldNotify = true;
                    }

                    // Check for status changes (total changed)
                    if (userSettings.notify_on_status_change && data.total !== previousCounts.total) {
                        shouldNotify = true;
                    }

                    // Check for new assignments
                    if (userSettings.notify_on_task_assigned && data.counts.assigned > (previousCounts.assigned || 0)) {
                        shouldNotify = true;
                    }

                    if (shouldNotify) {
                        playNotificationSound(userSettings.notification_sound);
                    }
                }

                previousCounts = { ...data.counts, total: data.total };
            } catch (error) {
                console.error('Error checking for changes:', error);
            }
        }

        // Auto-refresh dashboard
        function setupAutoRefresh() {
            if (refreshTimer) {
                clearInterval(refreshTimer);
            }

            const interval = userSettings.refresh_interval * 1000;
            console.log('[FMO2PO] Auto-refresh interval:', userSettings.refresh_interval, 'seconds');

            if (interval > 0) {
                console.log('[FMO2PO] Auto-refresh enabled. Will refresh dashboard every', userSettings.refresh_interval, 'seconds');

                // Check for changes before refresh
                refreshTimer = setInterval(async () => {
                    console.log('[FMO2PO] Checking for changes...');
                    await checkForChanges();

                    // Only refresh if on dashboard page
                    if (window.location.pathname === '/dashboard') {
                        console.log('[FMO2PO] Refreshing dashboard now...');
                        window.location.reload();
                    }
                }, interval);

                // Also check periodically without refresh for notifications
                setInterval(checkForChanges, Math.min(interval, 30000));
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Initial count fetch
            checkForChanges();

            // Setup auto-refresh
            setupAutoRefresh();
        });
    </script>
    @endauth
</body>
</html>
