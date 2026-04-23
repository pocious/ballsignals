<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login — BallSignals</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center p-4">

    <div class="w-full max-w-md">

        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                <span class="text-4xl">⚽</span>
                <span class="text-2xl font-bold text-green-700">BallSignals</span>
            </a>
            <p class="text-gray-500 text-sm mt-2">Admin Portal</p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            <h1 class="text-xl font-bold text-gray-900 mb-6">Sign in to your account</h1>

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Email address
                    </label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        autocomplete="email"
                        required
                        value="{{ old('email') }}"
                        class="w-full px-3 py-2.5 rounded-lg border @error('email') border-red-400 bg-red-50 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                        placeholder="admin@ballsignals.com"
                    >
                    @error('email')
                        <p class="text-red-600 text-xs mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Password
                    </label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                        placeholder="••••••••"
                    >
                    @error('password')
                        <p class="text-red-600 text-xs mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center gap-2">
                    <input id="remember" name="remember" type="checkbox"
                           class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    <label for="remember" class="text-sm text-gray-600">Keep me signed in</label>
                </div>

                <button type="submit"
                        class="w-full py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors text-sm">
                    Sign In
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">
            <a href="{{ route('home') }}" class="hover:text-gray-600">← Back to site</a>
        </p>
    </div>

</body>
</html>
