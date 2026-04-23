@extends('layouts.app')
@section('title', 'Football League Statistics — BallSignals')
@section('meta_description', 'Football league statistics and standings for Premier League, La Liga, Bundesliga, Serie A, and more. Data to support smarter betting decisions.')
@section('canonical', route('league-stats'))

@section('content')

{{-- Hero --}}
<div class="bg-[#0a0f1a] border-b border-white/5">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-6 text-center">
        <div class="inline-flex items-center gap-1.5 bg-green-500/10 border border-green-500/25 text-green-400 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-3">
            <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>
            Performance Breakdown
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-white leading-tight mb-2">
            League <span class="text-green-400">Stats</span>
        </h1>
        <p class="text-gray-400 text-xs sm:text-sm max-w-lg mx-auto leading-relaxed">
            How our tips perform across every league we cover.
        </p>
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-6">

    {{-- Overall summary --}}
    <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-2xl font-black text-green-500">{{ $overall['won'] }}</p>
            <p class="text-[10px] uppercase tracking-widest text-gray-400 font-semibold mt-0.5">Total Won</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-2xl font-black text-gray-900 dark:text-white">{{ $overall['rate'] }}%</p>
            <p class="text-[10px] uppercase tracking-widest text-gray-400 font-semibold mt-0.5">Overall Rate</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-2xl font-black text-gray-500">{{ $overall['total'] }}</p>
            <p class="text-[10px] uppercase tracking-widest text-gray-400 font-semibold mt-0.5">Tips Settled</p>
        </div>
    </div>

    @if($leagueStats->isEmpty())
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="py-12 px-6 text-center">
                <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mx-auto mb-4">
                    <span class="text-3xl">📊</span>
                </div>
                <p class="text-base font-black text-gray-800 dark:text-gray-200 mb-1">No League Stats Yet</p>
                <p class="text-sm text-gray-400 dark:text-gray-500 max-w-xs mx-auto leading-relaxed mb-6">
                    Stats are calculated once tips have been settled as Won or Lost. Check back after today's matches.
                </p>
                <div class="flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('home') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-green-600 dark:text-green-400 border border-green-500/30 hover:bg-green-500/10 transition-colors">
                        View Today's Tips
                    </a>
                    <a href="{{ route('results') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 transition-colors">
                        Browse Results
                    </a>
                </div>
            </div>
            <div class="px-6 py-3 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-800 flex items-center gap-3">
                @foreach(['Premier League', 'La Liga', 'Serie A', 'Bundesliga', 'Champions League'] as $league)
                    <span class="text-[10px] px-2 py-0.5 rounded-full border border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-600">{{ $league }}</span>
                @endforeach
                <span class="text-[10px] text-gray-400 dark:text-gray-600 ml-auto whitespace-nowrap">& more coming</span>
            </div>
        </div>
    @else
        {{-- Legend --}}
        <div class="flex items-center gap-4 mb-3 px-1">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500">{{ $leagueStats->count() }} Leagues — sorted by win rate</p>
            <div class="flex items-center gap-3 ml-auto text-[10px] font-semibold">
                <span class="flex items-center gap-1 text-green-500"><span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>Won</span>
                <span class="flex items-center gap-1 text-red-500"><span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>Lost</span>
            </div>
        </div>

        <div class="space-y-3">
            @foreach($leagueStats as $i => $stat)
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="text-[10px] font-black text-gray-400 w-5 text-right flex-shrink-0">{{ $i + 1 }}</span>
                        <p class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ $stat['league'] }}</p>
                        @if($i === 0)
                            <span class="text-[9px] font-black text-black bg-yellow-400 px-1.5 py-0.5 rounded uppercase flex-shrink-0">Top</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0 text-xs font-semibold">
                        <span class="text-green-500">{{ $stat['won'] }}W</span>
                        <span class="text-red-500">{{ $stat['lost'] }}L</span>
                        <span class="text-gray-900 dark:text-white font-black text-sm w-12 text-right">{{ $stat['rate'] }}%</span>
                    </div>
                </div>

                {{-- Bar --}}
                <div class="flex h-1.5 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-800">
                    @if($stat['total'] > 0)
                        <div class="bg-green-500 {{ $stat['lost_pct'] == 0 ? 'rounded-full' : 'rounded-l-full' }}"
                             @style(['width' => $stat['won_pct'].'%'])></div>
                        <div class="bg-red-500 {{ $stat['won_pct'] == 0 ? 'rounded-full' : 'rounded-r-full' }}"
                             @style(['width' => $stat['lost_pct'].'%'])></div>
                    @else
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                    @endif
                </div>
                <p class="text-[10px] text-gray-400 mt-1">{{ $stat['total'] }} tips settled</p>
            </div>
            @endforeach
        </div>
    @endif

    {{-- CTA back to results --}}
    <a href="{{ route('results') }}"
       class="mt-8 flex items-center justify-between gap-3 px-5 py-4 rounded-2xl
              bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800
              hover:border-green-500/40 transition-all duration-200 group">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-green-500/10 border border-green-500/30 flex items-center justify-center flex-shrink-0 group-hover:bg-green-500/20 transition-colors">
                <svg class="w-5 h-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-gray-900 dark:text-white">View All Results</p>
                <p class="text-xs text-gray-400">Browse every tip we have posted</p>
            </div>
        </div>
        <svg class="w-4 h-4 text-gray-400 group-hover:text-green-400 group-hover:translate-x-1 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </a>

</div>

@endsection
