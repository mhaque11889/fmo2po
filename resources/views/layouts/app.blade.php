<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'FMO2PO') }} - @yield('title', 'Dashboard')</title>
    <link rel="icon" type="image/png" href="/image.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
    @stack('head')
</head>
<body class="bg-gray-300 min-h-screen">
    <nav class="bg-black shadow-lg sticky top-0 z-50" x-data="{ mobileOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo + desktop nav links -->
                <div class="flex items-center space-x-6">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-1">
                        <img src="/aes-logo.png" alt="AES Logo" class="h-10 w-auto">
                        <img src="/image.png" alt="FMO2PO" class="h-10 w-auto">
                    </a>
                    @auth
                        <div class="hidden md:flex items-center space-x-6">
                            <a href="{{ route('dashboard') }}" class="text-gray-300 hover:text-white">
                                Dashboard
                            </a>
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isFmoAdmin() || auth()->user()->isPoAdmin())
                                <a href="{{ route('reports.index') }}" class="text-gray-300 hover:text-white">
                                    Reports
                                </a>
                                <a href="{{ route('admin.users.index') }}" class="text-gray-300 hover:text-white">
                                    Manage Users
                                </a>
                            @endif
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isFmoAdmin())
                                <a href="{{ route('admin.categories.index') }}" class="text-gray-300 hover:text-white">
                                    Categories
                                </a>
                                <a href="{{ route('admin.fmo-groups.index') }}" class="text-gray-300 hover:text-white">
                                    FMO Groups
                                </a>
                            @endif
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isPoAdmin())
                                <a href="{{ route('admin.po-groups.index') }}" class="text-gray-300 hover:text-white">
                                    PO Groups
                                </a>
                            @endif
                        </div>
                    @endauth
                </div>

                <!-- Right side: bell + user + hamburger -->
                <div class="flex items-center space-x-2">
                    @auth
                        <!-- Notification Bell -->
                        @if(auth()->user()->isPoUser() || auth()->user()->isPoAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isFmoUser() || auth()->user()->isFmoAdmin())
                            <div class="relative" x-data="{ bellOpen: false }">
                                <button @click="bellOpen = !bellOpen" @click.away="bellOpen = false"
                                        id="bell-button"
                                        class="relative flex items-center focus:outline-none hover:bg-gray-800 rounded-lg p-2 transition">
                                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    </svg>
                                    <span id="nudge-badge" class="hidden absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 items-center justify-center font-bold">0</span>
                                </button>

                                <!-- Bell Dropdown -->
                                <div x-show="bellOpen"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                                    <div class="px-4 py-2 border-b border-gray-100 flex items-center justify-between">
                                        <p class="text-sm font-semibold text-gray-900" id="nudge-dropdown-title">Notifications</p>
                                        <span id="nudge-dropdown-count" class="text-xs text-gray-500 font-medium"></span>
                                    </div>
                                    <div id="nudge-list" class="max-h-80 overflow-y-auto divide-y divide-gray-50">
                                        <p class="px-4 py-4 text-sm text-gray-400 text-center">No unread update requests</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- User Dropdown (hidden on mobile, shown via hamburger) -->
                        <div class="relative hidden md:block" x-data="{ open: false }">
                            <button @click="open = !open" @click.away="open = false"
                                    class="flex items-center space-x-2 focus:outline-none hover:bg-gray-800 rounded-lg px-3 py-2 transition">
                                @if(auth()->user()->avatar)
                                    <img src="{{ auth()->user()->avatar }}" alt="Avatar" class="w-8 h-8 rounded-full">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <span class="text-indigo-600 font-medium text-sm">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                    </div>
                                @endif
                                <span class="text-white text-sm">{{ auth()->user()->name }}</span>
                                <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                    <p class="font-medium text-gray-900 text-sm truncate">{{ auth()->user()->email }}</p>
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

                        <!-- Hamburger button (mobile only) -->
                        <button @click="mobileOpen = !mobileOpen"
                                class="md:hidden flex items-center focus:outline-none hover:bg-gray-800 rounded-lg p-2 transition">
                            <svg x-show="!mobileOpen" class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                            <svg x-show="mobileOpen" class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    @endauth
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        @auth
        <div x-show="mobileOpen"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden border-t border-gray-700 bg-black pb-3">

            <!-- User info -->
            <div class="px-4 py-3 border-b border-gray-700">
                <div class="flex items-center gap-3">
                    @if(auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar }}" alt="Avatar" class="w-9 h-9 rounded-full">
                    @else
                        <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center">
                            <span class="text-indigo-600 font-medium">{{ substr(auth()->user()->name, 0, 1) }}</span>
                        </div>
                    @endif
                    <div>
                        <p class="text-white font-medium text-sm">{{ auth()->user()->name }}</p>
                        <p class="text-gray-400 text-xs truncate">{{ auth()->user()->email }}</p>
                    </div>
                    <span class="ml-auto inline-block px-2 py-1 text-xs rounded-full
                        @if(auth()->user()->role === 'super_admin') bg-red-100 text-red-800
                        @elseif(auth()->user()->role === 'fmo_user') bg-blue-100 text-blue-800
                        @elseif(auth()->user()->role === 'fmo_admin') bg-green-100 text-green-800
                        @elseif(auth()->user()->role === 'po_admin') bg-purple-100 text-purple-800
                        @else bg-orange-100 text-orange-800
                        @endif">
                        {{ str_replace('_', ' ', ucwords(auth()->user()->role)) }}
                    </span>
                </div>
            </div>

            <!-- Nav links -->
            <div class="px-2 pt-2 space-y-1">
                <a href="{{ route('dashboard') }}" @click="mobileOpen = false"
                   class="block px-3 py-2 rounded-md text-gray-300 hover:text-white hover:bg-gray-800 transition">
                    Dashboard
                </a>
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isFmoAdmin() || auth()->user()->isPoAdmin())
                    <a href="{{ route('reports.index') }}" @click="mobileOpen = false"
                       class="block px-3 py-2 rounded-md text-gray-300 hover:text-white hover:bg-gray-800 transition">
                        Reports
                    </a>
                    <a href="{{ route('admin.users.index') }}" @click="mobileOpen = false"
                       class="block px-3 py-2 rounded-md text-gray-300 hover:text-white hover:bg-gray-800 transition">
                        Manage Users
                    </a>
                @endif
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isFmoAdmin())
                    <a href="{{ route('admin.categories.index') }}" @click="mobileOpen = false"
                       class="block px-3 py-2 rounded-md text-gray-300 hover:text-white hover:bg-gray-800 transition">
                        Categories
                    </a>
                    <a href="{{ route('admin.fmo-groups.index') }}" @click="mobileOpen = false"
                       class="block px-3 py-2 rounded-md text-gray-300 hover:text-white hover:bg-gray-800 transition">
                        FMO Groups
                    </a>
                @endif
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isPoAdmin())
                    <a href="{{ route('admin.po-groups.index') }}" @click="mobileOpen = false"
                       class="block px-3 py-2 rounded-md text-gray-300 hover:text-white hover:bg-gray-800 transition">
                        PO Groups
                    </a>
                @endif
                <a href="{{ route('settings.index') }}" @click="mobileOpen = false"
                   class="block px-3 py-2 rounded-md text-gray-300 hover:text-white hover:bg-gray-800 transition">
                    Settings
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="w-full text-left px-3 py-2 rounded-md text-gray-300 hover:text-white hover:bg-gray-800 transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>
        @endauth
    </nav>

    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <!-- Flash message container for sessionStorage messages -->
        <div id="js-flash-success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded hidden"></div>

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

        // Nudge badge state
        let previousNudgeCount = 0;

        // Fetch nudge counts and update bell badge
        async function checkNudges() {
            const badge = document.getElementById('nudge-badge');
            if (!badge) return; // No bell rendered

            try {
                const response = await fetch('{{ route("api.nudge-counts") }}');
                const data = await response.json();
                const count = data.count || 0;
                const mode = data.mode || 'nudges';

                if (count > 0) {
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.classList.remove('hidden');
                    badge.classList.add('flex');

                    // Play sound if new nudges arrived
                    if (count > previousNudgeCount) {
                        playNotificationSound(userSettings.notification_sound);
                    }
                } else {
                    badge.classList.add('hidden');
                    badge.classList.remove('flex');
                }

                // Update dropdown title
                const titleEl = document.getElementById('nudge-dropdown-title');
                if (titleEl) {
                    titleEl.textContent = mode === 'fmo' ? 'Notifications' : 'Update Requests';
                }

                // Update dropdown count text
                const countEl = document.getElementById('nudge-dropdown-count');
                if (countEl) {
                    countEl.textContent = count > 0 ? `${count} unread` : '';
                }

                // Render nudges in dropdown
                renderNudgeList(data.nudges || [], mode);

                previousNudgeCount = count;
            } catch (error) {
                console.error('Error fetching nudges:', error);
            }
        }

        function renderNudgeList(nudges, mode) {
            const list = document.getElementById('nudge-list');
            if (!list) return;

            const emptyMsg = mode === 'fmo' ? 'No new notifications' : 'No unread update requests';

            if (nudges.length === 0) {
                list.innerHTML = `<p class="px-4 py-4 text-sm text-gray-400 text-center">${emptyMsg}</p>`;
                return;
            }

            list.innerHTML = nudges.map(nudge => {
                const isCompletion = nudge.type === 'completion';
                const accentClass = isCompletion ? 'border-l-2 border-green-400' : '';
                const label = isCompletion
                    ? `<span class="inline-flex items-center gap-1 text-xs font-medium text-green-700 bg-green-50 px-1.5 py-0.5 rounded">&#10003; Completed</span>`
                    : `<span class="text-xs text-gray-500">${mode === 'fmo' ? 'reply from' : 'from'} <span class="font-medium text-gray-700">${nudge.sender_name}</span></span>`;
                const dismissBtn = isCompletion
                    ? `<button onclick="markCompletedSeen(${nudge.id})" class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-full transition">Mark as Seen</button>`
                    : `<button onclick="${mode === 'fmo' ? 'markReplySeen' : 'acknowledgeNudge'}(${nudge.id})" class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-full transition">${mode === 'fmo' ? 'Mark as Seen' : 'Acknowledge'}</button>`;

                return `
                <div class="px-4 py-3 hover:bg-gray-50 ${accentClass}" id="nudge-item-${nudge.type}-${nudge.id}">
                    <div class="flex items-start gap-2">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-0.5">
                                ${label}
                                <span class="text-xs text-gray-400">${nudge.sent_at}</span>
                            </div>
                            <a href="${nudge.request_url}" class="text-sm font-medium text-indigo-600 hover:underline block truncate">Request #${nudge.request_id}: ${nudge.request_item}</a>
                            ${nudge.message ? `<p class="text-sm text-gray-600 mt-1 line-clamp-2">${nudge.message}</p>` : ''}
                        </div>
                    </div>
                    <div class="mt-2 flex gap-2">
                        ${dismissBtn}
                        <a href="${nudge.request_url}" class="text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full transition">View Request</a>
                    </div>
                </div>`;
            }).join('');
        }

        async function acknowledgeNudge(nudgeId) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            try {
                const response = await fetch(`/nudges/${nudgeId}/acknowledge`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (response.ok) {
                    document.getElementById(`nudge-item-nudge-${nudgeId}`)?.remove();
                    await checkNudges();
                }
            } catch (error) { console.error('Error acknowledging nudge:', error); }
        }

        async function markReplySeen(nudgeId) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            try {
                const response = await fetch(`/nudges/${nudgeId}/mark-reply-seen`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (response.ok) {
                    document.getElementById(`nudge-item-reply-${nudgeId}`)?.remove();
                    await checkNudges();
                }
            } catch (error) { console.error('Error marking reply as seen:', error); }
        }

        async function markCompletedSeen(requestId) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            try {
                const response = await fetch(`/requests/${requestId}/mark-completed-seen`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (response.ok) {
                    document.getElementById(`nudge-item-completion-${requestId}`)?.remove();
                    await checkNudges();
                }
            } catch (error) { console.error('Error marking completion as seen:', error); }
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
            // Check for sessionStorage flash messages (from AJAX submissions)
            const flashSuccess = sessionStorage.getItem('flash_success');
            if (flashSuccess) {
                const flashContainer = document.getElementById('js-flash-success');
                if (flashContainer) {
                    flashContainer.textContent = flashSuccess;
                    flashContainer.classList.remove('hidden');
                }
                sessionStorage.removeItem('flash_success');
            }

            // Initial count fetch
            checkForChanges();

            // Initial nudge fetch (PO users only - badge won't render for others)
            checkNudges();

            // Setup auto-refresh
            setupAutoRefresh();

            // Poll for nudges every 30 seconds
            setInterval(checkNudges, 30000);
        });
    </script>
    @endauth
    @stack('scripts')
</body>
</html>
