@extends('layouts.app')
@section('title', $bettingTip->home_team . ' vs ' . $bettingTip->away_team . ' — Tip Analysis')
@section('meta_description', 'Expert analysis for ' . $bettingTip->prediction . ' — ' . $bettingTip->home_team . ' vs ' . $bettingTip->away_team)

@section('content')

{{-- ══════════════════════════════════════════════════
     HERO — always dark (intentional branding)
══════════════════════════════════════════════════ --}}
<div class="relative bg-gray-900 overflow-hidden">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-96 h-48 bg-green-500/5 rounded-full blur-3xl"></div>
    </div>

    <div class="relative max-w-2xl mx-auto px-4 sm:px-6 pt-5 pb-7">

        {{-- Back + badges --}}
        <div class="flex items-center justify-between mb-6">
            <a href="{{ url()->previous() }}" class="inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back
            </a>
            <div class="flex items-center gap-2">
                @if($bettingTip->is_premium)
                <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-widest text-yellow-400 bg-yellow-400/10 border border-yellow-400/25 px-2.5 py-1 rounded-full">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    VIP
                </span>
                @endif
                @if($bettingTip->status === 'won')
                    <span class="text-[10px] font-black px-2.5 py-1 rounded-full bg-green-500/20 text-green-400 border border-green-500/30">✓ Won</span>
                @elseif($bettingTip->status === 'lost')
                    <span class="text-[10px] font-black px-2.5 py-1 rounded-full bg-red-500/20 text-red-400 border border-red-500/30">✗ Lost</span>
                @elseif($bettingTip->display_status === 'live')
                    <span class="text-[10px] font-black px-2.5 py-1 rounded-full bg-red-500/20 text-red-400 border border-red-500/30 animate-pulse">● Live</span>
                @elseif($bettingTip->display_status === 'finished')
                    <span class="text-[10px] font-black px-2.5 py-1 rounded-full bg-gray-600/50 text-gray-300 border border-gray-600/50">Finished</span>
                @else
                    <span class="text-[10px] font-black px-2.5 py-1 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/30 animate-pulse">● Pending</span>
                @endif
            </div>
        </div>

        {{-- League + date --}}
        <div class="flex items-center gap-2 mb-5 flex-wrap">
            @if($bettingTip->league)
            <span class="text-[10px] font-bold uppercase tracking-widest {{ $bettingTip->is_premium ? 'text-yellow-400 bg-yellow-400/10 border border-yellow-400/20' : 'text-green-400 bg-green-400/10 border border-green-400/20' }} px-2.5 py-1 rounded-full">
                {{ $bettingTip->league }}
            </span>
            @endif
            @if($bettingTip->country)
            <span class="text-[10px] text-gray-500 font-semibold">{{ $bettingTip->country }}</span>
            @endif
            <span class="ml-auto text-[10px] font-semibold text-gray-500">
                {{ $bettingTip->match_time->format('d M Y · g:i A') }}
            </span>
        </div>

        {{-- Teams --}}
        <div class="flex items-center gap-4 mb-6">
            <div class="flex-1 min-w-0">
                <p class="text-xl sm:text-2xl font-black text-white leading-snug">{{ $bettingTip->home_team }}</p>
                <p class="text-[10px] text-gray-600 font-bold uppercase tracking-wider mt-1">Home</p>
            </div>
            <div class="flex-shrink-0 text-center px-1">
                @if($bettingTip->home_score !== null && $bettingTip->away_score !== null)
                    <span class="block text-2xl font-black text-white tracking-tight">{{ $bettingTip->home_score }}–{{ $bettingTip->away_score }}</span>
                    <span class="block text-[9px] font-bold uppercase text-gray-600 tracking-widest mt-0.5">FT</span>
                @else
                    <span class="block text-xs font-bold text-gray-500 bg-white/5 border border-white/10 rounded-xl px-3 py-2">VS</span>
                @endif
            </div>
            <div class="flex-1 min-w-0 text-right">
                <p class="text-xl sm:text-2xl font-black text-white leading-snug">{{ $bettingTip->away_team }}</p>
                <p class="text-[10px] text-gray-600 font-bold uppercase tracking-wider mt-1">Away</p>
            </div>
        </div>

        {{-- Our Pick --}}
        <div class="rounded-2xl overflow-hidden border {{ $bettingTip->is_premium ? 'border-yellow-500/25 bg-gradient-to-br from-yellow-500/10 to-orange-500/5' : 'border-green-500/25 bg-gradient-to-br from-green-500/10 to-emerald-500/5' }}">
            <div class="px-4 pt-4 pb-3">
                <p class="text-[9px] font-black uppercase tracking-widest {{ $bettingTip->is_premium ? 'text-yellow-500' : 'text-green-500' }} mb-2">🎯 Our Pick</p>
                <p class="text-lg sm:text-xl font-black text-white leading-tight">{{ $bettingTip->prediction }}</p>
            </div>
            <div class="grid grid-cols-2 border-t {{ $bettingTip->is_premium ? 'border-yellow-500/15' : 'border-green-500/15' }}">
                <div class="px-4 py-3 text-center border-r {{ $bettingTip->is_premium ? 'border-yellow-500/15' : 'border-green-500/15' }}">
                    <p class="text-[9px] font-bold uppercase tracking-widest text-gray-500 mb-1">Odds</p>
                    <p class="text-2xl font-black {{ $bettingTip->is_premium ? 'text-yellow-300' : 'text-white' }}">{{ $bettingTip->odds ? number_format($bettingTip->odds, 2) : '—' }}</p>
                </div>
                <div class="px-4 py-3 text-center">
                    <p class="text-[9px] font-bold uppercase tracking-widest text-gray-500 mb-1.5">Confidence</p>
                    <div class="flex justify-center gap-0.5">
                        @for($i = 1; $i <= 5; $i++)
                        <span class="text-lg leading-none {{ $bettingTip->confidence && $i <= $bettingTip->confidence ? 'text-yellow-400' : 'text-gray-700' }}">★</span>
                        @endfor
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Divider --}}
<div class="h-px bg-gray-200 dark:bg-gray-800"></div>

