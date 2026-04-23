<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FMO2PO - Facilities Management to Purchase Office</title>
    <link rel="icon" type="image/png" href="/image.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        .roboto-medium { font-family: 'Roboto', sans-serif; font-weight: 500; }
        .google-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #FFFFFF;
            border: 1px solid #747775;
            border-radius: 4px;
            padding: 12px 24px;
            color: #1F1F1F;
            font-size: 14px;
            font-family: 'Roboto', sans-serif;
            font-weight: 500;
            text-decoration: none;
            transition: background-color 0.2s, border-color 0.2s, box-shadow 0.2s;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            cursor: pointer;
        }
        .google-button:hover {
            background-color: #F8F9FA;
            border-color: #5F6368;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
        }
        .google-button:active {
            background-color: #F1F3F4;
            border-color: #4A5568;
        }
        .google-button svg {
            width: 20px;
            height: 20px;
            margin-right: 12px;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-100 via-white to-purple-100 min-h-screen">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="py-6 px-4">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <img src="/aes-logo.png" alt="FMO2PO" class="h-12 w-auto">
                @auth
                    <a href="{{ route('dashboard') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('auth.google') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                        Sign In
                    </a>
                @endauth
            </div>
        </header>

        <!-- Hero Section -->
        <main class="flex-grow flex items-center justify-center px-4">
            <div class="max-w-4xl mx-auto text-center">
                <img src="/image.png" alt="FMO2PO Logo" class="h-36 w-auto mx-auto mb-6">
                <h2 class="text-5xl font-bold text-gray-900 mb-6">
                    Facilities Management Office
                    <br/><span class="text-indigo-600">to</span><br/>
                    Purchase Office
                </h2>
                <p class="text-xl text-gray-600 mb-8">
                    Streamline your requirement requests from FMO to Purchase Office.
                    Submit, approve, and track requests all in one place.
                </p>

                @guest
                    <div class="space-y-4">
                        <a href="{{ route('auth.google') }}" class="google-button">
                            <!-- Official Google "G" Logo -->
                            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" xmlns:xlink="http://www.w3.org/1999/xlink" style="display: block;">
                                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"></path>
                                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"></path>
                                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"></path>
                                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"></path>
                                <path fill="none" d="M0 0h48v48H0z"></path>
                            </svg>
                            Sign in with AES Google Account
                        </a>
                        @if(app()->environment('local'))
                            <div>
                                {{-- <a href="{{ route('login') }}" class="inline-block text-sm text-indigo-500 hover:text-indigo-700 underline underline-offset-2">
                                    Dev login (local only)
                                </a> --}}
                            </div>
                        @endif
                    </div>
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
