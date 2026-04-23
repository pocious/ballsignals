@extends('layouts.app')
@section('title', 'Betting Tips Results & Track Record — BallSignals')
@section('meta_description', 'BallSignals betting tips track record. View our prediction results, win rate, and historical accuracy across Premier League, La Liga, Champions League, and more.')
@section('canonical', route('results'))

@section('content')

{{-- Hero --}}
<div class="bg-[#0a0f1a] border-b border-white/5">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-6 text-center">
        <div class="inline-flex items-center gap-1.5 bg-green-500/10 border border-green-500/25 text-green-400 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-3">
            <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>
            Historical Record
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-white leading-tight mb-2">
            Our <span class="text-green-400">Results</span>
        </h1>
        <p class="text-gray-400 text-xs sm:text-sm max-w-lg mx-auto leading-relaxed">
            Full transparency — every tip we have posted, every outcome.
        </p>
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-6">

    {{-- Overall stats --}}
    <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-2xl font-black text-green-500">{{ $stats['won'] }}</p>
            <p class="text-[10px] uppercase tracking-widest text-gray-400 font-semibold mt-0.5">Won</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-2xl font-black text-gray-900 dark:text-white">{{ $stats['rate'] }}%</p>
            <p class="text-[10px] uppercase tracking-widest text-gray-400 font-semibold mt-0.5">Win Rate</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-2xl font-black text-red-500">{{ $stats['lost'] }}</p>
            <p class="text-[10px] uppercase tracking-widest text-gray-400 font-semibold mt-0.5">Lost</p>
        </div>
    </div>

    {{-- Win rate bar --}}
    @if($stats['total'] > 0)
    <div class="mb-6">
        <div class="flex h-2 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-800">
            <div class="bg-green-500 transition-all duration-500 {{ $stats['lost_pct'] == 0 ? 'rounded-full' : 'rounded-l-full' }}"
                 @style(['width' => $stats['won_pct'].'%'])></div>
            <div class="bg-red-500 transition-all duration-500 {{ $stats['won_pct'] == 0 ? 'rounded-full' : 'rounded-r-full' }}"
                 @style(['width' => $stats['lost_pct'].'%'])></div>
        </div>
        <div class="flex justify-between text-[9px] font-semibold mt-1">
            <span class="text-green-600 dark:text-green-400">Won {{ $stats['won_pct'] }}%</span>
            <span class="text-gray-400">{{ $stats['total'] }} settled tips</span>
            <span class="text-red-500">Lost {{ $stats['lost_pct'] }}%</span>
        </div>
    </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('results') }}" class="flex flex-wrap items-center gap-2 mb-5">
        {{-- League filter --}}
        <select name="league" onchange="this.form.submit()"
                class="text-xs border border-white/10 rounded-lg px-3 py-2
                       bg-[#0a0f1a] text-gray-300 focus:outline-none focus:ring-1 focus:ring-green-500">
            <option value="">All Leagues</option>
            @foreach($leagues as $league)
                <option value="{{ $league }}" {{ $selectedLeague === $league ? 'selected' : '' }}>
                    {{ $league }}
                </option>
            @endforeach
        </select>

        {{-- Status filter --}}
        <select name="status" onchange="this.form.submit()"
                class="text-xs border border-white/10 rounded-lg px-3 py-2
                       bg-[#0a0f1a] text-gray-300 focus:outline-none focus:ring-1 focus:ring-green-500">
            <option value="">All Results</option>
            <option value="won"  {{ $selectedStatus === 'won'  ? 'selected' : '' }}>Won Only</option>
            <option value="lost" {{ $selectedStatus === 'lost' ? 'selected' : '' }}>Lost Only</option>
        </select>

        @if($selectedLeague || $selectedStatus)
            <a href="{{ route('results') }}" class="text-xs text-gray-500 hover:text-red-400 transition-colors px-2 py-2">
                Clear filters
            </a>
        @endif
    </form>

    {{-- Results list --}}
    @if($results->isEmpty())
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 py-16 text-center">
            <p class="text-3xl mb-3">📋</p>
            <p class="font-semibold text-gray-700 dark:text-gray-300">No results found</p>
            <p class="text-sm text-gray-400 dark:text-gray-600 mt-1">Try clearing your filters.</p>
        </div>
    @else
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden mb-6">
            @foreach($results as $tip)
            <div class="px-4 py-3 flex items-center justify-between gap-3
                        {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-800' : '' }}">
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate">
                        {{ $tip->home_team }} <span class="font-normal text-gray-400">vs</span> {{ $tip->away_team }}
                    </p>
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">
                        {{ $tip->match_time->format('d M Y · g:i A') }}
                        @if($tip->league)
                            <span class="mx-1">·</span>{{ $tip->league }}
                        @endif
                    </p>
                </div>
                <div class="flex-shrink-0 flex items-center gap-2">
                    @if($tip->confidence)
                        <div class="flex items-center gap-px">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="text-[10px] {{ $i <= $tip->confidence ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}">★</span>
                            @endfor
                        </div>
                    @endif
                    <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400">{{ $tip->prediction }}</span>
                    @if($tip->odds)
                        <span class="text-[11px] text-gray-400">{{ number_format($tip->odds, 2) }}</span>
                    @endif
                    <span class="text-[11px] px-2 py-0.5 rounded-full font-semibold {{ $tip->status_badge }}">
                        {{ ucfirst($tip->status) }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($results->hasPages())
            <div class="flex items-center justify-center gap-1 flex-wrap">
                @if($results->onFirstPage())
                    <span class="px-3 py-1.5 text-xs text-gray-600 bg-white/5 rounded-lg cursor-not-allowed">← Prev</span>
                @else
                    <a href="{{ $results->previousPageUrl() }}" class="px-3 py-1.5 text-xs text-gray-400 hover:text-white bg-white/5 hover:bg-white/10 rounded-lg transition-colors">← Prev</a>
                @endif

                @foreach($results->getUrlRange(max(1, $results->currentPage()-2), min($results->lastPage(), $results->currentPage()+2)) as $page => $url)
                    <a href="{{ $url }}"
                       class="px-3 py-1.5 text-xs rounded-lg transition-colors
                              {{ $page == $results->currentPage() ? 'bg-green-600 text-white font-bold' : 'text-gray-400 hover:text-white bg-white/5 hover:bg-white/10' }}">
                        {{ $page }}
                    </a>
                @endforeach

                @if($results->hasMorePages())
                    <a href="{{ $results->nextPageUrl() }}" class="px-3 py-1.5 text-xs text-gray-400 hover:text-white bg-white/5 hover:bg-white/10 rounded-lg transition-colors">Next →</a>
                @else
                    <span class="px-3 py-1.5 text-xs text-gray-600 bg-white/5 rounded-lg cursor-not-allowed">Next →</span>
                @endif
            </div>
        @endif
    @endif

    {{-- League Stats CTA --}}
    <a href="{{ route('league-stats') }}"
       class="mt-8 flex items-center justify-between gap-3 px-5 py-4 rounded-2xl
              bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800
              hover:border-green-500/40 transition-all duration-200 group">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-green-500/10 border border-green-500/30 flex items-center justify-center flex-shrink-0 group-hover:bg-green-500/20 transition-colors">
                <svg class="w-5 h-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-gray-900 dark:text-white">League Stats</p>
                <p class="text-xs text-gray-400">See our performance broken down by league</p>
            </div>
        </div>
        <svg class="w-4 h-4 text-gray-400 group-hover:text-green-400 group-hover:translate-x-1 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </a>

</div>

@endsection