{{-- ══════════════════════════════════════════════════
     ANALYSIS BLOCKS — light/dark adaptive
══════════════════════════════════════════════════ --}}
@if($bettingTip->reasoning)
@php
    $raw      = $bettingTip->reasoning ?? '';
    $blocks   = preg_split('/\n{2,}/', trim($raw));
    $sportIcon = match($bettingTip->sport ?? '') {
        'Football'          => '⚽',
        'Basketball'        => '🏀',
        'Baseball'          => '⚾',
        'Tennis'            => '🎾',
        'Cricket'           => '🏏',
        'MMA'               => '🥊',
        'Hockey'            => '🏒',
        'Rugby'             => '🏉',
        'American Football' => '🏈',
        default             => '🏅',
    };
@endphp

<div class="max-w-2xl mx-auto divide-y divide-gray-100 dark:divide-gray-800">
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

    {{-- ── MATCH RESULT ── --}}
    @if($isMatchResult)
        @php
            $oddsLine   = $body[0] ?? '';
            preg_match_all('/([^|:]+):\s*([\d.]+)\s*\(([\d.]+%)\)/', $oddsLine, $om, PREG_SET_ORDER);
            $lowestOdds = $om ? min(array_column(array_map(fn($x) => ['o' => (float)$x[2]], $om), 'o')) : null;
            $gridClass  = count($om) === 3 ? 'grid-cols-3' : 'grid-cols-2';
        @endphp
        <div class="bg-white dark:bg-gray-900">
            <div class="flex items-center gap-2 px-4 py-3 bg-gray-800 dark:bg-gray-950">
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-300">{{ $header }}</span>
            </div>
            @if(count($om) >= 2)
            <div class="grid {{ $gridClass }} border-t border-gray-100 dark:border-gray-800">
                @foreach($om as $o)
                    @php $isFav = (float)$o[2] === $lowestOdds; @endphp
                    <div class="flex flex-col items-center py-6 px-3 relative {{ $isFav ? 'bg-green-50 dark:bg-green-900/20' : 'bg-white dark:bg-gray-900' }} {{ !$loop->last ? 'border-r border-gray-100 dark:border-gray-800' : '' }}">
                        @if($isFav)
                        <div class="absolute top-0 inset-x-0 h-0.5 bg-green-500"></div>
                        @endif
                        <span class="text-[11px] text-gray-500 dark:text-gray-400 font-semibold text-center mb-3 line-clamp-1 px-2">{{ trim($o[1]) }}</span>
                        <span class="text-3xl font-black {{ $isFav ? 'text-green-600 dark:text-green-400' : 'text-gray-800 dark:text-white' }} mb-1 tabular-nums">{{ $o[2] }}</span>
                        <span class="text-xs font-bold {{ $isFav ? 'text-green-600 dark:text-green-400' : 'text-gray-400' }}">{{ $o[3] }}</span>
                        @if($isFav)
                            <span class="mt-3 text-[9px] font-black bg-green-500 text-white px-2.5 py-0.5 rounded-full uppercase tracking-wide">Fav</span>
                        @endif
                    </div>
                @endforeach
            </div>
            @else
                <p class="px-4 pb-4 font-mono text-sm text-gray-700 dark:text-gray-300">{{ $oddsLine }}</p>
            @endif
        </div>

    {{-- ── GOALS MARKETS ── --}}
    @elseif($isGoals)
        <div class="bg-white dark:bg-gray-900">
            <div class="flex items-center gap-2 px-4 py-3 bg-indigo-700 dark:bg-indigo-900">
                <span class="text-sm">📊</span>
                <span class="text-[10px] font-black uppercase tracking-widest text-white">{{ $header }}</span>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($body as $line)
                    @php
                        $mOU = $mBTTS = [];
                        preg_match('/^(.*?):\s+O\s+([\d.]+)\s*(\(\d+%\))?\s*\/\s*U\s+([\d.]+)\s*(\(\d+%\))?/i', $line, $mOU);
                        preg_match('/^(Both Teams.*?):\s+Yes\s+([\d.]+)\s*(\(\d+%\))?\s*\/\s*No\s+([\d.]+)\s*(\(\d+%\))?/i', $line, $mBTTS);
                        $overPct = 0;
                        if (!empty($mOU[3])) { preg_match('/(\d+)/', $mOU[3], $px); $overPct = (int)($px[1] ?? 0); }
                    @endphp
                    @if(count($mOU) >= 5)
                        <div class="px-4 py-4">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold flex-shrink-0 min-w-[80px]">{{ trim($mOU[1]) }}</span>
                                <div class="flex items-center gap-2 flex-1">
                                    <span class="text-xs font-bold text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-400/10 border border-green-200 dark:border-green-400/20 px-2.5 py-1 rounded-lg font-mono flex-shrink-0">O {{ $mOU[2] }}{{ !empty($mOU[3]) ? ' '.$mOU[3] : '' }}</span>
                                    <span class="text-gray-400 text-sm font-bold">/</span>
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 px-2.5 py-1 rounded-lg font-mono flex-shrink-0">U {{ $mOU[4] }}{{ !empty($mOU[5]) ? ' '.$mOU[5] : '' }}</span>
                                </div>
                            </div>
                            @if($overPct > 0)
                                <div class="h-1.5 rounded-full bg-gray-200 dark:bg-white/10 overflow-hidden">
                                    <div class="h-full rounded-full bg-green-500" style="width:{{ $overPct }}%"></div>
                                </div>
                                <div class="flex justify-between mt-1">
                                    <span class="text-[10px] text-green-600 dark:text-green-400 font-bold">Over {{ $overPct }}%</span>
                                    <span class="text-[10px] text-gray-400 font-bold">Under {{ 100 - $overPct }}%</span>
                                </div>
                            @endif
                        </div>
                    @elseif(count($mBTTS) >= 5)
                        @php
                            $yesPct = 0;
                            if (!empty($mBTTS[3])) { preg_match('/(\d+)/', $mBTTS[3], $bx); $yesPct = (int)($bx[1] ?? 0); }
                        @endphp
                        <div class="px-4 py-4">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold flex-shrink-0 min-w-[80px]">{{ trim($mBTTS[1]) }}</span>
                                <div class="flex items-center gap-2 flex-1">
                                    <span class="text-xs font-bold text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-400/10 border border-green-200 dark:border-green-400/20 px-2.5 py-1 rounded-lg font-mono flex-shrink-0">Yes {{ $mBTTS[2] }}{{ !empty($mBTTS[3]) ? ' '.$mBTTS[3] : '' }}</span>
                                    <span class="text-gray-400 text-sm font-bold">/</span>
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 px-2.5 py-1 rounded-lg font-mono flex-shrink-0">No {{ $mBTTS[4] }}{{ !empty($mBTTS[5]) ? ' '.$mBTTS[5] : '' }}</span>
                                </div>
                            </div>
                            @if($yesPct > 0)
                                <div class="h-1.5 rounded-full bg-gray-200 dark:bg-white/10 overflow-hidden">
                                    <div class="h-full rounded-full bg-green-500" style="width:{{ $yesPct }}%"></div>
                                </div>
                                <div class="flex justify-between mt-1">
                                    <span class="text-[10px] text-green-600 dark:text-green-400 font-bold">Yes {{ $yesPct }}%</span>
                                    <span class="text-[10px] text-gray-400 font-bold">No {{ 100 - $yesPct }}%</span>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="px-4 py-3 text-xs font-mono text-gray-500 dark:text-gray-400">{{ $line }}</p>
                    @endif
                @endforeach
            </div>
        </div>

    {{-- ── MARKET ANALYSIS ── --}}
    @elseif($isAnalysis)
        <div class="bg-white dark:bg-gray-900 px-4 py-5 max-w-2xl mx-auto space-y-3">
            <p class="text-[10px] font-black uppercase tracking-widest text-blue-600 dark:text-blue-400 flex items-center gap-1.5">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/></svg>
                {{ $header }}
            </p>
            @foreach($body as $i => $line)
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed {{ $i === 0 ? 'font-semibold text-gray-900 dark:text-gray-100' : '' }}">{{ $line }}</p>
            @endforeach
        </div>

    {{-- ── OUR PICK — already shown in hero ── --}}
    @elseif($isOurPick)

    {{-- ── PLAIN TEXT ── --}}
    @else
        @if(trim($block))
        <div class="bg-white dark:bg-gray-900 px-4 py-4 max-w-2xl mx-auto">
            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ $block }}</p>
        </div>
        @endif
    @endif
