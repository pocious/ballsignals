@extends('layouts.app')
@section('title', $bettingTip->home_team . ' vs ' . $bettingTip->away_team . ' — Tip Analysis')
@section('meta_description', 'Expert analysis for ' . $bettingTip->prediction . ' — ' . $bettingTip->home_team . ' vs ' . $bettingTip->away_team)

@section('content')

<div class="max-w-2xl mx-auto px-4 sm:px-6 py-10">

    {{-- Back button --}}
    <a href="{{ url()->previous() }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white mb-6 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back
    </a>

    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">

        {{-- Top colour bar --}}
        <div class="h-1.5 {{ $bettingTip->is_premium ? 'bg-gradient-to-r from-yellow-400 to-orange-400' : 'bg-gradient-to-r from-green-400 to-emerald-500' }}"></div>

        <div class="p-6 sm:p-8">

            {{-- Premium badge --}}
            @if($bettingTip->is_premium)
            <div class="inline-flex items-center gap-1.5 bg-yellow-400/10 border border-yellow-400/30 text-yellow-600 dark:text-yellow-400 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-4">
                <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 animate-pulse"></span>
                VIP Premium Tip
            </div>
            @endif

            {{-- League / Sport --}}
            <div class="flex items-center gap-2 mb-4 flex-wrap">
                <span class="text-xl">{{ $bettingTip->sport_icon }}</span>
                @if($bettingTip->league)
                    <span class="text-xs font-bold {{ $bettingTip->is_premium ? 'text-yellow-700 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800' : 'text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' }} px-2.5 py-1 rounded-full">
                        {{ $bettingTip->league }}
                    </span>
                @endif
                @if($bettingTip->country)
                    <span class="text-xs text-gray-400">{{ $bettingTip->country }}</span>
                @endif
                <span class="ml-auto text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2.5 py-1 rounded-full">
                    {{ $bettingTip->match_time->format('d M Y · g:i A') }}
                </span>
            </div>

            {{-- Teams --}}
            <div class="flex items-center justify-between gap-4 mb-6">
                <p class="text-lg sm:text-2xl font-black text-gray-900 dark:text-white flex-1">{{ $bettingTip->home_team }}</p>
                <span class="text-xs font-bold text-gray-400 bg-gray-100 dark:bg-gray-800 px-3 py-1.5 rounded-full flex-shrink-0">VS</span>
                <p class="text-lg sm:text-2xl font-black text-gray-900 dark:text-white flex-1 text-right">{{ $bettingTip->away_team }}</p>
            </div>

            {{-- Prediction / Odds / Confidence --}}
            <div class="grid grid-cols-3 gap-3 mb-6">
                <div class="{{ $bettingTip->is_premium ? 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800' : 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' }} rounded-xl p-3 text-center">
                    <p class="text-[10px] font-bold uppercase tracking-widest {{ $bettingTip->is_premium ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400' }} mb-1">Prediction</p>
                    <p class="text-sm font-black {{ $bettingTip->is_premium ? 'text-yellow-700 dark:text-yellow-300' : 'text-green-700 dark:text-green-300' }}">{{ $bettingTip->prediction }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3 text-center">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1">Odds</p>
                    <p class="text-sm font-black text-gray-900 dark:text-white">{{ $bettingTip->odds ? number_format($bettingTip->odds, 2) : '—' }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3 text-center">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1">Confidence</p>
                    <div class="flex items-center justify-center">
                        @if($bettingTip->confidence)
                            <span class="text-base leading-none text-yellow-400">{{ str_repeat('★', $bettingTip->confidence) }}<span class="text-gray-300 dark:text-gray-600">{{ str_repeat('★', 5 - $bettingTip->confidence) }}</span></span>
                        @else
                            <span class="text-base leading-none text-gray-300 dark:text-gray-600">★★★★★</span>
                        @endif
                    </div>
                </div>
            </div>

{{-- Status --}}
            <div class="flex items-center gap-2 mb-6">
                <span class="text-xs font-semibold text-gray-500">Status:</span>
                @if($bettingTip->status === 'won')
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-green-500 text-white">✓ Won</span>
                    @if($bettingTip->home_score !== null && $bettingTip->away_score !== null)
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-700 text-white">{{ $bettingTip->home_score }} - {{ $bettingTip->away_score }}</span>
                    @endif
                @elseif($bettingTip->status === 'lost')
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-500 text-white">✗ Lost</span>
                    @if($bettingTip->home_score !== null && $bettingTip->away_score !== null)
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-700 text-white">{{ $bettingTip->home_score }} - {{ $bettingTip->away_score }}</span>
                    @endif
                @elseif($bettingTip->display_status === 'live')
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-500/20 text-red-600 dark:text-red-400 border border-red-500/50 animate-pulse">● Live</span>
                @elseif($bettingTip->display_status === 'finished')
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-400 text-white">Finished</span>
                @else
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-400/20 text-yellow-700 dark:text-yellow-300 border border-yellow-400/50 animate-pulse">● Pending</span>
                @endif
            </div>

            {{-- Analysis / Reasoning --}}
            @if($bettingTip->reasoning)
            <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700">

                {{-- Analyst attribution --}}
                @if($bettingTip->analyst)
                @endif

                <p class="text-xs font-bold uppercase tracking-widest text-green-600 dark:text-green-400 mb-3 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    Full Analysis
                </p>
                @php
                    $raw    = $bettingTip->reasoning ?? '';
                    $blocks = preg_split('/\n{2,}/', trim($raw));
                    $sportIcon = match($bettingTip->sport ?? '') {
                        'Football'         => '⚽',
                        'Basketball'       => '🏀',
                        'Baseball'         => '⚾',
                        'Tennis'           => '🎾',
                        'Cricket'          => '🏏',
                        'MMA'              => '🥊',
                        'Hockey'           => '🏒',
                        'Rugby'            => '🏉',
                        'American Football'=> '🏈',
                        default            => '🏅',
                    };
                @endphp
                <div class="space-y-4 text-sm leading-relaxed">
                @foreach($blocks as $block)
                    @php
                        $block  = trim($block);
                        if ($block === '') continue;
                        $lines  = explode("\n", $block);
                        $header = trim($lines[0] ?? '');
                        $body   = array_values(array_filter(array_map('trim', array_slice($lines, 1))));

                        $isMatchResult = (bool) preg_match('/^(MATCH RESULT|MATCH ODDS)/i', $header);
                        $isGoals       = (bool) preg_match('/^GOALS MARKETS/i', $header);
                        $isAnalysis    = (bool) preg_match('/^MARKET ANALYSIS/i', $header);
                        $isOurPick     = (bool) preg_match('/^OUR PICK/i', $header);
                    @endphp

                    @if($isMatchResult)
                        @php
                            $oddsLine = $body[0] ?? '';
                            preg_match_all('/([^|:]+):\s*([\d.]+)\s*\(([\d.]+%)\)/', $oddsLine, $om, PREG_SET_ORDER);
                            $lowestOdds = $om ? min(array_column(array_map(fn($x)=>['o'=>(float)$x[2]],$om),'o')) : null;
                            $gridClass  = count($om) === 3 ? 'grid-cols-3' : 'grid-cols-2';
                        @endphp
                        <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-600 shadow-sm">
                            <div class="flex items-center gap-2 px-3.5 py-2.5 bg-gray-800 dark:bg-gray-900">
                                <span>{{ $sportIcon }}</span>
                                <span class="text-[10px] font-black uppercase tracking-widest text-white">{{ $header }}</span>
                            </div>
                            @if(count($om) >= 2)
                                <div class="grid {{ $gridClass }} divide-x divide-gray-200 dark:divide-gray-600 bg-white dark:bg-gray-800">
                                    @foreach($om as $o)
                                        @php $isFav = (float)$o[2] === $lowestOdds; @endphp
                                        <div class="flex flex-col items-center py-4 px-1 {{ $isFav ? 'bg-green-50 dark:bg-green-900/30' : 'bg-white dark:bg-gray-800' }}">
                                            <span class="text-[10px] text-gray-500 dark:text-gray-300 font-semibold text-center mb-1.5 line-clamp-1 px-1">{{ trim($o[1]) }}</span>
                                            <span class="text-2xl font-black {{ $isFav ? 'text-green-600 dark:text-green-400' : 'text-gray-700 dark:text-gray-100' }}">{{ $o[2] }}</span>
                                            <span class="text-xs mt-1 font-bold {{ $isFav ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-300' }}">{{ $o[3] }}</span>
                                            @if($isFav)
                                                <span class="mt-2 text-[9px] font-black bg-green-500 text-white px-2 py-0.5 rounded-full uppercase tracking-wide">Fav</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="p-3.5 font-mono text-sm font-semibold text-gray-900 dark:text-white bg-white dark:bg-gray-800">{{ $oddsLine }}</p>
                            @endif
                        </div>

                    @elseif($isGoals)
                        <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-600 shadow-sm">
                            <div class="flex items-center gap-2 px-3.5 py-2.5 bg-indigo-700 dark:bg-indigo-800">
                                <span>📊</span>
                                <span class="text-[10px] font-black uppercase tracking-widest text-white">{{ $header }}</span>
                            </div>
                            <div class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($body as $line)
                                    @php
                                        $mOU = $mBTTS = [];
                                        preg_match('/^(.*?):\s+O\s+([\d.]+)\s*(\(\d+%\))?\s*\/\s*U\s+([\d.]+)\s*(\(\d+%\))?/i', $line, $mOU);
                                        preg_match('/^(Both Teams.*?):\s+Yes\s+([\d.]+)\s*(\(\d+%\))?\s*\/\s*No\s+([\d.]+)\s*(\(\d+%\))?/i', $line, $mBTTS);
                                        $overPct = 0;
                                        if (!empty($mOU[3])) { preg_match('/(\d+)/', $mOU[3], $px); $overPct = (int)($px[1] ?? 0); }
                                    @endphp
                                    @if(count($mOU) >= 5)
                                        <div class="px-3.5 py-3">
                                            <div class="flex items-center gap-2">
                                                <span class="w-28 text-xs text-gray-600 dark:text-gray-300 font-semibold flex-shrink-0">{{ trim($mOU[1]) }}</span>
                                                <span class="text-xs font-bold text-gray-900 dark:text-white bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 px-2.5 py-1 rounded-full font-mono shadow-sm">O {{ $mOU[2] }}{{ !empty($mOU[3]) ? ' '.$mOU[3] : '' }}</span>
                                                <span class="text-gray-400 dark:text-gray-500 text-xs font-bold">/</span>
                                                <span class="text-xs font-bold text-white bg-gray-700 dark:bg-gray-900 border border-gray-600 dark:border-gray-700 px-2.5 py-1 rounded-full font-mono shadow-sm">U {{ $mOU[4] }}{{ !empty($mOU[5]) ? ' '.$mOU[5] : '' }}</span>
                                            </div>
                                            @if($overPct > 0)
                                                <div class="mt-2.5 h-2 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-600">
                                                    <div class="h-full rounded-full bg-gray-700 dark:bg-white" style="width:{{ $overPct }}%"></div>
                                                </div>
                                                <div class="flex justify-between mt-0.5">
                                                    <span class="text-[10px] text-gray-600 dark:text-gray-300 font-bold">Over {{ $overPct }}%</span>
                                                    <span class="text-[10px] text-gray-500 dark:text-gray-400 font-bold">Under {{ 100 - $overPct }}%</span>
                                                </div>
                                            @endif
                                        </div>
                                    @elseif(count($mBTTS) >= 5)
                                        @php
                                            $yesPct = 0;
                                            if (!empty($mBTTS[3])) { preg_match('/(\d+)/', $mBTTS[3], $bx); $yesPct = (int)($bx[1] ?? 0); }
                                        @endphp
                                        <div class="px-3.5 py-3">
                                            <div class="flex items-center gap-2">
                                                <span class="w-28 text-xs text-gray-600 dark:text-gray-300 font-semibold flex-shrink-0">{{ trim($mBTTS[1]) }}</span>
                                                <span class="text-xs font-bold text-gray-900 dark:text-white bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 px-2.5 py-1 rounded-full font-mono shadow-sm">Yes {{ $mBTTS[2] }}{{ !empty($mBTTS[3]) ? ' '.$mBTTS[3] : '' }}</span>
                                                <span class="text-gray-400 dark:text-gray-500 text-xs font-bold">/</span>
                                                <span class="text-xs font-bold text-white dark:text-gray-100 bg-gray-700 dark:bg-gray-900 border border-gray-600 dark:border-gray-700 px-2.5 py-1 rounded-full font-mono shadow-sm">No {{ $mBTTS[4] }}{{ !empty($mBTTS[5]) ? ' '.$mBTTS[5] : '' }}</span>
                                            </div>
                                            @if($yesPct > 0)
                                                <div class="mt-2.5 h-2 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-600">
                                                    <div class="h-full rounded-full bg-gray-700 dark:bg-white" style="width:{{ $yesPct }}%"></div>
                                                </div>
                                                <div class="flex justify-between mt-0.5">
                                                    <span class="text-[10px] text-gray-600 dark:text-gray-300 font-bold">Yes {{ $yesPct }}%</span>
                                                    <span class="text-[10px] text-gray-500 dark:text-gray-400 font-bold">No {{ 100 - $yesPct }}%</span>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <p class="px-3.5 py-2 text-xs font-mono text-gray-600 dark:text-gray-300">{{ $line }}</p>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                    @elseif($isAnalysis)
                        <div class="rounded-xl border border-gray-200 dark:border-gray-600 overflow-hidden shadow-sm">
                            <div class="flex items-center gap-2 px-3.5 py-2.5 bg-gray-100 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                                <svg class="w-3.5 h-3.5 text-green-500 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/></svg>
                                <span class="text-[10px] font-black uppercase tracking-widest text-gray-700 dark:text-white">{{ $header }}</span>
                            </div>
                            <div class="bg-white dark:bg-gray-800 px-4 py-4 space-y-3">
                                @foreach($body as $i => $line)
                                    <p class="text-sm text-gray-800 dark:text-gray-100 leading-relaxed {{ $i === 0 ? 'font-semibold' : '' }}">{{ $line }}</p>
                                @endforeach
                            </div>
                        </div>

                    @elseif($isOurPick)
                        @php
                            $pickText = preg_replace('/^OUR PICK:\s*/i', '', $header);
                            preg_match('/^(.+?)\s*@\s*([\d.]+)$/', $pickText, $pp);
                            $pickPred = count($pp) >= 2 ? trim($pp[1]) : $pickText;
                            $pickOdds = $pp[2] ?? null;
                        @endphp
                        <div class="rounded-xl overflow-hidden shadow-sm border border-green-300 dark:border-green-700">
                            <div class="bg-gradient-to-r from-green-600 to-emerald-500 px-4 py-4">
                                <p class="text-[10px] font-black uppercase tracking-widest text-green-100 mb-2">🎯 Our Selection</p>
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-xl font-black text-white leading-tight">{{ $pickPred }}</p>
                                    @if($pickOdds)
                                        <span class="flex-shrink-0 bg-white text-green-700 font-black text-xl px-4 py-1.5 rounded-lg shadow">{{ $pickOdds }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-800 px-4 py-4 space-y-2 border-t border-green-100 dark:border-green-900">
                                @foreach($body as $line)
                                    <p class="text-sm text-gray-700 dark:text-gray-200 leading-relaxed">{{ $line }}</p>
                                @endforeach
                            </div>
                        </div>

                    @else
                        <p class="text-sm text-gray-600 dark:text-gray-200 leading-relaxed">{{ $block }}</p>
                    @endif
                @endforeach
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between gap-3">
                    <p class="text-xs text-gray-400">Want more in-depth breakdowns and betting insights?</p>
                    <a href="{{ route('blog.index') }}"
                       class="inline-flex items-center gap-1.5 text-[11px] font-bold text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-3 py-1.5 rounded-full hover:bg-green-100 dark:hover:bg-green-900/40 transition-colors flex-shrink-0">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 12h6m-6-4h6"/>
                        </svg>
                        Read Our Blog
                    </a>
                </div>
            </div>
            @else
            <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 text-center">
                <p class="text-sm text-gray-400">No analysis available for this tip yet.</p>
            </div>
            @endif

        </div>
    </div>

    @if($bettingTip->is_premium)
    {{-- VIP confirmation banner --}}
    <div class="mt-6 rounded-2xl bg-gradient-to-br from-yellow-400/10 to-orange-400/10 border border-yellow-400/30 p-5 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-yellow-400/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
        </div>
        <div class="flex-1">
            <p class="text-sm font-black text-gray-900 dark:text-white">You're viewing a VIP Premium Tip</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">This exclusive analysis is part of your premium subscription.</p>
        </div>
        <a href="{{ route('premium') }}" class="text-xs font-bold text-yellow-600 dark:text-yellow-400 hover:underline flex-shrink-0">
            Back to tips →
        </a>
    </div>
    @else
    {{-- Free tip — promote premium --}}
    <div class="mt-6 rounded-2xl bg-gradient-to-br from-yellow-400/10 to-orange-400/10 border border-yellow-400/30 overflow-hidden">

        <div class="p-5 text-center border-b border-yellow-400/20">
            <p class="text-xs font-bold uppercase tracking-widest text-yellow-600 dark:text-yellow-400 mb-1">Want More Like This?</p>
            <p class="text-base font-black text-gray-900 dark:text-white">Unlock VIP Premium Tips</p>
        </div>

        @if($premiumToday->count())
        {{-- Combined odds display --}}
        <div class="px-5 py-4 flex items-center justify-between gap-4 border-b border-yellow-400/20">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-0.5">Today's VIP Tips</p>
                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $premiumToday->count() }} {{ Str::plural('tip', $premiumToday->count()) }} available</p>
            </div>
            @if($combinedOdds > 0)
            <div class="text-center bg-yellow-400/10 border border-yellow-400/30 rounded-xl px-4 py-2">
                <p class="text-[10px] font-bold uppercase tracking-widest text-yellow-600 dark:text-yellow-400 mb-0.5">Combined Odds</p>
                <p class="text-2xl font-black text-yellow-500 dark:text-yellow-300">{{ number_format($combinedOdds, 2) }}</p>
            </div>
            @endif
        </div>
        @else
        <p class="text-xs text-center text-gray-400 py-3 px-5">Premium tips for today coming soon.</p>
        @endif

        <div class="p-5 text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Full analysis + expert reasoning on every VIP tip</p>
            <a href="{{ route('premium') }}"
               class="inline-flex items-center gap-2 bg-yellow-400 hover:bg-yellow-300 text-black font-black text-sm px-6 py-3 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                Unlock VIP Access
            </a>
        </div>
    </div>
    @endif

    <p class="text-center text-xs text-gray-400 mt-6">
        18+ only. Betting involves risk — only bet what you can afford to lose.
    </p>

</div>

@endsection
