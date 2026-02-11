<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FMO2PO - Facilities Management to Purchase Office</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-indigo-100 via-white to-purple-100 min-h-screen">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="py-6 px-4">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <h1 class="text-2xl font-bold text-indigo-600">FMO2PO</h1>
                @auth
                    <a href="{{ route('dashboard') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                        Sign In
                    </a>
                @endauth
            </div>
        </header>

        <!-- Hero Section -->
        <main class="flex-grow flex items-center justify-center px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-5xl font-bold text-gray-900 mb-6">
                    Facilities Management Office
                    <span class="text-indigo-600">to</span>
                    Purchase Office
                </h2>
                <p class="text-xl text-gray-600 mb-8">
                    Streamline your requirement requests from FMO to Purchase Office.
                    Submit, approve, and track requests all in one place.
                </p>

                @guest
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center px-8 py-4 bg-indigo-600 text-white text-lg font-semibold rounded-lg hover:bg-indigo-700 transition shadow-lg">
                        <svg class="w-6 h-6 mr-3" viewBox="0 0 24 24">
                            <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        Sign in with Google
                    </a>
                @endguest

                <!-- Features -->
                <div class="mt-16 grid md:grid-cols-3 gap-8">
                    <div class="bg-white p-6 rounded-lg shadow-lg">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4 mx-auto">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Submit Requests</h3>
                        <p class="text-gray-600">FMO team can easily submit requirement requests with all necessary details.</p>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-lg">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4 mx-auto">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Quick Approvals</h3>
                        <p class="text-gray-600">FMO Admin reviews and approves requests before sending to Purchase Office.</p>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-lg">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4 mx-auto">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Task Assignment</h3>
                        <p class="text-gray-600">Purchase Office Admin assigns approved requests to team members.</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="py-6 text-center text-gray-500">
            <p>&copy; {{ date('Y') }} FMO2PO. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