@endforeach
</div>

{{-- Blog CTA --}}
<div class="max-w-2xl mx-auto px-4 sm:px-6 py-4">
    <div class="flex items-center justify-between gap-3 py-3 px-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <p class="text-xs text-gray-500">Want deeper betting insights?</p>
        <a href="{{ route('blog.index') }}" class="text-[11px] font-bold text-green-600 dark:text-green-400 hover:underline flex-shrink-0">
            Read Our Blog →
        </a>
    </div>
</div>

@else
<div class="max-w-2xl mx-auto px-4 sm:px-6 py-8 text-center">
    <p class="text-sm text-gray-400">No analysis available for this tip yet.</p>
</div>
@endif

{{-- ══════════════════════════════════════════════════
     DATA SECTIONS — light/dark adaptive cards
══════════════════════════════════════════════════ --}}
<div class="max-w-2xl mx-auto px-4 sm:px-6 space-y-3 pb-8">

{{-- Head to Head --}}
@if(!empty($bettingTip->head_to_head))
<div class="rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden bg-white dark:bg-gray-900 shadow-sm">
    <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
        <svg class="w-3.5 h-3.5 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
        <span class="text-[10px] font-black uppercase tracking-widest text-gray-700 dark:text-gray-300">Head to Head</span>
        <span class="ml-auto text-[10px] text-gray-400">Last {{ count($bettingTip->head_to_head) }}</span>
    </div>
    <div class="divide-y divide-gray-50 dark:divide-gray-800">
        @foreach($bettingTip->head_to_head as $match)
        @php
            $gh = $match['home_goals'] ?? null;
            $ga = $match['away_goals'] ?? null;
            $homeWon = $gh !== null && $ga !== null && $gh > $ga;
            $awayWon = $gh !== null && $ga !== null && $ga > $gh;
        @endphp
        <div class="px-4 py-3 flex items-center gap-3 text-xs">
            <span class="text-gray-400 font-semibold w-14 flex-shrink-0 text-[10px]">{{ \Carbon\Carbon::parse($match['date'])->format('M Y') }}</span>
            <div class="flex-1 flex items-center justify-between gap-2 min-w-0">
                <span class="font-semibold {{ $homeWon ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-300' }} truncate">{{ $match['home'] }}</span>
                @if($gh !== null && $ga !== null)
                <span class="flex-shrink-0 font-black text-gray-800 dark:text-white bg-gray-100 dark:bg-gray-800 px-2.5 py-0.5 rounded-lg font-mono text-xs">{{ $gh }}–{{ $ga }}</span>
                @endif
                <span class="font-semibold {{ $awayWon ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-300' }} truncate text-right">{{ $match['away'] }}</span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Key Absences --}}
