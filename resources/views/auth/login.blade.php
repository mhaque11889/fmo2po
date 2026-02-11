<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FMO2PO</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-indigo-600">FMO2PO</h1>
            <p class="text-gray-600 mt-2">Facilities Management Office to Purchase Office</p>
        </div>

        <div class="space-y-4">
            <a href="{{ route('auth.google') }}"
               class="flex items-center justify-center w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm bg-white hover:bg-gray-50 transition">
                <svg class="w-6 h-6 mr-3" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                <span class="text-gray-700 font-medium">Sign in with Google</span>
            </a>
        </div>

        <div class="mt-8 text-center text-sm text-gray-500">
            <p>Sign in with your organization's Google account</p>
        </div>

        @if(app()->environment('local') && isset($testUsers) && $testUsers->count() > 0)
            <div class="mt-8 pt-6 border-t border-gray-200">
                <h3 class="text-sm font-medium text-gray-700 mb-4 text-center">Local Development Login</h3>
                <div class="space-y-2">
                    @foreach($testUsers as $user)
                        <form action="{{ route('auth.fake-login') }}" method="POST">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                            <button type="submit" class="w-full px-4 py-2 text-sm border rounded-lg hover:bg-gray-50 transition flex items-center justify-between
                                @if($user->role === 'fmo_user') border-blue-200 bg-blue-50 hover:bg-blue-100
                                @elseif($user->role === 'fmo_admin') border-green-200 bg-green-50 hover:bg-green-100
                                @elseif($user->role === 'po_admin') border-purple-200 bg-purple-50 hover:bg-purple-100
                                @else border-orange-200 bg-orange-50 hover:bg-orange-100
                                @endif">
                                <span class="text-gray-700">{{ $user->name }}</span>
                                <span class="px-2 py-0.5 text-xs rounded-full
                                    @if($user->role === 'fmo_user') bg-blue-100 text-blue-800
                                    @elseif($user->role === 'fmo_admin') bg-green-100 text-green-800
                                    @elseif($user->role === 'po_admin') bg-purple-100 text-purple-800
                                    @else bg-orange-100 text-orange-800
                                    @endif">
                                    {{ str_replace('_', ' ', strtoupper($user->role)) }}
                                </span>
                            </button>
                        </form>
                    @endforeach
                </div>
                <p class="mt-3 text-xs text-red-500 text-center">* Only visible in local environment</p>
            </div>
        @elseif(app()->environment('local'))
            <div class="mt-8 pt-6 border-t border-gray-200">
                <h3 class="text-sm font-medium text-gray-700 mb-2 text-center">Local Development</h3>
                <p class="text-xs text-gray-500 text-center mb-3">No test users found. Run the seeder to create them:</p>
                <code class="block text-xs bg-gray-100 p-2 rounded text-center">php artisan db:seed --class=UserSeeder</code>
            </div>
        @endif
    </div>
</body>
</html>
