<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — BallSignals</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 antialiased font-[instrument-sans]">

<div class="flex min-h-screen">

    {{-- Mobile overlay --}}
    <div id="sidebar-overlay"
         class="fixed inset-0 bg-black/40 backdrop-blur-sm z-20 lg:hidden hidden"
         onclick="closeSidebar()"></div>

    {{-- ======================== SIDEBAR ======================== --}}
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-30 w-60 bg-gray-900 flex flex-col
                  transform -translate-x-full transition-transform duration-250 ease-in-out
                  lg:static lg:translate-x-0 lg:transform-none">

        {{-- Brand --}}
        <div class="flex items-center justify-between px-5 h-16 border-b border-gray-800 flex-shrink-0">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <circle cx="10" cy="10" r="9" fill="none" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M10 5.5L11.5 8.5H14.5L12 10.5L13 13.5L10 11.5L7 13.5L8 10.5L5.5 8.5H8.5Z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-white text-sm leading-tight">BallSignals</p>
                    <p class="text-[10px] text-gray-500 leading-tight">Admin Panel</p>
                </div>
            </a>
            <button onclick="closeSidebar()" class="lg:hidden p-1.5 rounded-md text-gray-500 hover:text-white hover:bg-gray-800 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 px-3 py-4 overflow-y-auto space-y-0.5">

            <p class="px-3 pt-1 pb-2 text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Main</p>

            <a href="{{ route('admin.dashboard') }}" onclick="closeSidebar()"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('admin.dashboard') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>

            <a href="{{ route('admin.betting-tips.index') }}" onclick="closeSidebar()"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('admin.betting-tips.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Betting Tips
            </a>

            <a href="{{ route('admin.blogs.index') }}" onclick="closeSidebar()"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('admin.blogs.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Blog Posts
            </a>

            @php $unread = \App\Models\ContactMessage::where('is_read', false)->count(); @endphp
            <a href="{{ route('admin.contacts.index') }}" onclick="closeSidebar()"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('admin.contacts.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span class="flex-1">Messages</span>
                @if($unread > 0)
                    <span class="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full leading-none">{{ $unread }}</span>
                @endif
            </a>


            <a href="{{ route('admin.subscribers.index') }}" onclick="closeSidebar()"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('admin.subscribers.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Subscribers
            </a>

            @php $pendingSubs = \App\Models\SubscriptionRequest::where('status', 'pending')->count(); @endphp
            <a href="{{ route('admin.subscription-requests.index') }}" onclick="closeSidebar()"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('admin.subscription-requests.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
                <span class="flex-1">Subscriptions</span>
                @if($pendingSubs > 0)
                    <span class="bg-yellow-400 text-black text-[10px] font-bold px-1.5 py-0.5 rounded-full leading-none">{{ $pendingSubs }}</span>
                @endif
            </a>

            <div class="pt-4 pb-1">
                <p class="px-3 pb-1 text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Account</p>
            </div>

            <a href="{{ route('admin.settings.index') }}" onclick="closeSidebar()"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('admin.settings.*') ? 'bg-green-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Settings
            </a>

            <a href="{{ route('home') }}" target="_blank"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-400 hover:bg-gray-800 hover:text-white transition-all duration-150">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                View Site
            </a>

        </nav>

        {{-- User footer --}}
        <div class="px-3 py-3 border-t border-gray-800 flex-shrink-0">
            <div class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-800 transition-colors">
                <div class="w-8 h-8 rounded-full bg-green-600 flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Logout"
                            class="p-1.5 rounded-md text-gray-500 hover:text-red-400 hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

    </aside>

    {{-- ======================== MAIN ======================== --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Top Bar --}}
        <header class="bg-white border-b border-gray-200 px-4 lg:px-6 h-16 flex items-center justify-between sticky top-0 z-10 flex-shrink-0">

            <div class="flex items-center gap-3">
                {{-- Hamburger --}}
                <button onclick="openSidebar()"
                        class="lg:hidden w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                {{-- Breadcrumb --}}
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-gray-400 hidden sm:inline">Admin</span>
                    <span class="text-gray-300 hidden sm:inline">/</span>
                    <span class="font-semibold text-gray-900">@yield('heading', 'Dashboard')</span>
                </div>
            </div>

            {{-- Right side --}}
            <div class="flex items-center gap-3">
                <span class="hidden sm:block text-xs text-gray-400 bg-gray-100 px-3 py-1.5 rounded-full">
                    {{ now()->format('d M Y') }}
                </span>

                @if($unread > 0)
                <a href="{{ route('admin.contacts.index') }}"
                   class="relative w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                </a>
                @endif

                <div class="w-8 h-8 rounded-full bg-green-600 flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                </div>
            </div>

        </header>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="bg-green-50 border-b border-green-100 px-4 lg:px-6 py-3 text-green-800 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border-b border-red-100 px-4 lg:px-6 py-3 text-red-800 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- Page Content --}}
        <main class="flex-1 p-4 lg:p-6 overflow-auto">
            @yield('content')
        </main>

    </div>
</div>

<script>
    function openSidebar() {
        document.getElementById('sidebar').classList.remove('-translate-x-full');
        document.getElementById('sidebar-overlay').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.add('-translate-x-full');
        document.getElementById('sidebar-overlay').classList.add('hidden');
        document.body.style.overflow = '';
    }
</script>

</body>
</html>
