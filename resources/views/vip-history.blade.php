@extends('layouts.app')
@section('title', 'VIP Tips History — 30-Day Track Record — BallSignals')
@section('meta_description', 'See BallSignals VIP premium tip results for the last 30 days — daily odds, win/loss record, and accumulator performance.')
@section('canonical', route('vip-history'))

@section('content')

{{-- Hero --}}
<div class="bg-[#0a0f1a] border-b border-white/5">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 text-center">
        <div class="inline-flex items-center gap-1.5 bg-yellow-400/10 border border-yellow-400/30 text-yellow-400 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-4">
            <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 animate-pulse"></span>
            VIP Track Record
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-white leading-tight mb-2">
            Last 30 Days — <span class="text-yellow-400">Premium Results</span>
        </h1>
        <p class="text-gray-400 text-xs sm:text-sm max-w-lg mx-auto leading-relaxed">
            Every VIP pick from the past month. Real results, real odds — no cherry-picking.
        </p>

        {{-- Month stats bar --}}
        <div class="mt-6 grid grid-cols-4 gap-3 max-w-lg mx-auto">
            <div class="bg-white/5 rounded-2xl p-3 border border-white/10">
                <p class="text-xl font-black text-green-400">{{ $totalWon }}</p>
                <p class="text-[9px] uppercase tracking-widest text-gray-400 mt-0.5">Won</p>
            </div>
            <div class="bg-white/5 rounded-2xl p-3 border border-white/10">
                <p class="text-xl font-black text-red-400">{{ $totalLost }}</p>
                <p class="text-[9px] uppercase tracking-widest text-gray-400 mt-0.5">Lost</p>
            </div>
            <div class="bg-white/5 rounded-2xl p-3 border border-white/10">
                <p class="text-xl font-black text-yellow-400">{{ $winRate }}%</p>
                <p class="text-[9px] uppercase tracking-widest text-gray-400 mt-0.5">Win Rate</p>
            </div>
            <div class="bg-white/5 rounded-2xl p-3 border border-white/10">
                <p class="text-xl font-black text-white">{{ $perfectDays }}</p>
                <p class="text-[9px] uppercase tracking-widest text-gray-400 mt-0.5">Perfect Days</p>
            </div>
        </div>
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-6">

    @if($days->isEmpty())
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 py-12 px-5 text-center">
        <p class="text-3xl mb-3">🏆</p>
        <p class="font-semibold text-gray-700 dark:text-gray-300">No settled VIP tips yet</p>
        <p class="text-sm text-gray-400 mt-1">Results appear here as tips are settled daily.</p>
    </div>
    @else

    {{-- CTA for non-subscribers --}}
    <div class="mb-6 rounded-2xl border border-yellow-400/30 bg-yellow-400/5 px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="text-sm font-black text-yellow-400">Want tomorrow's picks?</p>
            <p class="text-xs text-gray-400 mt-0.5">Subscribe to VIP and get every premium tip before kick-off.</p>
        </div>
        <a href="{{ route('premium') }}"
           class="flex-shrink-0 inline-flex items-center gap-1.5 px-4 py-2 bg-yellow-400 hover:bg-yellow-300 rounded-xl text-xs font-black text-black transition-colors">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
            </svg>
            Unlock VIP Access
        </a>
    </div>

    {{-- Day-by-day cards --}}
    <div class="space-y-4">
        @foreach($days as $day)
        @php
            $date   = \Carbon\Carbon::parse($day['date']);
            $isGood = $day['all_won'];
            $hasMix = $day['won'] > 0 && $day['lost'] > 0;
        @endphp

        <div class="rounded-2xl overflow-hidden border
            {{ $isGood
                ? 'border-green-400/30 dark:border-green-600/30'
                : ($day['lost'] > 0 ? 'border-red-400/20 dark:border-red-600/20' : 'border-gray-200 dark:border-gray-800') }}">

            {{-- Day header --}}
            <div class="flex items-center justify-between px-4 py-3
                {{ $isGood
                    ? 'bg-green-500/10'
                    : ($day['lost'] > 0 ? 'bg-red-500/5 dark:bg-red-900/10' : 'bg-white dark:bg-gray-900') }}">
                <div class="flex items-center gap-3">
                    <div class="text-center min-w-[2.5rem]">
                        <p class="text-[9px] font-bold uppercase tracking-widest
                            {{ $isGood ? 'text-green-400' : ($day['lost'] > 0 ? 'text-red-400' : 'text-gray-400') }}">
                            {{ $date->format('D') }}
                        </p>
                        <p class="text-lg font-black leading-none
                            {{ $isGood ? 'text-green-400' : ($day['lost'] > 0 ? 'text-red-400' : 'text-gray-700 dark:text-white') }}">
                            {{ $date->format('d') }}
                        </p>
                        <p class="text-[9px] text-gray-400">{{ $date->format('M') }}</p>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            @if($day['won'] > 0)
                            <span class="text-[10px] font-black text-white bg-green-500 px-2 py-0.5 rounded-full">
                                {{ $day['won'] }}W
                            </span>
                            @endif
                            @if($day['lost'] > 0)
                            <span class="text-[10px] font-black text-white bg-red-500 px-2 py-0.5 rounded-full">
                                {{ $day['lost'] }}L
                            </span>
                            @endif
                            <span class="text-[10px] text-black bg-yellow-400 font-black px-1.5 py-0.5 rounded uppercase">Pro</span>
                        </div>
                        @if($isGood)
                        <p class="text-[9px] text-green-400 font-semibold mt-0.5">Perfect day ✓</p>
                        @elseif($hasMix)
                        <p class="text-[9px] text-gray-400 mt-0.5">Mixed results</p>
                        @else
                        <p class="text-[9px] text-red-400 mt-0.5">No wins today</p>
                        @endif
                    </div>
                </div>

                {{-- Accumulator odds --}}
                @if($day['accum_odds'])
                <div class="text-right flex-shrink-0">
                    <p class="text-[9px] uppercase tracking-widest text-gray-400">Accum. Odds</p>
                    <p class="text-lg font-black {{ $isGood ? 'text-yellow-400' : 'text-yellow-500/70' }} leading-tight">
                        {{ number_format($day['accum_odds'], 2) }}
                    </p>
                    <p class="text-[9px] text-gray-500">{{ $day['won'] }} leg{{ $day['won'] !== 1 ? 's' : '' }}</p>
                </div>
                @endif
            </div>

            {{-- Individual tips --}}
            @foreach($day['tips'] as $tip)
            <div class="bg-white dark:bg-gray-900 px-4 py-2.5 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-800' : '' }}">
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-gray-900 dark:text-white truncate">
                            {{ $tip->home_team }} <span class="font-normal text-gray-400">vs</span> {{ $tip->away_team }}
                        </p>
                        <p class="text-[10px] text-gray-400 truncate mt-0.5">
                            {{ $tip->match_time->format('g:i A') }}
                            @if($tip->league)<span class="mx-1">·</span>{{ $tip->league }}@endif
                        </p>
                    </div>
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        @if($tip->odds)
                        <span class="text-[10px] font-semibold text-gray-500 dark:text-gray-400">@ {{ number_format($tip->odds, 2) }}</span>
                        @endif
                        <span class="text-[10px] font-black px-1.5 py-0.5 rounded text-black bg-yellow-400">
                            {{ $tip->prediction }}
                        </span>
                        <span class="text-[10px] font-black px-2 py-0.5 rounded-full
                            {{ $tip->status === 'won' ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }}">
                            {{ ucfirst($tip->status) }}
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endforeach
    </div>

    {{-- Bottom CTA --}}
    <div class="mt-8 rounded-2xl border border-yellow-400/30 bg-yellow-400/5 px-5 py-6 text-center">
        <p class="text-base font-black text-yellow-400 mb-1">Join VIP — Get Tomorrow's Picks</p>
        <p class="text-xs text-gray-400 mb-4">Access every premium tip before kick-off, starting from $10/week.</p>
        <a href="{{ route('premium') }}"
           class="inline-flex items-center gap-2 px-6 py-3 bg-yellow-400 hover:bg-yellow-300 rounded-xl text-sm font-black text-black transition-colors">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
            </svg>
            Unlock VIP Access
        </a>
    </div>

    @endif
</div>

@endsection