@if(!empty($bettingTip->injuries))
<div class="rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden bg-white dark:bg-gray-900 shadow-sm">
    <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
        <svg class="w-3.5 h-3.5 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <span class="text-[10px] font-black uppercase tracking-widest text-gray-700 dark:text-gray-300">Key Absences</span>
        <span class="ml-auto text-[10px] text-gray-400">{{ count($bettingTip->injuries) }} player{{ count($bettingTip->injuries) !== 1 ? 's' : '' }}</span>
    </div>
    <div class="divide-y divide-gray-50 dark:divide-gray-800">
        @foreach($bettingTip->injuries as $injury)
        <div class="px-4 py-3 flex items-center gap-3 text-xs">
            <div class="flex-1 min-w-0">
                <p class="font-bold text-gray-900 dark:text-white truncate">{{ $injury['player'] }}</p>
                <p class="text-gray-400 text-[10px] mt-0.5 truncate">{{ $injury['team'] }}</p>
            </div>
            <div class="text-right flex-shrink-0">
                <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full
                    {{ strtolower($injury['type']) === 'suspension' ? 'bg-yellow-100 dark:bg-yellow-400/20 text-yellow-700 dark:text-yellow-400' : 'bg-red-100 dark:bg-red-400/20 text-red-600 dark:text-red-400' }}">
                    {{ $injury['type'] }}
                </span>
                @if(!empty($injury['reason']))
                <p class="text-[10px] text-gray-400 mt-0.5 truncate max-w-[120px]">{{ $injury['reason'] }}</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Team Statistics --}}
