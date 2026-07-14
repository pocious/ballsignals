@extends('layouts.app')
@section('title', 'Live Matches — BallSignals')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <div class="flex items-center gap-2">
            <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
            </span>
            <h1 class="text-lg font-black text-gray-900 dark:text-white">Live Matches</h1>
        </div>
        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-500/10 text-red-400 border border-red-500/20">
            {{ $liveTips->count() + $liveVipTips->count() + count($worldLiveMatches) }} live
        </span>
        <span class="ml-auto text-xs text-gray-400">{{ now()->format('g:i A') }}</span>
    </div>

    {{-- World Live Matches --}}
    @if(count($worldLiveMatches) > 0)
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-1 h-4 rounded-full bg-red-500"></div>
            <h2 class="text-xs font-bold uppercase tracking-widest text-gray-700 dark:text-gray-300">Live Around The World</h2>
            <span class="text-[10px] text-gray-400">{{ count($worldLiveMatches) }} matches</span>
        </div>
        <div class="space-y-2">
            @foreach($worldLiveMatches as $match)
            <div class="rounded-xl border border-red-400/30 bg-red-50/10 dark:bg-red-900/5 dark:border-red-800/30 px-4 py-3">
                <div class="flex items-center justify-between gap-2">
                    {{-- Teams + Score --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ $match['home_team'] }}</span>
                                    <span class="text-lg font-black text-gray-900 dark:text-white tabular-nums">{{ $match['home_score'] }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-3 mt-0.5">
                                    <span class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ $match['away_team'] }}</span>
                                    <span class="text-lg font-black text-gray-900 dark:text-white tabular-nums">{{ $match['away_score'] }}</span>
                                </div>
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1 truncate">{{ $match['league'] }}</p>
                    </div>
                    {{-- Minute badge --}}
                    <div class="flex-shrink-0 flex flex-col items-center gap-1 pl-3 border-l border-red-200 dark:border-red-800/40">
                        <span class="inline-flex items-center gap-1 text-[9px] px-2 py-0.5 rounded-full font-bold bg-red-500/20 text-red-400 border border-red-500/40 animate-pulse whitespace-nowrap">
                            <span class="w-1 h-1 rounded-full bg-red-400"></span>
                            {{ $match['minute'] ?: 'Live' }}
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Live Free Tips --}}
    @if($liveTips->isNotEmpty())
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-1 h-4 rounded-full bg-red-500"></div>
            <h2 class="text-xs font-bold uppercase tracking-widest text-gray-700 dark:text-gray-300">Our Live Tips — Free</h2>
        </div>
        <div class="space-y-3">
            @foreach($liveTips as $tip)
            <div class="rounded-2xl overflow-hidden border border-red-400/40 bg-red-50/20 dark:bg-red-900/5 dark:border-red-700/40">
                <div class="px-4 pt-2.5 pb-2">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <p class="text-sm font-bold truncate text-gray-900 dark:text-white">
                            {{ $tip->home_team }}
                            <span class="font-normal text-gray-400 mx-1">vs</span>
                            {{ $tip->away_team }}
                        </p>
                        <span class="inline-flex items-center gap-1 text-[8px] px-1.5 py-px rounded-full font-bold flex-shrink-0 bg-red-500/20 text-red-400 border border-red-500/50 animate-pulse">
                            <span class="relative flex h-1.5 w-1.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-red-400"></span>
                            </span>
                            Live
                        </span>
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
                            <span class="text-[10px] font-black px-2 py-0.5 rounded-full whitespace-nowrap text-white" style="background:#22c55e;">
                                {{ $tip->prediction }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 overflow-hidden rounded-b-2xl border-t border-red-100 dark:border-red-900/30">
                    <a href="{{ route('tips.show', $tip) }}"
                       class="flex items-center justify-center gap-1 text-[10px] font-bold py-2 bg-green-500/10 hover:bg-green-500/20 text-green-600 dark:text-green-400 transition-colors border-r border-red-100 dark:border-red-900/30">
                        Analysis
                    </a>
                    <button onclick="shareTip(this)"
                            data-title="{{ $tip->home_team }} vs {{ $tip->away_team }}"
                            data-url="{{ route('tips.show', $tip) }}"
                            class="flex items-center justify-center gap-1 text-[10px] font-bold py-2 bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-700/50 text-gray-500 dark:text-gray-400 transition-colors">
                        Share
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Live VIP Tips --}}
    @if($liveVipTips->isNotEmpty() && $canSeePremium)
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-1 h-4 rounded-full bg-yellow-400"></div>
            <h2 class="text-xs font-bold uppercase tracking-widest text-gray-700 dark:text-gray-300">Our Live Tips — VIP</h2>
        </div>
        <div class="space-y-3">
            @foreach($liveVipTips as $tip)
            <div class="rounded-2xl overflow-hidden border-2 border-yellow-400/50 bg-yellow-50/10 dark:bg-yellow-900/5">
                <div class="px-4 pt-2.5 pb-2">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <p class="text-sm font-bold truncate text-gray-900 dark:text-white">
                            {{ $tip->home_team }}
                            <span class="font-normal text-gray-400 mx-1">vs</span>
                            {{ $tip->away_team }}
                        </p>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[9px] font-black text-black bg-yellow-400 px-1.5 py-0.5 rounded uppercase">PRO</span>
                            <span class="inline-flex items-center gap-1 text-[8px] px-1.5 py-px rounded-full font-bold flex-shrink-0 bg-red-500/20 text-red-400 border border-red-500/50 animate-pulse">Live</span>
                        </div>
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
                            <span class="text-[10px] font-black px-2 py-0.5 rounded-full whitespace-nowrap text-yellow-900 bg-yellow-400">
                                {{ $tip->prediction }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Upcoming in 2 hours --}}
    @if($upcomingTips->isNotEmpty())
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-1 h-4 rounded-full bg-blue-400"></div>
            <h2 class="text-xs font-bold uppercase tracking-widest text-gray-700 dark:text-gray-300">Starting Soon</h2>
            <span class="text-[10px] text-gray-400">next 2 hours</span>
        </div>
        <div class="space-y-3">
            @foreach($upcomingTips as $tip)
            <div class="rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                <div class="px-4 pt-2.5 pb-2">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <p class="text-sm font-bold truncate text-gray-900 dark:text-white">
                            {{ $tip->home_team }}
                            <span class="font-normal text-gray-400 mx-1">vs</span>
                            {{ $tip->away_team }}
                        </p>
                        <span class="text-[8px] px-1.5 py-px rounded-full font-bold flex-shrink-0 bg-blue-500/10 text-blue-400 border border-blue-400/30">
                            {{ $tip->match_time->diffForHumans() }}
                        </span>
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
                            <span class="text-[10px] font-black px-2 py-0.5 rounded-full whitespace-nowrap text-white" style="background:#22c55e;">
                                {{ $tip->prediction }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 overflow-hidden rounded-b-2xl border-t border-gray-100 dark:border-gray-800">
                    <a href="{{ route('tips.show', $tip) }}"
                       class="flex items-center justify-center gap-1 text-[10px] font-bold py-2 bg-green-500/10 hover:bg-green-500/20 text-green-600 dark:text-green-400 transition-colors border-r border-gray-100 dark:border-gray-800">
                        Analysis
                    </a>
                    <button onclick="shareTip(this)"
                            data-title="{{ $tip->home_team }} vs {{ $tip->away_team }}"
                            data-url="{{ route('tips.show', $tip) }}"
                            class="flex items-center justify-center gap-1 text-[10px] font-bold py-2 bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-700/50 text-gray-500 dark:text-gray-400 transition-colors">
                        Share
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Nothing at all --}}
    @if($liveTips->isEmpty() && $liveVipTips->isEmpty() && $upcomingTips->isEmpty() && count($worldLiveMatches) === 0)
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-16 text-center">
        <p class="font-black text-gray-900 dark:text-white text-base mb-2">No live matches right now</p>
        <p class="text-sm text-gray-400 mb-5">Check back when matches are scheduled to kick off.</p>
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-400 text-white font-bold text-sm px-5 py-2.5 rounded-xl transition-colors">
            View Today's Tips
        </a>
    </div>
    @endif

    {{-- Auto-refresh notice --}}
    <p class="text-center text-[10px] text-gray-400 mt-6">Page auto-refreshes every 60 seconds</p>

</div>

<script>
    setTimeout(() => location.reload(), 60000);

    function shareTip(btn) {
        const data = { title: btn.dataset.title, url: btn.dataset.url };
        if (navigator.share) { navigator.share(data); }
        else { navigator.clipboard.writeText(data.url); }
    }
</script>
@endsection
