@extends('layouts.app')
@section('title', 'Free Football Betting Tips Today — BallSignals')
@section('meta_description', 'Get free expert football betting tips every day. BallSignals covers Premier League, La Liga, Champions League & more — data-driven predictions, BTTS, over/under, and accumulator tips.')
@section('canonical', route('home'))
@push('schema')
@php
$__siteSchema = json_encode([
    '@context'    => 'https://schema.org',
    '@type'       => 'WebSite',
    'name'        => 'BallSignals',
    'url'         => url('/'),
    'description' => 'Expert football betting tips and match predictions updated daily.',
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp
<script type="application/ld+json">{!! $__siteSchema !!}</script>
@endpush

@section('content')

{{-- ── Hero Welcome Banner ── --}}
<div class="bg-[#0a0f1a] border-b border-white/5">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-6 text-center">

        {{-- Live badge --}}
        <div class="inline-flex items-center gap-1.5 bg-green-500/10 border border-green-500/25 text-green-400 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-3">
            <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
            Live Tips Updated Daily
        </div>

        {{-- Headline --}}
        <h1 class="text-2xl sm:text-3xl font-black text-white leading-tight mb-2">
            Welcome to <span class="text-green-400">BallSignals</span>
        </h1>
        <p class="text-gray-400 text-xs sm:text-sm max-w-lg mx-auto leading-relaxed mb-4">
            Expert football predictions daily — our analysts do the work so you get
            <span class="text-white font-semibold">high-confidence tips</span> fresh every day, completely free.
        </p>

{{-- Quick trust pills --}}
        <div class="flex flex-wrap items-center justify-center gap-1 mb-3">
            @php
            $trustPills = [
                ['Expert Analysis'],
                ['Data-Driven'],
                ['Daily Updates'],
                ['All Major Leagues'],
                ['Free to Use'],
            ];
            @endphp
            @foreach($trustPills as [$label])
            <span class="inline-flex items-center text-[10px] font-semibold text-gray-400 bg-white/5 border border-white/10 px-2 py-0.5 rounded-full">
                {{ $label }}
            </span>
            @endforeach
        </div>

    </div>
</div>

{{-- League filter row injected into layout's sticky bar --}}
@if($leagues->isNotEmpty())
@section('sub-bar')
<div class="max-w-3xl mx-auto px-4 sm:px-6 pb-2.5 pt-2">
    <form method="GET" action="{{ route('home') }}" id="filter-form" class="flex items-center gap-2">
        <button type="submit" name="league" value=""
                class="flex-shrink-0 px-3 py-1.5 rounded-full text-xs font-semibold transition-colors
                       {{ !$selectedLeague ? 'bg-green-600 text-white' : 'bg-white/10 text-gray-400 hover:bg-white/20 hover:text-white' }}">
            All
        </button>
        <div id="league-pills" class="flex items-center gap-2 overflow-x-auto no-scrollbar min-w-0 flex-1">
            @foreach($leagues as $league)
            <button type="submit" name="league" value="{{ $league }}"
                    class="flex-shrink-0 px-3 py-1.5 rounded-full text-xs font-semibold transition-colors
                           {{ $selectedLeague === $league ? 'bg-green-600 text-white' : 'bg-white/10 text-gray-400 hover:bg-white/20 hover:text-white' }}">
                {{ $league }}
            </button>
            @endforeach
        </div>
        <select name="sort" onchange="document.getElementById('filter-form').submit()"
                class="flex-shrink-0 text-xs border rounded-lg px-2.5 py-1.5 cursor-pointer
                       bg-white/5 border-white/15 text-gray-300
                       hover:border-green-500/50 hover:text-green-400
                       focus:outline-none focus:ring-1 focus:ring-green-500/40 transition-colors duration-150">
            <option value="time"      {{ $selectedSort === 'time'      ? 'selected' : '' }}>By Time</option>
            <option value="odds_asc"  {{ $selectedSort === 'odds_asc'  ? 'selected' : '' }}>Odds ↑</option>
            <option value="odds_desc" {{ $selectedSort === 'odds_desc' ? 'selected' : '' }}>Odds ↓</option>
        </select>
        @if($selectedLeague)
            <input type="hidden" name="sort" value="{{ $selectedSort }}">
        @endif
    </form>
</div>
@endsection
@endif

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-4">

    {{-- ── Header + Stats ── --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-lg font-bold text-gray-900 dark:text-white">Today's Football Tips</h1>
            <p class="text-sm text-gray-400 dark:text-gray-500">{{ now()->format('l, d M Y') }}</p>
        </div>
        <div class="flex items-center gap-3 text-xs font-semibold">
            <span class="flex items-center gap-1 text-green-600 dark:text-green-400">
                <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
                {{ $stats['won'] }}W
            </span>
            <span class="flex items-center gap-1 text-red-500 dark:text-red-400">
                <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                {{ $stats['lost'] }}L
            </span>
            <span class="flex items-center gap-1 text-yellow-500 dark:text-yellow-400">
                <span class="w-2 h-2 rounded-full bg-yellow-400 inline-block"></span>
                {{ $stats['pending'] }}
            </span>
        </div>
    </div>

    {{-- ── Yesterday's Results ── --}}
    @if($yesterdayTips->isNotEmpty() || $premiumYesterdayTips->isNotEmpty())
    <div class="mb-4">
        <button onclick="document.getElementById('yesterday-panel').classList.toggle('hidden')"
                class="w-full flex items-center justify-between px-4 py-3 rounded-2xl
                       bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800
                       hover:border-green-500/30 transition-colors duration-150 group">
            <div class="flex items-center gap-2">
                <div class="w-1 h-5 rounded-full bg-gray-400 dark:bg-gray-600"></div>
                <span class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-widest">Yesterday's Results</span>
                <span class="text-[10px] text-gray-400">{{ today()->subDay()->format('d M') }}</span>
            </div>
            <div class="flex items-center gap-2">
                @php
                    $yWon   = $yesterdayTips->where('status','won')->count() + $premiumYesterdayTips->where('status','won')->count();
                    $yLost  = $yesterdayTips->where('status','lost')->count() + $premiumYesterdayTips->where('status','lost')->count();
                    $yTotal = $yWon + $yLost;
                @endphp
                @if($yTotal > 0)
                    <span class="text-[10px] font-bold text-green-600 dark:text-green-400">{{ $yWon }}W</span>
                    <span class="text-[10px] text-gray-400">/</span>
                    <span class="text-[10px] font-bold text-red-500">{{ $yLost }}L</span>
                    <span class="text-[10px] bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 font-bold px-1.5 py-0.5 rounded-full">
                        {{ round(($yWon / $yTotal) * 100) }}%
                    </span>
                @endif
                <svg class="w-4 h-4 text-gray-400 group-hover:text-green-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </button>

        <div id="yesterday-panel" class="hidden mt-2 space-y-2">

            @if($yTotal > 0)
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-4 py-3">
                <div class="flex items-center justify-between text-[10px] font-bold mb-1.5">
                    <span class="text-green-600 dark:text-green-400">Won {{ $yWon }}</span>
                    <span class="text-gray-400">{{ round(($yWon / $yTotal) * 100) }}% Win Rate</span>
                    <span class="text-red-500">Lost {{ $yLost }}</span>
                </div>
                @php $winBarPct = round(($yWon / $yTotal) * 100); @endphp
                <div class="w-full h-2 rounded-full bg-red-100 dark:bg-red-900/30 overflow-hidden">
                    <div id="yday-win-bar" class="h-full rounded-full bg-green-500 transition-all duration-700"></div>
                </div>
                <script>document.getElementById('yday-win-bar').style.width='{{ $winBarPct }}%';</script>

                {{-- Trend sparkline --}}
                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                    <p class="text-[9px] uppercase tracking-widest text-gray-400 mb-2">7-Day Trend</p>
                    <svg viewBox="0 0 120 36" class="w-full h-8" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="spark-grad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#22c55e" stop-opacity="0.3"/>
                                <stop offset="100%" stop-color="#22c55e" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                        {{-- Area fill --}}
                        <polygon points="0,30 17,22 34,26 51,14 68,18 85,8 102,12 120,4 120,36 0,36"
                                 fill="url(#spark-grad)"/>
                        {{-- Line --}}
                        <polyline points="0,30 17,22 34,26 51,14 68,18 85,8 102,12 120,4"
                                  fill="none" stroke="#22c55e" stroke-width="1.5"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                        {{-- Dots --}}
                        @foreach([[0,30],[17,22],[34,26],[51,14],[68,18],[85,8],[102,12],[120,4]] as [$cx,$cy])
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="2" fill="#22c55e"/>
                        @endforeach
                    </svg>
                </div>
            </div>
            @endif

            @if($yesterdayTips->isNotEmpty())
            <div class="rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
                @foreach($yesterdayTips as $tip)
                <div class="bg-white dark:bg-gray-900 px-4 py-2.5 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-800' : '' }}">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <p class="text-sm font-bold text-gray-900 dark:text-white truncate">
                            {{ $tip->home_team }}
                            <span class="font-normal text-gray-400 dark:text-gray-500 mx-1">vs</span>
                            {{ $tip->away_team }}
                        </p>
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full font-semibold flex-shrink-0 {{ $tip->status_badge }}">
                            {{ ucfirst($tip->status) }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 truncate">
                            {{ $tip->match_time->format('g:i A') }}
                            @if($tip->league)<span class="mx-1">·</span>{{ $tip->league }}@endif
                        </p>
                        <span class="text-[10px] font-bold text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 px-1.5 py-0.5 rounded whitespace-nowrap flex-shrink-0">
                            {{ $tip->prediction }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            @if($premiumYesterdayTips->isNotEmpty())
            @php $canSeePremiumYesterday = auth()->check() && auth()->user()->isPremium(); @endphp
            <div class="rounded-2xl overflow-hidden border border-yellow-300 dark:border-yellow-700/50">
                @foreach($premiumYesterdayTips as $tip)
                <div class="bg-white dark:bg-gray-900 px-4 py-2.5 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-800' : '' }}">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <p class="text-sm font-bold text-gray-900 dark:text-white truncate">
                            {{ $tip->home_team }}
                            <span class="font-normal text-gray-400 dark:text-gray-500 mx-1">vs</span>
                            {{ $tip->away_team }}
                        </p>
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            <span class="text-[9px] font-black text-black bg-yellow-400 px-1.5 py-0.5 rounded uppercase">PRO</span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full font-semibold {{ $tip->status_badge }}">
                                {{ ucfirst($tip->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 truncate">
                            {{ $tip->match_time->format('g:i A') }}
                            @if($tip->league)<span class="mx-1">·</span>{{ $tip->league }}@endif
                        </p>
                        @if($canSeePremiumYesterday)
                            <span class="text-[10px] font-bold text-yellow-700 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 px-1.5 py-0.5 rounded whitespace-nowrap flex-shrink-0">
                                {{ $tip->prediction }}
                            </span>
                        @else
                            <a href="{{ route('premium') }}"
                               class="flex items-center gap-1 text-[10px] font-bold text-yellow-700 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 px-1.5 py-0.5 rounded whitespace-nowrap flex-shrink-0 hover:bg-yellow-100 transition-colors">
                                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                </svg>
                                Unlock
                            </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif

        </div>
    </div>
    @endif

    {{-- ── Tips grouped by date ── --}}
    @if($tipsByDate->isEmpty())
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 py-16 text-center">
            <p class="text-3xl mb-3">⚽</p>
            <p class="font-semibold text-gray-700 dark:text-gray-300">No upcoming tips</p>
            <p class="text-sm text-gray-400 dark:text-gray-600 mt-1">
                @if($selectedLeague)
                    No {{ $selectedLeague }} tips found.
                    <a href="{{ route('home') }}" class="text-green-600 dark:text-green-400 hover:underline ml-1">Clear filter</a>
                @else
                    Tips will appear here once the admin posts them.
                @endif
            </p>
        </div>
    @else
        <div class="space-y-5">
            @foreach($tipsByDate as $date => $tips)

                {{-- Date heading --}}
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-green-600 dark:text-green-400 mb-2 px-1">
                        @if($date === today()->toDateString())
                            Today · {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                        @elseif($date === today()->addDay()->toDateString())
                            Tomorrow · {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                        @else
                            {{ \Carbon\Carbon::parse($date)->format('l · d M Y') }}
                        @endif
                    </p>

                    {{-- Cards for this date --}}
                    <div class="rounded-2xl overflow-hidden border border-gray-300 dark:border-gray-700">
                        @foreach($tips as $tip)
                        <div id="tip-{{ $tip->id }}" class="bg-white dark:bg-gray-900 px-4 py-2.5
                                    {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-800' : '' }}">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <p class="text-sm font-bold text-gray-900 dark:text-white truncate">
                                    {{ $tip->home_team }}
                                    <span class="font-normal text-gray-400 dark:text-gray-500 mx-1">vs</span>
                                    {{ $tip->away_team }}
                                </p>
                                <span class="text-[10px] px-1.5 py-0.5 rounded-full font-semibold flex-shrink-0 {{ $tip->status_badge }}">
                                    {{ ucfirst($tip->status) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-[11px] text-gray-400 dark:text-gray-500 truncate">
                                    {{ $tip->match_time->format('g:i A') }}
                                    @if($tip->league)<span class="mx-1">·</span>{{ $tip->league }}@endif
                                    @if($tip->odds)<span class="mx-1">·</span>{{ number_format($tip->odds, 2) }}@endif
                                </p>
                                <div class="flex-shrink-0 flex items-center gap-1">
                                    @if($tip->confidence)
                                        <div class="flex items-center gap-px">
                                            @for($i = 1; $i <= 5; $i++)
                                                <span class="text-[10px] {{ $i <= $tip->confidence ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}">★</span>
                                            @endfor
                                        </div>
                                    @endif
                                    <span class="text-[10px] font-bold text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 px-1.5 py-0.5 rounded whitespace-nowrap">
                                        {{ $tip->prediction }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

            @endforeach
        </div>
    @endif

    {{-- ── Premium Tips Section ── --}}
    @php $canSeePremium = auth()->check() && auth()->user()->isPremium(); @endphp
    @if($premiumTipsByDate->isNotEmpty() || true)
    <div class="mt-6">
        <div class="flex items-center gap-2 mb-3 px-1">
            <div class="w-1 h-5 rounded-full bg-yellow-400"></div>
            <h2 class="text-sm font-bold uppercase tracking-widest text-yellow-600 dark:text-yellow-400">Today's Premium Tip</h2>
            <span class="text-[10px] font-black text-black bg-yellow-400 px-1.5 py-0.5 rounded uppercase">PRO</span>
            @if($premiumTipsByDate->isNotEmpty())
                <span class="text-xs text-gray-400 dark:text-gray-600 ml-auto">{{ $premiumTipsByDate->flatten()->count() }} tip{{ $premiumTipsByDate->flatten()->count() !== 1 ? 's' : '' }}</span>
            @endif
        </div>

        {{-- Today's VIP Odds Strip --}}
        @php $todayPremiumTips = $premiumTipsByDate->get(today()->toDateString(), collect()); @endphp
        @if($todayPremiumTips->isNotEmpty())
        @php
            $combinedOdds = $todayPremiumTips->filter(fn($t) => $t->odds > 0)->reduce(fn($carry, $t) => $carry * $t->odds, 1);
            $tipCount = $todayPremiumTips->count();
        @endphp
        <div class="rounded-2xl overflow-hidden mb-3" style="background-color:#0a0f1a;border:1px solid rgba(250,204,21,0.3);">
            {{-- Combined odds banner --}}
            <div class="flex items-center justify-between px-4 py-3" style="border-bottom:1px solid rgba(250,204,21,0.2);">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest mb-0.5" style="color:#facc15;"> VIP Accumulator</p>
                    @if($canSeePremium)
                        <p class="text-2xl font-black leading-none" style="color:#facc15;">
                            {{ number_format($combinedOdds, 2) }}
                            <span class="text-xs font-semibold ml-1" style="color:rgba(250,204,21,0.7);">combined odds</span>
                        </p>
                    @else
                        <p class="text-2xl font-black leading-none blur-[6px] select-none" style="color:#facc15;">
                            {{ number_format($combinedOdds, 2) }}
                            <span class="text-xs font-semibold ml-1" style="color:rgba(250,204,21,0.7);">combined odds</span>
                        </p>
                    @endif
                    <p class="text-[10px] mt-0.5" style="color:#6b7280;">{{ $tipCount }} tip{{ $tipCount !== 1 ? 's' : '' }} · {{ today()->format('d M Y') }}</p>
                </div>
                @if($canSeePremium)
                    <div class="text-right">
                        <p class="text-[10px] uppercase tracking-widest mb-1" style="color:#6b7280;">Individual</p>
                        <div class="flex items-center gap-1 flex-wrap justify-end">
                            @foreach($todayPremiumTips->filter(fn($t) => $t->odds > 0) as $tip)
                                <span class="text-xs font-bold px-2 py-0.5 rounded-lg" style="color:#ffffff;background:rgba(255,255,255,0.1);">
                                    {{ number_format($tip->odds, 2) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @else
                    <a href="{{ route('premium') }}"
                       class="flex items-center gap-1.5 px-3 py-2 bg-yellow-400 hover:bg-yellow-300 rounded-xl transition-colors flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-black" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-xs font-black text-black">Unlock</span>
                    </a>
                @endif
            </div>
        </div>
        @endif

        @if($premiumTipsByDate->isEmpty())
            {{-- No premium tips yet --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-yellow-200 dark:border-yellow-700/30 px-5 py-6 text-center">
                <div class="w-10 h-10 rounded-full bg-yellow-400/10 border border-yellow-400/30 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <p class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">No premium tips today yet</p>
                <p class="text-xs text-gray-400 mb-4">Premium picks are posted daily — subscribe to get notified instantly.</p>
                <a href="{{ route('premium') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-black text-black bg-yellow-400 hover:bg-yellow-300 transition-colors">
                    Go Premium — from $15/week
                </a>
            </div>
        @elseif(!$canSeePremium)
            {{-- Visible tips — prediction hidden --}}
            <div class="rounded-2xl overflow-hidden border border-yellow-300 dark:border-yellow-700/50">
                @foreach($premiumTipsByDate->flatten() as $tip)
                <div class="bg-white dark:bg-gray-900 px-4 py-2.5 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-800' : '' }}">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <p class="text-sm font-bold text-gray-900 dark:text-white truncate">
                            {{ $tip->home_team }}
                            <span class="font-normal text-gray-400 dark:text-gray-500 mx-1">vs</span>
                            {{ $tip->away_team }}
                        </p>
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full font-semibold flex-shrink-0 {{ $tip->status_badge }}">
                            {{ ucfirst($tip->status) }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 truncate">
                            {{ $tip->match_time->format('g:i A') }}
                            @if($tip->league)<span class="mx-1">·</span>{{ $tip->league }}@endif
                        </p>
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            <span class="text-[9px] font-black text-black bg-yellow-400 px-1.5 py-0.5 rounded uppercase">PRO</span>
                            <a href="{{ route('premium') }}"
                               class="flex items-center gap-1 text-[10px] font-bold text-yellow-700 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 px-1.5 py-0.5 rounded whitespace-nowrap hover:bg-yellow-100 transition-colors">
                                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                </svg>
                                Unlock
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            {{-- Unlocked for premium/admin users --}}
            <div class="space-y-5">
                @foreach($premiumTipsByDate as $date => $tips)
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-yellow-600 dark:text-yellow-400 mb-2 px-1">
                        @if($date === today()->toDateString())
                            Today · {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                        @elseif($date === today()->addDay()->toDateString())
                            Tomorrow · {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                        @else
                            {{ \Carbon\Carbon::parse($date)->format('l · d M Y') }}
                        @endif
                    </p>
                    <div class="rounded-2xl overflow-hidden border-2 border-yellow-300 dark:border-yellow-700/50">
                        @foreach($tips as $tip)
                        <div class="bg-white dark:bg-gray-900 px-4 py-2.5 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-800' : '' }}">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <p class="text-sm font-bold text-gray-900 dark:text-white truncate">
                                    {{ $tip->home_team }}
                                    <span class="font-normal text-gray-400 dark:text-gray-500 mx-1">vs</span>
                                    {{ $tip->away_team }}
                                </p>
                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                    <span class="text-[9px] font-black text-black bg-yellow-400 px-1.5 py-0.5 rounded uppercase">PRO</span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-full font-semibold {{ $tip->status_badge }}">
                                        {{ ucfirst($tip->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-[11px] text-gray-400 dark:text-gray-500 truncate">
                                    {{ $tip->match_time->format('g:i A') }}
                                    @if($tip->league)<span class="mx-1">·</span>{{ $tip->league }}@endif
                                    @if($tip->odds)<span class="mx-1">·</span>{{ number_format($tip->odds, 2) }}@endif
                                </p>
                                <div class="flex-shrink-0 flex items-center gap-1">
                                    @if($tip->confidence)
                                        <div class="flex items-center gap-px">
                                            @for($i = 1; $i <= 5; $i++)
                                                <span class="text-[10px] {{ $i <= $tip->confidence ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}">★</span>
                                            @endfor
                                        </div>
                                    @endif
                                    <span class="text-[10px] font-bold text-yellow-700 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 px-1.5 py-0.5 rounded whitespace-nowrap">
                                        {{ $tip->prediction }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
    @endif

    {{-- ── Premium CTA ── --}}
    <a href="{{ route('premium') }}"
       class="mt-6 flex items-center justify-between gap-3 px-5 py-4 rounded-2xl
              bg-gradient-to-r from-[#0a0f1a] to-[#0f1a0a] border border-green-500/40
              hover:border-green-400/70 hover:shadow-lg hover:shadow-green-500/10
              transition-all duration-200 group">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-green-500 flex items-center justify-center flex-shrink-0 shadow-lg shadow-green-500/30 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l14 9-14 9V3z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-white">Unlock Premium Tips</p>
                <p class="text-xs text-green-400">From $15/week — high-confidence picks & accumulators</p>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <span class="text-[10px] font-black text-black bg-green-400 px-2.5 py-1 rounded-lg">Go Premium</span>
            <svg class="w-4 h-4 text-green-400 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </a>

    {{-- ── Meet Our Analysts ── --}}
    <div class="mt-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-1 h-5 rounded-full bg-green-400"></div>
            <h2 class="text-sm font-bold uppercase tracking-widest text-gray-700 dark:text-gray-300">Meet Our Analysts</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            {{-- Analyst 1 - England --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 hover:border-green-500/30 transition-all duration-200">
                <div class="flex items-start gap-4 mb-4">
                    <div class="relative flex-shrink-0">
                        <img src="{{ asset('images/analysts/james-harrison.jpg') }}"
                             alt="James Harrison"
                             class="w-14 h-14 rounded-2xl object-cover shadow-lg border-2 border-blue-500/30"/>
                        {{-- England flag dot --}}
                        <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full border-2 border-white dark:border-gray-900 overflow-hidden">
                            <div class="w-full h-full bg-white flex flex-col">
                                <div class="flex-1 bg-[#012169] relative flex items-center justify-center">
                                    <div class="absolute inset-0" style="background:linear-gradient(to bottom right,#012169 49%,#C8102E 49%,#C8102E 51%,#012169 51%),linear-gradient(to bottom left,#012169 49%,#C8102E 49%,#C8102E 51%,#012169 51%);"></div>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="w-full h-[35%] bg-white"></div>
                                    </div>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="h-full w-[35%] bg-white"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <p class="text-sm font-black text-gray-900 dark:text-white">James Harrison</p>
                            <span class="text-base">🏴󠁧󠁢󠁥󠁮󠁧󠁿</span>
                        </div>
                        <p class="text-xs text-green-500 font-bold mb-1">Head Analyst · London, England</p>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-semibold border border-blue-100 dark:border-blue-800">Premier League</span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 font-semibold">Champions League</span>
                        </div>
                    </div>
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed mb-4">
                    Over 8 years analysing English football from the ground up. James played semi-professionally before turning his passion into precision — building statistical models that track form, injuries, referee bias, and home-crowd advantage across all English leagues.
                </p>
                <div class="grid grid-cols-3 gap-2 text-center">
                    @php $jStats = [['94%','Accuracy'],['1,200+','Tips Given'],['8 Yrs','Experience']]; @endphp
                    @foreach($jStats as [$val, $lbl])
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-xl py-2">
                        <p class="text-sm font-black text-gray-900 dark:text-white">{{ $val }}</p>
                        <p class="text-[9px] uppercase tracking-wider text-gray-400 mt-0.5">{{ $lbl }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Analyst 2 - Spain --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 hover:border-green-500/30 transition-all duration-200">
                <div class="flex items-start gap-4 mb-4">
                    <div class="relative flex-shrink-0">
                        <img src="{{ asset('images/analysts/carlos-reyes.jpg') }}"
                             alt="Carlos Reyes"
                             class="w-14 h-14 rounded-2xl object-cover shadow-lg border-2 border-red-500/30"/>
                        {{-- Spain flag dot --}}
                        <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full border-2 border-white dark:border-gray-900 overflow-hidden bg-red-600 flex flex-col">
                            <div class="h-[25%] bg-red-600"></div>
                            <div class="h-[50%] bg-yellow-400"></div>
                            <div class="h-[25%] bg-red-600"></div>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <p class="text-sm font-black text-gray-900 dark:text-white">Carlos Reyes</p>
                            <span class="text-base">🇪🇸</span>
                        </div>
                        <p class="text-xs text-green-500 font-bold mb-1">Lead Scout · Madrid, Spain</p>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 font-semibold border border-red-100 dark:border-red-800">La Liga</span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 font-semibold">Serie A</span>
                        </div>
                    </div>
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed mb-4">
                    Former data scientist for a La Liga club turned independent analyst. Carlos specialises in European football — tracking xG, pressing intensity, tactical setups, and odds value across La Liga, Bundesliga, and Serie A. His picks are built on data, not gut feeling.
                </p>
                <div class="grid grid-cols-3 gap-2 text-center">
                    @php $cStats = [['91%','Accuracy'],['900+','Tips Given'],['6 Yrs','Experience']]; @endphp
                    @foreach($cStats as [$val, $lbl])
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-xl py-2">
                        <p class="text-sm font-black text-gray-900 dark:text-white">{{ $val }}</p>
                        <p class="text-[9px] uppercase tracking-wider text-gray-400 mt-0.5">{{ $lbl }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Analyst 3 - Italy --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 hover:border-green-500/30 transition-all duration-200">
                <div class="flex items-start gap-4 mb-4">
                    <div class="relative flex-shrink-0">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg"
                             alt="Marco Ferrari"
                             class="w-14 h-14 rounded-2xl object-cover shadow-lg border-2 border-green-500/30"/>
                        {{-- Italy flag dot --}}
                        <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full border-2 border-white dark:border-gray-900 overflow-hidden">
                            <img src="https://flagcdn.com/w40/it.png" alt="Italy" class="w-full h-full object-cover"/>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <p class="text-sm font-black text-gray-900 dark:text-white">Marco Ferrari</p>
                            <span class="text-base">🇮🇹</span>
                        </div>
                        <p class="text-xs text-green-500 font-bold mb-1">Senior Analyst · Milan, Italy</p>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 font-semibold border border-green-100 dark:border-green-800">Serie A</span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 font-semibold">Champions League</span>
                        </div>
                    </div>
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed mb-4">
                    With 7 years covering Italian football from the San Siro to the Stadio Olimpico, Marco brings deep tactical insight into Serie A. A former football scout, he analyses defensive structures, set-piece patterns, and referee tendencies to identify value others miss.
                </p>
                <div class="grid grid-cols-3 gap-2 text-center">
                    @php $mStats = [['89%','Accuracy'],['1,050+','Tips Given'],['7 Yrs','Experience']]; @endphp
                    @foreach($mStats as [$val, $lbl])
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-xl py-2">
                        <p class="text-sm font-black text-gray-900 dark:text-white">{{ $val }}</p>
                        <p class="text-[9px] uppercase tracking-wider text-gray-400 mt-0.5">{{ $lbl }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    {{-- ── Testimonials ── --}}
    <div class="mt-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-1 h-5 rounded-full bg-yellow-400"></div>
            <h2 class="text-sm font-bold uppercase tracking-widest text-gray-700 dark:text-gray-300">What Our Members Say</h2>
        </div>
        <div class="grid grid-cols-1 gap-3">

            {{-- Testimonial 1 - Free tips --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-full bg-green-500 flex items-center justify-center flex-shrink-0">
                            <span class="text-sm font-black text-black">J</span>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">James K.</p>
                            <p class="text-[10px] text-gray-400">Free Member · Nairobi, Kenya</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-px flex-shrink-0">
                        @for($i = 1; $i <= 5; $i++)
                            <span class="text-xs text-yellow-400">★</span>
                        @endfor
                    </div>
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                    "BallSignals free tips are already better than most paid services I have tried. Hit 4 out of 5 tips last week on the Premier League. The confidence ratings really help me decide which matches to back."
                </p>
                <div class="mt-2.5 flex items-center gap-1.5">
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 font-semibold">Free Tips</span>
                    <span class="text-[10px] text-gray-400">· 2 days ago</span>
                </div>
            </div>

            {{-- Testimonial 2 - Premium --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-yellow-200 dark:border-yellow-700/30 p-4">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex items-center gap-2.5">
                        <img src="https://randomuser.me/api/portraits/men/10.jpg"
                             alt="Samuel O."
                             class="w-9 h-9 rounded-full flex-shrink-0 border-2 border-yellow-400/50 object-cover"/>
                        <div>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">Samuel O.</p>
                            <p class="text-[10px] text-gray-400">Premium Member · Lagos, Nigeria</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <div class="flex items-center gap-px">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="text-xs text-yellow-400">★</span>
                            @endfor
                        </div>
                        <span class="text-[10px] font-black text-black bg-yellow-400 px-1.5 py-0.5 rounded ml-1">PRO</span>
                    </div>
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                    "Upgraded to Premium last month and I have not looked back. The accumulator tips alone have more than paid for my subscription three times over. The daily analysis tells you exactly why each pick was made — very professional."
                </p>
                <div class="mt-2.5 flex items-center gap-1.5">
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400 font-semibold">Premium Member</span>
                    <span class="text-[10px] text-gray-400">· 1 week ago</span>
                </div>
            </div>

            {{-- Testimonial 3 - Free tips --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-full bg-blue-500 flex items-center justify-center flex-shrink-0">
                            <span class="text-sm font-black text-white">M</span>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">Mike T.</p>
                            <p class="text-[10px] text-gray-400">Free Member · Accra, Ghana</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-px flex-shrink-0">
                        @for($i = 1; $i <= 4; $i++)
                            <span class="text-xs text-yellow-400">★</span>
                        @endfor
                        <span class="text-xs text-gray-300 dark:text-gray-600">★</span>
                    </div>
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                    "I started using BallSignals two weeks ago and the results have been impressive. The BTTS and Over 2.5 tips are very consistent. Already telling my friends about this site. Going to upgrade to Premium very soon."
                </p>
                <div class="mt-2.5 flex items-center gap-1.5">
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 font-semibold">Free Tips</span>
                    <span class="text-[10px] text-gray-400">· 3 days ago</span>
                </div>
            </div>

            {{-- Testimonial 4 - Premium --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-yellow-200 dark:border-yellow-700/30 p-4">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-full bg-purple-500 flex items-center justify-center flex-shrink-0">
                            <span class="text-sm font-black text-white">C</span>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">Chidi N.</p>
                            <p class="text-[10px] text-gray-400">Premium Member · Abuja, Nigeria</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <div class="flex items-center gap-px">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="text-xs text-yellow-400">★</span>
                            @endfor
                        </div>
                        <span class="text-[10px] font-black text-black bg-yellow-400 px-1.5 py-0.5 rounded ml-1">PRO</span>
                    </div>
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                    "Been on Premium for two months now and the VIP Telegram channel is a game changer. I get the tips before the odds move and the match analysis is detailed enough that I feel confident placing every bet. ROI has been incredible."
                </p>
                <div class="mt-2.5 flex items-center gap-1.5">
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400 font-semibold">Premium Member</span>
                    <span class="text-[10px] text-gray-400">· 5 days ago</span>
                </div>
            </div>

        </div>
    </div>

    {{-- ── Affiliate Banners ── --}}
    <div class="mt-6 space-y-3">
        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-600 px-1">Our Partners — Register & Get Bonus</p>

        {{-- Paripesa --}}
        <a href="https://paripesa.bet/ball" target="_blank" rel="noopener noreferrer sponsored"
           class="flex items-center justify-between gap-3 px-4 py-3 rounded-2xl
                  bg-gradient-to-r from-[#1a1f2e] to-[#0f1419] border border-orange-500/30
                  hover:border-orange-500/60 hover:shadow-lg hover:shadow-orange-500/10
                  transition-all duration-200 group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-orange-500 flex items-center justify-center flex-shrink-0 shadow-lg shadow-orange-500/30 group-hover:scale-105 transition-transform">
                    <span class="text-xs font-black text-white">PP</span>
                </div>
                <div>
                    <p class="text-sm font-bold text-white">Paripesa</p>
                    <p class="text-xs text-orange-400 font-semibold">Register & Get Welcome Bonus</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <span class="text-xs font-black text-black bg-orange-400 px-2.5 py-1 rounded-lg">Get Bonus</span>
                <svg class="w-4 h-4 text-orange-400 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </a>

        {{-- 22Bet --}}
        <a href="https://cutt.ly/DwBTVik8" target="_blank" rel="noopener noreferrer sponsored"
           class="flex items-center justify-between gap-3 px-4 py-3 rounded-2xl
                  bg-gradient-to-r from-[#1a1f2e] to-[#0f1419] border border-blue-500/30
                  hover:border-blue-500/60 hover:shadow-lg hover:shadow-blue-500/10
                  transition-all duration-200 group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center flex-shrink-0 shadow-lg shadow-blue-500/30 group-hover:scale-105 transition-transform">
                    <span class="text-xs font-black text-white">22</span>
                </div>
                <div>
                    <p class="text-sm font-bold text-white">22Bet</p>
                    <p class="text-xs text-blue-400 font-semibold">Register & Get Welcome Bonus</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <span class="text-xs font-black text-black bg-blue-400 px-2.5 py-1 rounded-lg">Get Bonus</span>
                <svg class="w-4 h-4 text-blue-400 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </a>
    </div>

    {{-- ── Telegram CTA ── --}}
    <a href="https://t.me/ballsigtips" target="_blank" rel="noopener noreferrer"
       class="mt-6 flex items-center justify-between gap-4 px-5 py-4 rounded-2xl
              bg-[#229ED9]/10 border border-[#229ED9]/30 hover:bg-[#229ED9]/20
              hover:border-[#229ED9]/50 transition-all duration-200 group">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#229ED9] flex items-center justify-center flex-shrink-0 shadow-lg shadow-[#229ED9]/30 group-hover:scale-105 transition-transform duration-200">
                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.248l-1.97 9.289c-.145.658-.537.818-1.084.508l-3-2.21-1.447 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.12L7.48 14.056l-2.95-.924c-.64-.203-.653-.64.136-.954l11.57-4.461c.537-.194 1.006.131.326.531z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-gray-900 dark:text-white">Join our Telegram Channel</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Get morefree daily tips</p>
            </div>
        </div>
        <svg class="w-4 h-4 text-[#229ED9] flex-shrink-0 group-hover:translate-x-1 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </a>

</div>

@endsection