@if(!empty($bettingTip->team_stats))
@php $homeTs = $bettingTip->team_stats['home'] ?? null; $awayTs = $bettingTip->team_stats['away'] ?? null; @endphp
@if($homeTs || $awayTs)
<div class="rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden bg-white dark:bg-gray-900 shadow-sm">
    <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
        <svg class="w-3.5 h-3.5 text-purple-500 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zm6-4a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zm6-3a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/></svg>
        <span class="text-[10px] font-black uppercase tracking-widest text-gray-700 dark:text-gray-300">Team Statistics</span>
        <span class="ml-auto text-[10px] text-gray-400">This Season</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <th class="text-left px-4 py-2.5 text-gray-400 font-semibold w-24"></th>
                    <th class="px-4 py-2.5 text-center text-gray-800 dark:text-white font-black text-[11px]">{{ $homeTs['team'] ?? $bettingTip->home_team }}</th>
                    <th class="px-4 py-2.5 text-center text-gray-800 dark:text-white font-black text-[11px]">{{ $awayTs['team'] ?? $bettingTip->away_team }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                @foreach([['Played','played'],['Won','won'],['Drawn','drawn'],['Lost','lost'],['Goals/Game','goals_for'],['Conceded/G','goals_against']] as [$label,$key])
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <td class="px-4 py-2.5 text-gray-500 font-semibold">{{ $label }}</td>
                    <td class="px-4 py-2.5 text-center text-gray-800 dark:text-white font-bold">{{ $homeTs[$key] ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-center text-gray-800 dark:text-white font-bold">{{ $awayTs[$key] ?? '—' }}</td>
                </tr>
                @endforeach
                @if(($homeTs['form'] ?? '') || ($awayTs['form'] ?? ''))
                <tr>
                    <td class="px-4 py-2.5 text-gray-500 font-semibold">Form</td>
                    @foreach([$homeTs, $awayTs] as $ts)
                    <td class="px-4 py-2.5">
                        <div class="flex justify-center gap-0.5">
                            @foreach(str_split(substr($ts['form'] ?? '', -5)) as $r)
                            <span class="w-5 h-5 rounded-full text-[8px] font-black flex items-center justify-center
                                {{ $r === 'W' ? 'bg-green-500 text-white' : ($r === 'D' ? 'bg-gray-400 text-white' : 'bg-red-500 text-white') }}">{{ $r }}</span>
                            @endforeach
                        </div>
                    </td>
                    @endforeach
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endif
@endif

{{-- Starting Lineups --}}
@if(!empty($bettingTip->lineups))
@php $homeL = $bettingTip->lineups['home'] ?? null; $awayL = $bettingTip->lineups['away'] ?? null; @endphp
@if($homeL || $awayL)
<div class="rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden bg-white dark:bg-gray-900 shadow-sm">
    <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
        <svg class="w-3.5 h-3.5 text-green-500 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
        <span class="text-[10px] font-black uppercase tracking-widest text-gray-700 dark:text-gray-300">Starting Lineups</span>
    </div>
    <div class="grid grid-cols-2 divide-x divide-gray-100 dark:divide-gray-700">
        @foreach(['home' => $homeL, 'away' => $awayL] as $side => $lineup)
        @if($lineup)
        <div class="p-4">
            <p class="text-xs font-black text-gray-900 dark:text-white truncate mb-0.5">{{ $lineup['team'] }}</p>
            <p class="text-[10px] text-green-600 dark:text-green-400 font-bold mb-3">{{ $lineup['formation'] }}</p>
            @foreach($lineup['xi'] ?? [] as $player)
            <p class="text-[11px] text-gray-600 dark:text-gray-400 py-0.5 truncate">{{ $player }}</p>
            @endforeach
        </div>
        @endif
        @endforeach
    </div>
</div>
@endif
@endif

{{-- Match Statistics --}}
@if(!empty($bettingTip->match_stats))
@php $homeMs = $bettingTip->match_stats['home'] ?? null; $awayMs = $bettingTip->match_stats['away'] ?? null; @endphp
@if($homeMs || $awayMs)
<div class="rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden bg-white dark:bg-gray-900 shadow-sm">
    <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
        <svg class="w-3.5 h-3.5 text-purple-500 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
        <span class="text-[10px] font-black uppercase tracking-widest text-gray-700 dark:text-gray-300">Match Statistics</span>
        <span class="ml-auto text-[10px] text-gray-400">Full-Time</span>
    </div>
    @if(($homeMs['possession'] ?? null) || ($awayMs['possession'] ?? null))
    @php $homePoss = (int) trim($homeMs['possession'] ?? '0', '%'); $awayPoss = (int) trim($awayMs['possession'] ?? '0', '%'); @endphp
    <div class="px-4 py-4 border-b border-gray-100 dark:border-gray-800">
        <div class="flex justify-between text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
            <span>{{ $homePoss }}%</span>
            <span class="text-[10px] text-gray-400 font-semibold">Possession</span>
            <span>{{ $awayPoss }}%</span>
        </div>
        <div class="h-2 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
            <div class="h-full bg-green-500 rounded-full" style="width: {{ $homePoss }}%"></div>
        </div>
    </div>
    @endif
    <div class="divide-y divide-gray-50 dark:divide-gray-800">
    @foreach([['Shots','shots'],['On Target','shots_on_target'],['Corners','corners'],['Fouls','fouls'],['Yellow Cards','yellow_cards'],['Red Cards','red_cards']] as [$label,$key])
    @if(($homeMs[$key] ?? null) !== null || ($awayMs[$key] ?? null) !== null)
    <div class="flex items-center px-4 py-3">
        <span class="text-base font-black text-gray-800 dark:text-white w-10 text-left tabular-nums">{{ $homeMs[$key] ?? '—' }}</span>
        <span class="flex-1 text-center text-[10px] font-semibold text-gray-400">{{ $label }}</span>
        <span class="text-base font-black text-gray-800 dark:text-white w-10 text-right tabular-nums">{{ $awayMs[$key] ?? '—' }}</span>
    </div>
    @endif
    @endforeach
    </div>
</div>
@endif
@endif

{{-- Match Timeline --}}
@if(!empty($bettingTip->match_events))
<div class="rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden bg-white dark:bg-gray-900 shadow-sm">
    <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
        <svg class="w-3.5 h-3.5 text-orange-500 dark:text-orange-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
        <span class="text-[10px] font-black uppercase tracking-widest text-gray-700 dark:text-gray-300">Match Timeline</span>
        <span class="ml-auto text-[10px] text-gray-400">{{ count(array_filter($bettingTip->match_events, fn($e) => $e['type'] === 'Goal')) }} goals</span>
    </div>
    <div class="divide-y divide-gray-50 dark:divide-gray-800">
        @foreach($bettingTip->match_events as $event)
        @php
            $isHome = strtolower($event['team'] ?? '') === strtolower($bettingTip->home_team);
            $type   = $event['type'] ?? '';
            $detail = $event['detail'] ?? '';
        @endphp
        @if(in_array($type, ['Goal', 'Card']))
        <div class="flex items-center gap-3 px-4 py-3 {{ $isHome ? '' : 'flex-row-reverse' }}">
            <span class="text-[10px] font-black text-gray-400 w-8 flex-shrink-0 tabular-nums {{ $isHome ? 'text-left' : 'text-right' }}">{{ $event['minute'] }}'</span>
            <div class="flex items-center gap-2 flex-1 {{ $isHome ? '' : 'flex-row-reverse' }}">
                @if($type === 'Goal')
                    <span class="text-base flex-shrink-0">⚽</span>
                    <div class="{{ $isHome ? 'text-left' : 'text-right' }}">
                        <p class="text-xs font-bold text-gray-900 dark:text-white">{{ $event['player'] }}</p>
                        @if(!empty($event['assist']))
                        <p class="text-[10px] text-gray-400">Assist: {{ $event['assist'] }}</p>
                        @endif
                    </div>
                @elseif($type === 'Card')
                    <span class="inline-block w-3 h-4 rounded-sm flex-shrink-0 {{ str_contains($detail, 'Red') ? 'bg-red-500' : 'bg-yellow-400' }}"></span>
                    <p class="text-xs font-bold text-gray-900 dark:text-white">{{ $event['player'] }}</p>
                @endif
            </div>
            <span class="text-[10px] text-gray-400 w-16 flex-shrink-0 {{ $isHome ? 'text-right' : 'text-left' }} truncate">{{ $event['team'] }}</span>
        </div>
        @endif
        @endforeach
    </div>
</div>
@endif

{{-- Player Ratings --}}
@if(!empty($bettingTip->player_ratings))
@php $homeR = $bettingTip->player_ratings['home'] ?? null; $awayR = $bettingTip->player_ratings['away'] ?? null; @endphp
@if($homeR || $awayR)
<div class="rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden bg-white dark:bg-gray-900 shadow-sm">
    <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
        <svg class="w-3.5 h-3.5 text-yellow-500 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
        <span class="text-[10px] font-black uppercase tracking-widest text-gray-700 dark:text-gray-300">Player Ratings</span>
    </div>
    <div class="grid grid-cols-2 divide-x divide-gray-100 dark:divide-gray-700">
        @foreach(['home' => $homeR, 'away' => $awayR] as $side => $teamR)
        @if($teamR)
        <div class="p-3">
            <p class="text-[10px] font-black text-gray-800 dark:text-gray-200 truncate mb-2.5">{{ $teamR['team'] }}</p>
            @foreach(array_slice($teamR['players'] ?? [], 0, 7) as $p)
            @php $r = floatval($p['rating']); @endphp
            <div class="flex items-center gap-1.5 py-0.5">
                <span class="text-[10px] font-black px-1.5 py-0.5 rounded min-w-[34px] text-center tabular-nums
                    {{ $r >= 8 ? 'bg-green-500 text-white' : ($r >= 7 ? 'bg-green-100 dark:bg-green-400/20 text-green-700 dark:text-green-400' : ($r >= 6.5 ? 'bg-yellow-100 dark:bg-yellow-400/20 text-yellow-700 dark:text-yellow-400' : 'bg-red-100 dark:bg-red-400/20 text-red-600 dark:text-red-400')) }}">
                    {{ number_format($r, 1) }}
                </span>
                <p class="text-[10px] text-gray-600 dark:text-gray-400 truncate flex-1">{{ $p['name'] }}</p>
                @if(($p['goals'] ?? 0) > 0)<span class="text-[9px]">⚽</span>@endif
                @if(($p['assists'] ?? 0) > 0)<span class="text-[9px]">🅰</span>@endif
            </div>
            @endforeach
        </div>
        @endif
        @endforeach
    </div>
</div>
@endif
@endif

{{-- Premium / Upgrade Banner --}}
@if($bettingTip->is_premium)
<div class="rounded-2xl bg-gradient-to-br from-yellow-400/10 to-orange-400/10 border border-yellow-400/30 p-5 flex items-center gap-4">
    <div class="w-10 h-10 rounded-xl bg-yellow-400/20 flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
    </div>
    <div class="flex-1 min-w-0">
        <p class="text-sm font-black text-gray-900 dark:text-white">You're viewing a VIP Premium Tip</p>
        <p class="text-xs text-gray-500 mt-0.5">Exclusive analysis as part of your premium subscription.</p>
    </div>
    <a href="{{ route('premium') }}" class="text-xs font-bold text-yellow-600 dark:text-yellow-400 hover:underline flex-shrink-0">Back →</a>
</div>
@else
<div class="rounded-2xl bg-gradient-to-br from-yellow-400/10 to-orange-400/10 border border-yellow-400/30 overflow-hidden">
    <div class="px-5 py-4 text-center border-b border-yellow-400/20">
        <p class="text-[10px] font-black uppercase tracking-widest text-yellow-600 dark:text-yellow-400 mb-1">Want More Like This?</p>
        <p class="text-base font-black text-gray-900 dark:text-white">Unlock VIP Premium Tips</p>
    </div>
    @if($premiumToday->count())
    <div class="px-5 py-4 flex items-center justify-between gap-4 border-b border-yellow-400/20">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-0.5">Today's VIP Tips</p>
            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $premiumToday->count() }} {{ Str::plural('tip', $premiumToday->count()) }} available</p>
        </div>
        @if($combinedOdds > 0)
        <div class="text-center bg-yellow-400/10 border border-yellow-400/30 rounded-xl px-4 py-2 flex-shrink-0">
            <p class="text-[9px] font-bold uppercase tracking-widest text-yellow-600 dark:text-yellow-400 mb-0.5">Combined Odds</p>
            <p class="text-2xl font-black text-yellow-600 dark:text-yellow-300 tabular-nums">{{ number_format($combinedOdds, 2) }}</p>
        </div>
        @endif
    </div>
    @else
    <p class="text-xs text-center text-gray-400 py-3 px-5">Premium tips for today coming soon.</p>
    @endif
    <div class="p-5 text-center">
        <p class="text-xs text-gray-500 mb-4">Full analysis + expert reasoning on every VIP tip</p>
        <a href="{{ route('premium') }}" class="inline-flex items-center gap-2 bg-yellow-400 hover:bg-yellow-300 text-black font-black text-sm px-7 py-3 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            Unlock VIP Access
        </a>
    </div>
</div>
@endif

<p class="text-center text-[10px] text-gray-400 pb-2">
    18+ only · Gambling involves risk · Only bet what you can afford to lose
</p>

</div>{{-- end data sections --}}

@endsection
