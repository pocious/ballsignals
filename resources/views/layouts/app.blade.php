<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="scroll-behavior:smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google-site-verification" content="5stoS4gFxhcR0wdEBNJXJM-Qv5DUdYdNyMu5dZVlRj8">

    {{-- SEO: Title --}}
    <title>@yield('title', 'BallSignals — Free Football Betting Tips & Predictions')</title>

    {{-- SEO: Description & robots --}}
    <meta name="description" content="@yield('meta_description', 'BallSignals delivers expert football betting tips and match predictions every day. Free daily tips across Premier League, La Liga, Champions League, Serie A and more.')">
    <meta name="robots" content="@yield('robots', 'index, follow')">

    {{-- SEO: Canonical URL --}}
    <link rel="canonical" href="@yield('canonical', url()->current())">

    {{-- Open Graph --}}
    <meta property="og:site_name" content="BallSignals">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('title', 'BallSignals — Free Football Betting Tips & Predictions')">
    <meta property="og:description" content="@yield('meta_description', 'BallSignals delivers expert football betting tips and match predictions every day. Free daily tips across Premier League, La Liga, Champions League and more.')">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta property="og:image" content="@yield('og_image', asset('images/og-default.jpg'))">
    <meta property="og:locale" content="en_GB">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@ballsignals">
    <meta name="twitter:title" content="@yield('title', 'BallSignals — Free Football Betting Tips & Predictions')">
    <meta name="twitter:description" content="@yield('meta_description', 'BallSignals delivers expert football betting tips and match predictions every day.')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/og-default.jpg'))">

    {{-- JSON-LD: Organization (sitewide) --}}
    @php
    $__orgSchema = json_encode([
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => 'BallSignals',
        'url'      => url('/'),
        'sameAs'   => [
            'https://facebook.com/ballsignals',
            'https://x.com/ballsignals',
            'https://t.me/ballsigtips',
            'https://threads.net/@ballsignals',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    @endphp
    <script type="application/ld+json">{!! $__orgSchema !!}</script>

    {{-- Page-specific structured data --}}
    @stack('schema')

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        #mobile-menu {
            transition: transform 0.2s ease, opacity 0.2s ease;
        }
        #mobile-menu.menu-hidden {
            transform: translateY(-8px);
            opacity: 0;
            pointer-events: none;
        }
        #mobile-menu.menu-visible {
            transform: translateY(0);
            opacity: 1;
            pointer-events: auto;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 antialiased transition-colors duration-200">

    {{-- ── Main Header ── --}}
    <header id="main-header"
            class="sticky top-0 z-50 bg-[#0a0f1a]/95 backdrop-blur-md border-b border-white/5 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-16">

                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 group flex-shrink-0">
                    <div class="w-8 h-8 rounded-lg bg-green-500 flex items-center justify-center
                                shadow-lg shadow-green-500/30 group-hover:shadow-green-500/50
                                group-hover:scale-105 transition-all duration-200">
                        <svg class="w-5 h-5 text-black" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14.93V15h2v1.93c-1.31.09-2 .07-2 0zm4-1.07V14h-2v-2h4v1.86c-.64.37-1.32.69-2 1zm-6 0c-.68-.31-1.36-.63-2-1V12h4v2H9v1.86zM4.07 13H6v-2H4.07c.09-.69.24-1.36.46-2H6V7.14C7.06 6.43 8.24 6 9.5 6c.17 0 .34.01.5.03V9h5V6.03c.16-.02.33-.03.5-.03 1.26 0 2.44.43 3.5 1.14V9h1.47c.22.64.37 1.31.46 2H19v2h1.93c-.09.69-.24 1.36-.46 2H19v1.86c-1.06.71-2.24 1.14-3.5 1.14-.17 0-.34-.01-.5-.03V15H9v1.97c-.16.02-.33.03-.5.03-1.26 0-2.44-.43-3.5-1.14V15H3.53c-.22-.64-.37-1.31-.46-2z"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold tracking-tight text-white">
                        Ball<span class="text-green-400">Signals</span>
                    </span>
                </a>

                {{-- Desktop Nav --}}
                <nav class="hidden md:flex items-center gap-0.5">
                    @php
                        $navLinks = [
                            ['Home',         route('home'),       request()->routeIs('home')],
                            ["Today's Tips", route('home'),       false],
                            ['Blog',         route('blog.index'), request()->routeIs('blog.*')],
                            ['Premium',      route('premium'),    request()->routeIs('premium')],
                            ['Results',      route('results'),    request()->routeIs('results')],
                        ];
                    @endphp
                    @foreach($navLinks as [$label, $href, $active])
                        <a href="{{ $href }}"
                           class="relative px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 group
                                  {{ $active ? 'text-green-400' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                            {{ $label }}
                            <span class="absolute bottom-1.5 left-4 right-4 h-px rounded-full bg-green-400 transition-transform duration-200 origin-left
                                         {{ $active ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' }}"></span>
                        </a>
                    @endforeach
                </nav>

                {{-- Right — auth + dark toggle --}}
                <div class="hidden md:flex items-center gap-2">
                    <a href="https://t.me/ballsigtips" target="_blank" rel="noopener noreferrer"
                       class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#229ED9]/10 border border-[#229ED9]/30 hover:bg-[#229ED9]/20 transition-all duration-200 group">
                        <svg class="w-4 h-4 text-[#229ED9]" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.248l-1.97 9.289c-.145.658-.537.818-1.084.508l-3-2.21-1.447 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.12L7.48 14.056l-2.95-.924c-.64-.203-.653-.64.136-.954l11.57-4.461c.537-.194 1.006.131.326.531z"/>
                        </svg>
                        <span class="text-xs font-semibold text-[#229ED9]">Telegram</span>
                    </a>
                    @guest
                        <a href="{{ route('login') }}"
                           class="px-4 py-2 text-sm font-semibold text-gray-300 border border-white/10
                                  rounded-lg hover:text-white hover:border-white/25 hover:bg-white/5 transition-all duration-200">
                            Login
                        </a>
                    @endguest

                    <button id="dark-toggle" aria-label="Toggle dark mode"
                            class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-500
                                   hover:text-white hover:bg-white/5 transition-colors duration-200 ml-1">
                        <svg data-icon="moon" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                        </svg>
                        <svg data-icon="sun" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>
                        </svg>
                    </button>
                </div>

                {{-- Mobile right: telegram + dark toggle + hamburger --}}
                <div class="md:hidden flex items-center gap-1">
                    <a href="https://t.me/ballsigtips" target="_blank" rel="noopener noreferrer"
                       class="w-9 h-9 flex items-center justify-center rounded-lg bg-[#229ED9]/10 border border-[#229ED9]/30 hover:bg-[#229ED9]/20 transition-all duration-200">
                        <svg class="w-4 h-4 text-[#229ED9]" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.248l-1.97 9.289c-.145.658-.537.818-1.084.508l-3-2.21-1.447 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.12L7.48 14.056l-2.95-.924c-.64-.203-.653-.64.136-.954l11.57-4.461c.537-.194 1.006.131.326.531z"/>
                        </svg>
                    </a>
                    <button id="dark-toggle" aria-label="Toggle dark mode"
                            class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-500
                                   hover:text-white hover:bg-white/5 transition-colors duration-200">
                        <svg data-icon="moon" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                        </svg>
                        <svg data-icon="sun" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>
                        </svg>
                    </button>

                    <button id="mobile-menu-btn" aria-label="Toggle menu"
                            class="w-10 h-10 flex items-center justify-center rounded-lg text-gray-400
                                   hover:text-white hover:bg-white/5 transition-colors duration-200">
                        <svg id="icon-hamburger" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg id="icon-close" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile Dropdown Menu --}}
        <div id="mobile-menu"
             class="menu-hidden md:hidden absolute top-full left-0 right-0 bg-[#0a0f1a] border-b border-white/10 shadow-xl">
            <div class="px-4 py-3 space-y-1">
                <a href="{{ route('home') }}"
                   class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold text-green-400 bg-green-500/10">
                    Home
                </a>
                <a href="{{ route('home') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-colors">Today's Tips</a>
                <a href="{{ route('blog.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-colors">Blog</a>
                <a href="{{ route('tip-of-the-day') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-colors">Tip of the Day</a>
                <a href="{{ route('premium') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-colors">
                    Premium
                    <span class="text-[10px] font-black text-black bg-green-400 px-1.5 py-0.5 rounded uppercase">Pro</span>
                </a>
                <a href="{{ route('results') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-colors">Results</a>
                <a href="https://t.me/ballsigtips" target="_blank" rel="noopener noreferrer"
                   class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium bg-[#229ED9]/10 border border-[#229ED9]/20 text-[#229ED9] hover:bg-[#229ED9]/20 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.248l-1.97 9.289c-.145.658-.537.818-1.084.508l-3-2.21-1.447 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.12L7.48 14.056l-2.95-.924c-.64-.203-.653-.64.136-.954l11.57-4.461c.537-.194 1.006.131.326.531z"/>
                    </svg>
                    Join Telegram
                </a>
            </div>
            @guest
            <div class="px-4 py-3 border-t border-white/10">
                <div class="flex gap-2 pb-1">
                    <a href="{{ route('login') }}"
                       class="flex-1 text-center py-2 text-sm font-semibold text-gray-300 border border-white/10 rounded-xl hover:text-white hover:border-white/25 transition-all">
                        Login
                    </a>
                </div>
            </div>
            @endguest
        </div>
    </header>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="bg-green-50 dark:bg-green-900/30 border-b border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 px-4 py-2.5 text-sm text-center">
            {{ session('success') }}
        </div>
    @endif

    {{-- ── Sticky Nav Bar (CTA + optional page filter) ── --}}
    <div class="sticky top-16 z-40 bg-[#0a0f1a]/98 backdrop-blur-md border-b border-white/10 shadow-lg shadow-black/20">
        {{-- CTA buttons --}}
        <div class="max-w-3xl mx-auto px-4 sm:px-6 pt-2.5 pb-2 flex items-center gap-2 overflow-x-auto no-scrollbar">
            <a href="{{ route('home') }}"
               class="flex-shrink-0 inline-flex items-center gap-1.5 px-4 py-1.5 rounded-xl text-xs font-semibold text-white/70 bg-white/5 border border-white/10 hover:border-white/25 hover:text-white transition-all duration-200 {{ request()->routeIs('home') ? 'border-white/25 text-white' : '' }}">
                Home
            </a>
            <a href="{{ route('subscribe') }}"
               class="flex-shrink-0 inline-flex items-center gap-1.5 px-4 py-1.5 rounded-xl text-xs font-bold text-yellow-400 bg-yellow-500/10 border border-yellow-500/40 hover:bg-yellow-500/20 hover:border-yellow-400 transition-all duration-200 {{ request()->routeIs('subscribe') ? 'bg-yellow-500/20 border-yellow-400' : '' }}">
                🔔 Get Daily Tips
            </a>
            <a href="{{ route('premium') }}"
               class="flex-shrink-0 inline-flex items-center gap-1.5 px-4 py-1.5 rounded-xl text-xs font-black text-black bg-green-400 hover:bg-green-300 shadow-lg shadow-green-500/20 transition-all duration-200">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 3l14 9-14 9V3z"/>
                </svg>
                Go Premium
            </a>
            <a href="{{ route('blog.index') }}"
               class="flex-shrink-0 inline-flex items-center gap-1.5 px-4 py-1.5 rounded-xl text-xs font-semibold text-white/70 bg-white/5 border border-white/10 hover:border-white/25 hover:text-white transition-all duration-200 {{ request()->routeIs('blog.*') ? 'border-white/25 text-white' : '' }}">
                Blog
            </a>
            <a href="{{ route('tip-of-the-day') }}"
               class="flex-shrink-0 inline-flex items-center gap-1.5 px-4 py-1.5 rounded-xl text-xs font-bold text-green-400 bg-green-500/10 border border-green-500/40 hover:bg-green-500/20 hover:border-green-400 transition-all duration-200 {{ request()->routeIs('tip-of-the-day') ? 'bg-green-500/20 border-green-400' : '' }}">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Tip of the Day
            </a>
        </div>
        {{-- Page-specific row (e.g. league filter on home) --}}
        @hasSection('sub-bar')
            <div class="border-t border-white/8"></div>
            @yield('sub-bar')
        @endif
    </div>

    <main>@yield('content')</main>

    {{-- ── Footer ── --}}
    <footer class="bg-[#0a0f1a] border-t border-white/5 mt-12">

        {{-- Top section --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-12 pb-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">

                {{-- Brand col --}}
                <div class="lg:col-span-1">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-green-500 flex items-center justify-center shadow-lg shadow-green-500/30">
                            <svg class="w-5 h-5 text-black" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14.93V15h2v1.93c-1.31.09-2 .07-2 0zm4-1.07V14h-2v-2h4v1.86c-.64.37-1.32.69-2 1zm-6 0c-.68-.31-1.36-.63-2-1V12h4v2H9v1.86zM4.07 13H6v-2H4.07c.09-.69.24-1.36.46-2H6V7.14C7.06 6.43 8.24 6 9.5 6c.17 0 .34.01.5.03V9h5V6.03c.16-.02.33-.03.5-.03 1.26 0 2.44.43 3.5 1.14V9h1.47c.22.64.37 1.31.46 2H19v2h1.93c-.09.69-.24 1.36-.46 2H19v1.86c-1.06.71-2.24 1.14-3.5 1.14-.17 0-.34-.01-.5-.03V15H9v1.97c-.16.02-.33.03-.5.03-1.26 0-2.44-.43-3.5-1.14V15H3.53c-.22-.64-.37-1.31-.46-2z"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-white tracking-tight">Ball<span class="text-green-400">Signals</span></span>
                    </a>
                    <p class="text-sm text-gray-400 leading-relaxed mb-5">
                        Your daily source for expert football betting tips, match predictions, and in-depth odds analysis across all major leagues.
                    </p>
                    {{-- Social handles --}}
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-2">Follow Us</p>
                    <div class="flex items-center gap-2 flex-wrap">

                        {{-- Facebook --}}
                        <a href="https://facebook.com/ballsignals" target="_blank" rel="noopener noreferrer"
                           title="Facebook"
                           class="w-9 h-9 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center hover:bg-[#1877F2]/20 hover:border-[#1877F2]/40 transition-all duration-200 group">
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-[#1877F2]" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M24 12.073C24 5.406 18.627 0 12 0S0 5.406 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.41c0-3.025 1.792-4.697 4.533-4.697 1.312 0 2.686.236 2.686.236v2.97h-1.513c-1.491 0-1.956.93-1.956 1.886v2.268h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/>
                            </svg>
                        </a>

                        {{-- Telegram --}}
                        <a href="https://t.me/ballsigtips" target="_blank" rel="noopener noreferrer"
                           title="Telegram"
                           class="w-9 h-9 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center hover:bg-[#229ED9]/20 hover:border-[#229ED9]/40 transition-all duration-200 group">
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-[#229ED9]" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.248l-1.97 9.289c-.145.658-.537.818-1.084.508l-3-2.21-1.447 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.12L7.48 14.056l-2.95-.924c-.64-.203-.653-.64.136-.954l11.57-4.461c.537-.194 1.006.131.326.531z"/>
                            </svg>
                        </a>

                        {{-- X / Twitter --}}
                        <a href="https://x.com/ballsignals" target="_blank" rel="noopener noreferrer"
                           title="X (Twitter)"
                           class="w-9 h-9 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center hover:bg-white/10 hover:border-white/30 transition-all duration-200 group">
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-white" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                        </a>

                        {{-- Threads --}}
                        <a href="https://threads.net/@ballsignals" target="_blank" rel="noopener noreferrer"
                           title="Threads"
                           class="w-9 h-9 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center hover:bg-white/10 hover:border-white/30 transition-all duration-200 group">
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-white" viewBox="0 0 192 192" fill="currentColor">
                                <path d="M141.537 88.988a66.667 66.667 0 0 0-2.518-1.143c-1.482-27.307-16.403-42.94-41.457-43.1h-.34c-14.986 0-27.449 6.396-35.12 18.036l13.779 9.452c5.73-8.695 14.724-10.548 21.348-10.548h.229c8.249.053 14.474 2.452 18.503 7.129 2.932 3.405 4.893 8.111 5.864 14.05-7.314-1.243-15.224-1.626-23.68-1.14-23.82 1.371-39.134 15.264-38.105 34.568.522 9.792 5.4 18.216 13.735 23.719 7.047 4.652 16.124 6.927 25.557 6.412 12.458-.683 22.231-5.436 29.049-14.127 5.178-6.6 8.453-15.153 9.899-25.93 5.937 3.583 10.337 8.298 12.767 13.966 4.132 9.635 4.373 25.468-8.546 38.376-11.319 11.308-24.925 16.2-45.488 16.351-22.809-.169-40.06-7.484-51.275-21.742C35.236 139.966 29.808 120.682 29.605 96c.203-24.682 5.631-43.966 16.133-57.317C56.954 24.425 74.205 17.11 97.014 16.941c22.975.17 40.526 7.52 52.171 21.847 5.71 6.969 10.035 15.863 12.832 26.44l16.23-4.073c-3.426-12.9-8.853-24.052-16.232-33.178C147.35 9.469 125.33.2 97.07 0h-.113C68.882.2 47.292 9.515 32.788 27.69 19.715 44.138 12.996 67.499 12.75 96v.027c.246 28.502 6.965 51.863 19.989 68.362C47.292 182.484 68.882 191.8 96.957 192h.113c24.96-.173 42.554-6.708 57.048-21.189 18.963-18.945 18.392-42.692 12.142-57.27-4.484-10.454-13.033-18.945-24.723-24.553zM98.44 129.507c-10.44.588-21.286-4.098-21.82-14.135-.397-7.442 5.276-15.746 22.363-16.735 1.958-.113 3.88-.168 5.77-.168 6.014 0 11.638.584 16.716 1.693-1.9 23.698-12.447 28.733-23.03 29.345z"/>
                            </svg>
                        </a>

                    </div>
                </div>

                {{-- Quick links --}}
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-widest text-gray-500 mb-4">Quick Links</h3>
                    <ul class="space-y-2.5">
                        @foreach([
                            ["Today's Tips",   route('home')],
                            ['Blog',           route('blog.index')],
                            ['Premium Picks',  route('premium')],
                            ['Results',        route('results')],
                            ['League Stats',   route('league-stats')],
                            ['Get Daily Tips', route('subscribe')],
                        ] as [$label, $href])
                            <li>
                                <a href="{{ $href }}" class="text-sm text-gray-400 hover:text-green-400 transition-colors duration-150 flex items-center gap-1.5 group">
                                    <span class="w-1 h-1 rounded-full bg-green-500/0 group-hover:bg-green-500 transition-colors"></span>
                                    {{ $label }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- How we analyse --}}
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-widest text-gray-500 mb-4">How We Analyse</h3>
                    <ul class="space-y-3">
                        @foreach([
                            ['📊', 'Statistical Models',    'We process head-to-head records, form tables, and Poisson distribution models to calculate win probabilities.'],
                            ['📈', 'Odds Movement',         'We track live line movements across major bookmakers to detect sharp money and value opportunities.'],
                            ['🧠', 'Expert Review',         'Every tip is reviewed by our analysts before publishing — no automated picks without human validation.'],
                            ['🗂️', 'Data Sources',          'We collect data from official league APIs, betting exchanges, and verified statistical providers.'],
                        ] as [$icon, $title, $desc])
                            <li class="flex items-start gap-2.5">
                                <span class="text-base leading-none mt-0.5">{{ $icon }}</span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-300">{{ $title }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">{{ $desc }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- SEO keywords + disclaimer --}}
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-widest text-gray-500 mb-4">Popular Searches</h3>
                    <div class="flex flex-wrap gap-1.5 mb-6">
                        @foreach([
                            'Football Tips', 'Betting Predictions', 'Free Tips Today',
                            'Premier League Tips', 'Over 2.5 Goals', 'BTTS Tips',
                            'Accumulator Tips', 'Both Teams to Score', 'Match Predictions',
                            'Best Odds', 'La Liga Tips', 'Champions League Tips',
                            'Draw Predictions', 'Home Win Tips', 'Value Bets',
                        ] as $kw)
                            <span class="text-[10px] px-2 py-0.5 rounded-full border border-white/10 text-gray-500 hover:border-green-500/30 hover:text-green-400 transition-colors cursor-default">
                                {{ $kw }}
                            </span>
                        @endforeach
                    </div>
                    <div class="p-3 rounded-xl bg-red-500/5 border border-red-500/10">
                        <p class="text-[10px] text-gray-500 leading-relaxed">
                            <span class="text-red-400 font-bold">18+</span> · Betting tips are for informational purposes only. We do not guarantee winnings. Please gamble responsibly. If you feel you may have a gambling problem, seek help at <span class="text-gray-400">BeGambleAware.org</span>.
                        </p>
                    </div>
                </div>

            </div>
        </div>

        {{-- Bottom bar --}}
        <div class="border-t border-white/5">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 text-center">
                <p class="text-xs text-gray-600">© 2026 BallSignals. All rights reserved.</p>
            </div>
        </div>

    </footer>

@stack('scripts')
</body>
</html>
