<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'FMO2PO') }} - @yield('title', 'Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
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
                            <a href="{{ route('admin.users.index') }}" class="text-gray-600 hover:text-gray-900">
                                Manage Users
                            </a>
                        @endif
                    @endauth
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        <div class="flex items-center space-x-3">
                            @if(auth()->user()->avatar)
                                <img src="{{ auth()->user()->avatar }}" alt="Avatar" class="w-8 h-8 rounded-full">
                            @endif
                            <span class="text-gray-700">{{ auth()->user()->name }}</span>
                            <span class="px-2 py-1 text-xs rounded-full
                                @if(auth()->user()->role === 'super_admin') bg-red-100 text-red-800
                                @elseif(auth()->user()->role === 'fmo_user') bg-blue-100 text-blue-800
                                @elseif(auth()->user()->role === 'fmo_admin') bg-green-100 text-green-800
                                @elseif(auth()->user()->role === 'po_admin') bg-purple-100 text-purple-800
                                @else bg-orange-100 text-orange-800
                                @endif">
                                {{ str_replace('_', ' ', strtoupper(auth()->user()->role)) }}
                            </span>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-gray-900">
                                Logout
                            </button>
                        </form>
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
</body>
</html>
