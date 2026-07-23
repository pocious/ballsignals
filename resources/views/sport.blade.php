@extends('layouts.app')
@section('title', $config['name'] . ' Tips — BallSignals')
@section('meta_description', 'Free daily ' . $config['name'] . ' betting tips and predictions. Expert picks updated every day on BallSignals.')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6 space-y-6">

    {{-- Header --}}
    <div class="mb-4">
        <div class="flex items-center justify-between mb-1">
            <h1 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span>{{ $config['name'] }} Tips</span>
            </h1>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-full border border-gray-200 dark:border-gray-700 text-xs font-semibold text-gray-500 dark:text-gray-400">
                    {{ $tipsByDate->flatten()->count() }} tip{{ $tipsByDate->flatten()->count() !== 1 ? 's' : '' }}
                </span>
                @if($yesterdayTips->isNotEmpty())
                <a href="#sport-results"
                   class="px-3 py-1.5 rounded-full bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs transition-colors shadow-sm shadow-orange-500/30 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Yesterday's Results
                </a>
                @endif
            </div>
        </div>
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-400 dark:text-gray-500">{{ now()->format('l, d M Y') }}</p>
            <div class="flex items-center gap-3 text-xs font-semibold">
                <span class="flex items-center gap-1 text-green-600 dark:text-green-400">
                    <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
                    {{ $stats['won'] }}W
                </span>
                <span class="flex items-center gap-1 text-red-500 dark:text-red-400">
                    <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                    {{ $stats['lost'] }}L
                </span>
                @if($stats['total'] > 0)
                <span class="text-gray-500 dark:text-gray-400">{{ $stats['rate'] }}% yesterday</span>
                @endif
            </div>
        </div>
    </div>

    {{-- League filter --}}
    @if($leagues->isNotEmpty())
    <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
        <a href="{{ route('sport', $sport) }}"
           class="flex-shrink-0 px-3 py-1 rounded-full text-xs font-semibold transition-colors
                  {{ !$selectedLeague ? 'bg-orange-500 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-orange-500/10 hover:text-orange-500' }}">
            All
        </a>
        @foreach($leagues as $league)
        <a href="{{ route('sport', ['sport' => $sport, 'league' => $league]) }}"
           class="flex-shrink-0 px-3 py-1 rounded-full text-xs font-semibold transition-colors
                  {{ $selectedLeague === $league ? 'bg-orange-500 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-orange-500/10 hover:text-orange-500' }}">
            {{ $league }}
        </a>
        @endforeach
    </div>
    @endif

    {{-- Sort bar --}}
    <div class="flex items-center gap-1.5 text-[10px] font-bold">
        <span class="text-gray-400 dark:text-gray-500 mr-1">Sort:</span>
        @foreach(['time' => 'By Time', 'odds_asc' => 'Odds ↑', 'odds_desc' => 'Odds ↓'] as $val => $label)
        <a href="{{ route('sport', array_merge(['sport' => $sport], request()->query(), ['sort' => $val])) }}"
           class="px-2.5 py-1 rounded-full transition-colors
                  {{ $selectedSort === $val ? 'bg-orange-500 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-orange-500/10 hover:text-orange-500' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- Tips --}}
    @if($tipsByDate->isEmpty())
    @php
        $allSportLinks = [
            ['slug' => null,               'route' => route('home'),              'name' => 'Football',         'icon' => '⚽', 'db' => 'Football'],
            ['slug' => 'basketball',       'route' => route('basketball'),        'name' => 'Basketball',       'icon' => '🏀', 'db' => 'Basketball'],
            ['slug' => 'tennis',           'route' => route('sport','tennis'),           'name' => 'Tennis',           'icon' => '🎾', 'db' => 'Tennis'],
            ['slug' => 'cricket',          'route' => route('sport','cricket'),          'name' => 'Cricket',          'icon' => '🏏', 'db' => 'Cricket'],
            ['slug' => 'mma',              'route' => route('sport','mma'),              'name' => 'MMA',              'icon' => '🥊', 'db' => 'MMA'],
            ['slug' => 'baseball',         'route' => route('sport','baseball'),         'name' => 'Baseball',         'icon' => '⚾', 'db' => 'Baseball'],
            ['slug' => 'american-football','route' => route('sport','american-football'),'name' => 'NFL',              'icon' => '🏈', 'db' => 'American Football'],
            ['slug' => 'hockey',           'route' => route('sport','hockey'),           'name' => 'Hockey',           'icon' => '🏒', 'db' => 'Hockey'],
            ['slug' => 'rugby',            'route' => route('sport','rugby'),            'name' => 'Rugby',            'icon' => '🏉', 'db' => 'Rugby'],
        ];
        $availableSports = collect($allSportLinks)->filter(fn($s) =>
            $s['db'] !== $config['db'] &&
            \App\Models\BettingTip::where('sport', $s['db'])
                ->where('match_time', '>=', today()->startOfDay())
                ->where('match_time', '<=', today()->addDays(7)->endOfDay())
                ->where('is_premium', false)
                ->exists()
        )->values();
    @endphp
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 py-10 px-5 text-center">
            <p class="text-3xl mb-3">{{ $config['icon'] }}</p>
            <p class="font-semibold text-gray-700 dark:text-gray-300">No {{ $config['name'] }} tips right now</p>
            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1 mb-6">The season may be on a break — tips appear as soon as matches are scheduled.</p>

            @if($availableSports->isNotEmpty())
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-3">Available tips today</p>
            <div class="flex flex-wrap justify-center gap-2">
                @foreach($availableSports as $s)
                @php
                    $cnt = \App\Models\BettingTip::where('sport', $s['db'])
                        ->where('match_time', '>=', today()->startOfDay())
                        ->where('match_time', '<=', today()->addDays(7)->endOfDay())
                        ->where('is_premium', false)->count();
                @endphp
                <a href="{{ $s['route'] }}"
                   class="flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 hover:border-orange-400/50 hover:bg-orange-50/30 dark:hover:bg-orange-900/10 transition-all duration-150">
                    <span class="text-lg leading-none">{{ $s['icon'] }}</span>
                    <div class="text-left">
                        <p class="text-xs font-bold text-gray-800 dark:text-white leading-none">{{ $s['name'] }}</p>
                        <p class="text-[10px] text-orange-500 font-semibold mt-0.5">{{ $cnt }} tip{{ $cnt !== 1 ? 's' : '' }}</p>
                    </div>
                </a>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Matches from available sports --}}
        @foreach($availableSports as $s)
        @php
            $sportTips = \App\Models\BettingTip::where('sport', $s['db'])
                ->where('match_time', '>=', today()->startOfDay())
                ->where('match_time', '<=', today()->addDays(7)->endOfDay())
                ->where('is_premium', false)
                ->orderByRaw('DATE(match_time) ASC, match_time ASC')
                ->limit(5)
                ->get();
        @endphp
        @if($sportTips->isNotEmpty())
        <div class="mt-5">
            <div class="flex items-center justify-between mb-2 px-1">
                <div class="flex items-center gap-2">
                    <span class="text-base">{{ $s['icon'] }}</span>
                    <span class="text-xs font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">{{ $s['name'] }}</span>
                </div>
                <a href="{{ $s['route'] }}" class="text-[10px] font-bold text-orange-500 hover:text-orange-400 flex items-center gap-0.5">
                    See all
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="space-y-2">
                @foreach($sportTips as $tip)
                <a href="{{ route('tips.show', $tip) }}"
                   class="flex items-center justify-between gap-3 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 hover:border-orange-400/40 transition-colors">
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-gray-900 dark:text-white truncate">
                            {{ $tip->home_team }} <span class="font-normal text-gray-400 text-xs">vs</span> {{ $tip->away_team }}
                        </p>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate mt-0.5">
                            {{ $tip->match_time->format('g:i A · d M') }}
                            @if($tip->league)<span class="mx-1">·</span>{{ $tip->league }}@endif
                        </p>
                    </div>
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        @if($tip->odds)
                        <span class="text-[10px] text-gray-400">@ {{ number_format($tip->odds, 2) }}</span>
                        @endif
                        <span class="text-[10px] font-black px-2 py-0.5 rounded-full text-white whitespace-nowrap" style="background:#f97316;">
                            {{ $tip->prediction }}
                        </span>
                    </div>
                </a>
                @endforeach
                @if(\App\Models\BettingTip::where('sport', $s['db'])->where('match_time', '>=', today()->startOfDay())->where('match_time', '<=', today()->addDays(7)->endOfDay())->where('is_premium', false)->count() > 5)
                <a href="{{ $s['route'] }}" class="flex items-center justify-center gap-1 py-2 text-[11px] font-bold text-orange-500 hover:text-orange-400 transition-colors">
                    View all {{ \App\Models\BettingTip::where('sport', $s['db'])->where('match_time', '>=', today()->startOfDay())->where('match_time', '<=', today()->addDays(7)->endOfDay())->where('is_premium', false)->count() }} {{ $s['name'] }} tips →
                </a>
                @endif
            </div>
        </div>
        @endif
        @endforeach
    @else
        <div class="space-y-5">
            @foreach($tipsByDate as $date => $tips)
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-orange-500 mb-2 px-1">
                    @if($date === today()->toDateString())
                        Today · {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                    @elseif($date === today()->addDay()->toDateString())
                        Tomorrow · {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                    @else
                        {{ \Carbon\Carbon::parse($date)->format('l · d M Y') }}
                    @endif
                </p>
                <div class="space-y-3">
                    @foreach($tips as $tip)
                    @php
                        $bNow = now();
                        $liveMin = $config['live_minutes'];
                        $bIsLive     = $tip->status === 'pending' && $tip->match_time <= $bNow && $tip->match_time >= $bNow->copy()->subMinutes($liveMin);
                        $bIsFinished = in_array($tip->status, ['won','lost']) || ($tip->status === 'pending' && $tip->match_time < $bNow->copy()->subMinutes($liveMin));
                    @endphp
                    <div class="rounded-2xl overflow-hidden border
                        {{ $tip->status === 'won' ? 'border-green-400 dark:border-green-700/60 bg-green-50/40 dark:bg-green-900/10'
                         : ($tip->status === 'lost' ? 'border-red-400 dark:border-red-700/60 bg-red-50/40 dark:bg-red-900/10'
                         : 'border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900') }}">
                        <div class="px-4 pt-2.5 pb-2">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <p class="text-sm font-bold truncate text-gray-900 dark:text-white">
                                    {{ $tip->home_team }}
                                    <span class="font-normal text-gray-400 mx-1">vs</span>
                                    {{ $tip->away_team }}
                                </p>
                                @if($bIsLive)
                                    <span class="inline-flex items-center gap-0.5 text-[8px] px-1.5 py-px rounded-full font-bold flex-shrink-0 bg-red-50 dark:bg-red-500/15 text-red-500 border border-red-200 dark:border-red-500/35 animate-pulse">
                                        <span class="w-1 h-1 rounded-full bg-red-500 flex-shrink-0"></span>
                                        Live
                                    </span>
                                @elseif($tip->status === 'won')
                                    <span class="text-[8px] px-1.5 py-px rounded-full font-bold flex-shrink-0 bg-green-500/20 text-green-400 border border-green-500/30">✔ Won</span>
                                @elseif($tip->status === 'lost')
                                    <span class="text-[8px] px-1.5 py-px rounded-full font-bold flex-shrink-0 bg-red-500/20 text-red-400 border border-red-500/30">✗ Lost</span>
                                @elseif($bIsFinished)
                                    <span class="text-[8px] px-1.5 py-px rounded-full font-semibold flex-shrink-0 bg-gray-200 text-gray-500 dark:bg-gray-700 dark:text-gray-400">Finished</span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[8px] px-1.5 py-px rounded-full font-bold flex-shrink-0 bg-yellow-400/20 text-yellow-500 border border-yellow-400/40">
                                        <span class="relative flex h-1.5 w-1.5">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-yellow-400"></span>
                                        </span>
                                        Pending
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">
                                    {{ $tip->match_time->format('g:i A') }}
                                    @if($tip->league)<span class="mx-1">·</span>{{ $tip->league }}@endif
                                </p>
                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                    @if($tip->confidence)
                                    <span class="text-[11px] leading-none text-yellow-400">{{ str_repeat('★', $tip->confidence) }}<span class="text-gray-300 dark:text-gray-700">{{ str_repeat('★', 5 - $tip->confidence) }}</span></span>
                                    @endif
                                    @if($tip->odds)
                                    <span class="text-[10px] font-semibold text-gray-500 dark:text-gray-400">@ {{ number_format($tip->odds, 2) }}</span>
                                    @endif
                                    <span class="text-[10px] font-black px-2 py-0.5 rounded-full whitespace-nowrap text-white"
                                          style="background:#f97316;">
                                        {{ $tip->prediction }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 overflow-hidden rounded-b-2xl border-t border-gray-100 dark:border-gray-800">
                            <a href="{{ route('tips.show', $tip) }}"
                               class="flex items-center justify-center gap-1 text-[10px] font-bold py-2 bg-orange-500/10 hover:bg-orange-500/20 text-orange-600 dark:text-orange-400 transition-colors border-r border-gray-100 dark:border-gray-800">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                Analysis
                            </a>
                            <a href="{{ route('premium') }}"
                               class="flex items-center justify-center gap-1 text-[10px] font-bold py-2 bg-yellow-400/10 hover:bg-yellow-400/20 text-yellow-600 dark:text-yellow-400 transition-colors border-r border-gray-100 dark:border-gray-800">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                VIP Odds
                            </a>
                            <button onclick="shareTip(this)"
                                    data-title="{{ $tip->home_team }} vs {{ $tip->away_team }}"
                                    data-url="{{ route('tips.show', $tip) }}"
                                    class="flex items-center justify-center gap-1 text-[10px] font-bold py-2 bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-700/50 text-gray-500 dark:text-gray-400 transition-colors">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                                </svg>
                                Share
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    @endif

    {{-- Yesterday's Results --}}
    @if($yesterdayTips->isNotEmpty())
    <div id="sport-results" class="mt-6">
        <p class="text-xs font-bold uppercase tracking-widest text-gray-500 mb-3 px-1">Yesterday's Results</p>
        <div class="space-y-2">
            @foreach($yesterdayTips as $tip)
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 px-4 py-2.5 flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                        {{ $tip->home_team }} vs {{ $tip->away_team }}
                    </p>
                    <p class="text-[11px] text-gray-400">{{ $tip->league }}</p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    @if($tip->odds)
                    <span class="text-[10px] font-semibold text-gray-500 dark:text-gray-400">@ {{ number_format($tip->odds, 2) }}</span>
                    @endif
                    <span class="text-[10px] font-black px-2 py-0.5 rounded-full text-white" style="background:#f97316;">
                        {{ $tip->prediction }}
                    </span>
                    @if($tip->status === 'won')
                        <span class="text-[10px] font-black px-2 py-0.5 rounded-full bg-green-500/20 text-green-400 border border-green-500/30">✓ Won</span>
                    @elseif($tip->status === 'lost')
                        <span class="text-[10px] font-black px-2 py-0.5 rounded-full bg-red-500/20 text-red-400 border border-red-500/30">✗ Lost</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

<script>
function shareTip(btn) {
    var url = btn.dataset.url, title = btn.dataset.title;
    if (navigator.share) {
        navigator.share({ title: title, url: url });
    } else {
        navigator.clipboard.writeText(url);
        var h = btn.innerHTML;
        btn.textContent = 'Copied!';
        setTimeout(function() { btn.innerHTML = h; }, 1500);
    }
}
</script>
@endsection
