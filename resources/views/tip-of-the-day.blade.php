@extends('layouts.app')
@section('title', "Today's Best Football Tip — BallSignals")
@section('meta_description', "Today's top football betting tip, hand-picked by BallSignals analysts. One high-confidence prediction selected daily using statistical models and expert review.")
@section('canonical', route('tip-of-the-day'))

@section('content')

{{-- Hero --}}
<div class="bg-[#0a0f1a] border-b border-white/5">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-10 text-center">
        <div class="inline-flex items-center gap-1.5 bg-green-500/10 border border-green-500/25 text-green-400 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-4">
            <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
            {{ now()->format('l, d M Y') }}
        </div>
        <h1 class="text-3xl sm:text-4xl font-black text-white mb-2">Tip of the Day</h1>
        <p class="text-gray-400 text-sm max-w-md mx-auto">Our highest-confidence free pick for today — handpicked by our expert analysts.</p>
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-8">

    @if($tip)

    {{-- Main Tip Card --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border-2 border-green-500/50 overflow-hidden shadow-xl shadow-green-500/10 mb-6">
        <div class="h-1.5 bg-gradient-to-r from-green-500 to-emerald-400"></div>
        <div class="p-6">

            {{-- League + time --}}
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2">
                    @if($tip->league)
                    <span class="text-xs font-bold text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-2.5 py-1 rounded-full">
                        {{ $tip->league }}
                    </span>
                    @endif
                    @if($tip->country)
                    <span class="text-xs text-gray-400">{{ $tip->country }}</span>
                    @endif
                </div>
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2.5 py-1 rounded-full">
                    {{ $tip->match_time->format('g:i A') }}
                </span>
            </div>

            {{-- Teams --}}
            <div class="flex items-center justify-between gap-4 mb-6">
                <div class="flex-1 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mx-auto mb-2 text-2xl font-black text-gray-400">
                        {{ strtoupper(substr($tip->home_team, 0, 1)) }}
                    </div>
                    <p class="text-sm font-black text-gray-900 dark:text-white leading-tight">{{ $tip->home_team }}</p>
                </div>
                <div class="flex-shrink-0 text-center">
                    <span class="text-xs font-bold text-gray-400 bg-gray-100 dark:bg-gray-800 px-3 py-1.5 rounded-full">VS</span>
                </div>
                <div class="flex-1 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mx-auto mb-2 text-2xl font-black text-gray-400">
                        {{ strtoupper(substr($tip->away_team, 0, 1)) }}
                    </div>
                    <p class="text-sm font-black text-gray-900 dark:text-white leading-tight">{{ $tip->away_team }}</p>
                </div>
            </div>

            {{-- Prediction + Odds + Confidence --}}
            <div class="grid grid-cols-3 gap-3 mb-5">
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-3 text-center">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-green-600 dark:text-green-400 mb-1">Prediction</p>
                    <p class="text-sm font-black text-green-700 dark:text-green-300">{{ $tip->prediction }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3 text-center">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1">Odds</p>
                    <p class="text-sm font-black text-gray-900 dark:text-white">
                        {{ $tip->odds ? number_format($tip->odds, 2) : '—' }}
                    </p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3 text-center">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1">Confidence</p>
                    <div class="flex items-center justify-center gap-px">
                        @for($i = 1; $i <= 5; $i++)
                            <span class="text-sm {{ $i <= $tip->confidence ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}">★</span>
                        @endfor
                    </div>
                </div>
            </div>

            {{-- Status badge --}}
            <div class="flex items-center justify-center">
                <span class="text-xs px-4 py-1.5 rounded-full font-bold {{ $tip->status_badge }}">
                    {{ ucfirst($tip->status) }}
                </span>
            </div>
        </div>
    </div>

    {{-- Analyst note --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 mb-6">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-full bg-green-500 flex items-center justify-center flex-shrink-0">
                <span class="text-xs font-black text-black">JH</span>
            </div>
            <div>
                <p class="text-sm font-bold text-gray-900 dark:text-white">James Harrison <span class="text-green-500 font-normal text-xs">· Head Analyst</span></p>
                <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mt-1">
                    This pick is selected based on current form, head-to-head records, team news, and odds value analysis. Always bet responsibly and within your limits.
                </p>
            </div>
        </div>
    </div>

    @else

    {{-- No tip today --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden mb-6">
        <div class="h-1 bg-gradient-to-r from-gray-200 to-gray-100 dark:from-gray-700 dark:to-gray-800"></div>
        <div class="py-12 px-6 text-center">
            <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mx-auto mb-4">
                <span class="text-3xl">⚽</span>
            </div>
            <p class="text-base font-black text-gray-800 dark:text-gray-200 mb-1">No Tip Posted Yet Today</p>
            <p class="text-sm text-gray-400 dark:text-gray-500 max-w-xs mx-auto leading-relaxed mb-1">
                Our analysts are currently reviewing today's matches and will post the best pick shortly.
            </p>
            <p class="text-xs text-gray-400 dark:text-gray-600 mb-6">Check back in a few hours.</p>
            <div class="flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('home') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-green-600 dark:text-green-400 border border-green-500/30 hover:bg-green-500/10 transition-colors">
                    View All Tips
                </a>
                <a href="{{ route('results') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 transition-colors">
                    Past Results
                </a>
            </div>
        </div>
        <div class="px-6 py-3 bg-green-50 dark:bg-green-900/10 border-t border-green-100 dark:border-green-900/30 flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse flex-shrink-0"></span>
            <p class="text-xs text-green-700 dark:text-green-400 font-medium">Our analysts post the tip of the day every morning — usually before 9 AM.</p>
        </div>
    </div>

    @endif

    {{-- Stats bar --}}
    <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-2xl font-black text-green-500">{{ $stats['won'] }}</p>
            <p class="text-[10px] uppercase tracking-widest text-gray-400 font-semibold mt-0.5">Total Won</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-2xl font-black text-gray-900 dark:text-white">{{ $stats['rate'] }}%</p>
            <p class="text-[10px] uppercase tracking-widest text-gray-400 font-semibold mt-0.5">Win Rate</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <p class="text-2xl font-black text-red-500">{{ $stats['lost'] }}</p>
            <p class="text-[10px] uppercase tracking-widest text-gray-400 font-semibold mt-0.5">Total Lost</p>
        </div>
    </div>

    {{-- Recent wins --}}
    @if($recentWins->isNotEmpty())
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden mb-6">
        <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100 dark:border-gray-800">
            <div class="w-1.5 h-4 rounded-full bg-green-500"></div>
            <h2 class="text-xs font-bold uppercase tracking-widest text-gray-600 dark:text-gray-400">Recent Wins</h2>
        </div>
        @foreach($recentWins as $win)
        <div class="px-5 py-3 flex items-center justify-between gap-3 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-800' : '' }}">
            <div class="min-w-0">
                <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 truncate">{{ $win->home_team }} vs {{ $win->away_team }}</p>
                <p class="text-[10px] text-gray-400">{{ $win->match_time->format('d M Y') }} · {{ $win->prediction }}</p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                @if($win->odds)
                <span class="text-xs font-bold text-gray-500">{{ number_format($win->odds, 2) }}</span>
                @endif
                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-green-500 text-white">Won</span>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- CTA --}}
    <div class="bg-[#0a0f1a] rounded-2xl border border-green-500/20 p-6 text-center">
        <p class="text-white font-black text-base mb-1">Want More Premium Picks?</p>
        <p class="text-gray-400 text-xs mb-4">Get high-confidence tips, accumulators and VIP access every day.</p>
        <a href="{{ route('premium') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-black text-black bg-green-400 hover:bg-green-300 shadow-lg shadow-green-500/20 transition-all duration-200">
            Go Premium
        </a>
    </div>

</div>
@endsection
