@extends('layouts.app')
@section('title', 'Premium Football Tips — BallSignals')
@section('meta_description', 'Unlock VIP football betting tips from BallSignals. High-confidence predictions, premium accumulator picks, and daily expert signals — starting from $10/week.')
@section('canonical', route('premium'))


@section('content')

@if(session('vip_activated'))
<div class="max-w-4xl mx-auto px-4 sm:px-6 pt-6">
    <div class="flex items-start gap-3 px-5 py-4 rounded-2xl border border-green-400/40 bg-green-500/10 mb-4">
        <span class="text-2xl">🎉</span>
        <div>
            <p class="font-black text-green-400 text-sm">Payment confirmed — welcome to VIP!</p>
            <p class="text-xs text-green-300/70 mt-0.5">Your premium access is now active. Enjoy your tips below.</p>
        </div>
    </div>
</div>
@endif

@if($subscription || $isAdmin)

{{-- ====== VIP ACCESS VIEW ====== --}}
<div class="bg-[#0a0f1a] border-b border-white/5">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                @if($isAdmin && !$subscription)
                <div class="inline-flex items-center gap-1.5 bg-blue-400/10 border border-blue-400/30 text-blue-400 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-3">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></span>
                    Admin Preview
                </div>
                @else
                <div class="inline-flex items-center gap-1.5 bg-yellow-400/10 border border-yellow-400/30 text-yellow-400 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-3">
                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 animate-pulse"></span>
                    VIP Access — {{ $subscription->plan_label }}
                </div>
                @endif
                <h1 class="text-2xl sm:text-3xl font-black text-white leading-tight">
                    Upcoming <span class="text-yellow-400">Premium Tips</span>
                </h1>
                @if($subscription)
                <p class="text-gray-400 text-sm mt-1">
                    Signed in as <span class="text-white font-semibold">{{ $subscription->email }}</span>
                    &nbsp;·&nbsp; Expires {{ $subscription->expires_at->format('d M Y') }}
                    @if($subscription->expires_at->diffInDays(now()) <= 3)
                        <span class="text-red-400 font-semibold">(expiring soon)</span>
                    @endif
                </p>
                @endif
            </div>
            @if($subscription)
            <form method="POST" action="{{ route('premium.revoke') }}">
                @csrf
                <button type="submit"
                        class="px-4 py-2 text-xs font-semibold text-gray-400 border border-gray-700 rounded-xl hover:text-white hover:border-gray-500 transition-colors">
                    Sign Out
                </button>
            </form>
            @endif
        </div>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">

    @if($vipTips->isEmpty())
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-16 text-center">
            <div class="text-5xl mb-4">⚽</div>
            <p class="font-black text-gray-900 dark:text-white text-lg mb-2">No VIP tips posted yet today</p>
            <p class="text-sm text-gray-400">Check back soon — tips are usually posted by 8 AM daily.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($vipTips as $tip)
            <a href="{{ route('tips.show', $tip) }}" class="block rounded-2xl border-2 overflow-hidden shadow-lg transition-all {{ $tip->display_status === 'won' ? 'border-green-400 dark:border-green-700/60 bg-green-50/40 dark:bg-green-900/10 shadow-green-400/5' : ($tip->display_status === 'lost' ? 'border-red-400 dark:border-red-700/60 bg-red-50/40 dark:bg-red-900/10 shadow-red-400/5' : 'border-yellow-400/40 bg-white dark:bg-gray-900 shadow-yellow-400/5 hover:border-yellow-400/70') }}">
                <div class="h-1 bg-gradient-to-r from-yellow-400 to-orange-400"></div>
                <div class="p-5">

                    {{-- Top row: league, time, status --}}
                    <div class="flex items-center justify-between gap-3 mb-4 flex-wrap">
                        <div class="flex items-center gap-2 flex-wrap">
                            @if($tip->league)
                                <span class="text-xs font-bold text-yellow-600 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 px-2.5 py-1 rounded-full">
                                    {{ $tip->league }}
                                </span>
                            @endif
                            @if($tip->country)
                                <span class="text-xs text-gray-400">{{ $tip->country }}</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2.5 py-1 rounded-full">
                                {{ $tip->match_time->format('d M, g:i A') }}
                            </span>
                            @if($tip->display_status === 'live')
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-500/20 text-red-600 dark:text-red-400 border border-red-500/50 animate-pulse">● Live</span>
                            @elseif($tip->display_status === 'finished')
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-400 text-white">Finished</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $tip->display_status_badge }}">{{ ucfirst($tip->display_status) }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Teams --}}
                    <div class="flex items-center justify-between gap-4 mb-4">
                        <p class="text-base sm:text-lg font-black text-gray-900 dark:text-white flex-1">{{ $tip->home_team }}</p>
                        <span class="text-xs font-bold text-gray-400 bg-gray-100 dark:bg-gray-800 px-3 py-1.5 rounded-full flex-shrink-0">VS</span>
                        <p class="text-base sm:text-lg font-black text-gray-900 dark:text-white flex-1 text-right">{{ $tip->away_team }}</p>
                    </div>

                    {{-- Prediction / Odds / Confidence --}}
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-3 text-center">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-yellow-600 dark:text-yellow-400 mb-1">Prediction</p>
                            <p class="text-sm font-black text-yellow-700 dark:text-yellow-300">{{ $tip->prediction }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3 text-center">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1">Odds</p>
                            <p class="text-sm font-black text-gray-900 dark:text-white">{{ $tip->odds ? number_format($tip->odds, 2) : '—' }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3 text-center">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1">Confidence</p>
                            <div class="flex items-center justify-center gap-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-3 h-3 {{ $i <= $tip->confidence ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                        </div>
                    </div>

                    {{-- Analysis preview --}}
                    @if($tip->reasoning)
                    <div class="mt-3 px-3 py-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-700/50">
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">{{ Str::limit($tip->reasoning, 160) }}</p>
                    </div>
                    @endif

                    {{-- View analysis hint --}}
                    <div class="mt-3 pt-3 border-t border-yellow-200 dark:border-yellow-800/40 flex items-center justify-between">
                        @if($tip->analyst)
                        <div class="flex items-center gap-1.5 text-xs text-gray-400">
                            <div class="w-5 h-5 rounded-full bg-yellow-100 dark:bg-yellow-900/40 flex items-center justify-center flex-shrink-0">
                                <span class="text-[10px] font-black text-yellow-700 dark:text-yellow-400">{{ strtoupper($tip->analyst[0]) }}</span>
                            </div>
                            {{ $tip->analyst }}
                        </div>
                        @else
                        <span></span>
                        @endif
                        <span class="text-xs font-bold text-yellow-600 dark:text-yellow-400 flex items-center gap-1">
                            View full analysis
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </div>

                </div>
            </a>
            @endforeach
        </div>
    @endif

    <p class="text-center text-xs text-gray-500 dark:text-gray-600 mt-8 leading-relaxed">
        18+ only. Betting involves risk — only bet what you can afford to lose. Past performance does not guarantee future results.
    </p>

</div>

@else

{{-- ====== PUBLIC / SUBSCRIBE VIEW ====== --}}

{{-- Already subscribed? --}}
<div class="bg-[#0a0f1a] border-b border-yellow-400/20">
    <div class="max-w-xl mx-auto px-4 sm:px-6 py-5">
        <p class="text-xs font-bold uppercase tracking-widest text-yellow-400 mb-2 text-center">Already a Premium Member?</p>
        <form method="POST" action="{{ route('premium.access') }}" class="flex gap-2">
            @csrf
            <input type="email" name="email" value="{{ old('email') }}" required placeholder="Enter your email to access VIP tips"
                   class="flex-1 px-4 py-2.5 text-sm rounded-xl border
                          {{ $errors->has('email') ? 'border-red-500 bg-red-900/10' : 'border-white/10 bg-white/5' }}
                          text-white placeholder-gray-500
                          focus:outline-none focus:ring-2 focus:ring-yellow-400/50 focus:border-yellow-400 transition-colors">
            <button type="submit"
                    class="px-5 py-2.5 bg-yellow-400 hover:bg-yellow-300 text-black text-sm font-black rounded-xl transition-colors whitespace-nowrap">
                View Tips →
            </button>
        </form>
        @error('email')
            <p class="text-xs text-red-400 mt-2 text-center">{{ $message }}</p>
        @enderror
    </div>
</div>

{{-- Hero --}}
<div class="bg-[#0a0f1a] border-b border-white/5">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-4 text-center">
        <div class="inline-flex items-center gap-1.5 bg-green-500/10 border border-green-500/30 text-green-400 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-2">
            <span class="w-1 h-1 rounded-full bg-green-400 animate-pulse"></span>
            Premium Membership
        </div>
        <h1 class="text-lg sm:text-xl font-black text-white mb-2 leading-tight">
            Daily Sure Odds <span class="text-green-400">2.00+</span> — Straight to You
        </h1>
        <p class="text-gray-400 text-xs max-w-md mx-auto mb-3 leading-relaxed">
            Hand-picked <span class="text-white font-semibold">1 high-confidence tip/day</span> with odds 2.00+ delivered to your Telegram before kick-off. No guesswork, no filler.
        </p>
        <div class="flex flex-wrap items-center justify-center gap-2">
            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-green-400 bg-green-500/10 border border-green-500/20 px-2.5 py-0.5 rounded-full">
                <span class="w-1 h-1 rounded-full bg-green-400"></span>No hidden fees
            </span>
            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-green-400 bg-green-500/10 border border-green-500/20 px-2.5 py-0.5 rounded-full">
                <span class="w-1 h-1 rounded-full bg-green-400"></span>Instant access
            </span>
            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-green-400 bg-green-500/10 border border-green-500/20 px-2.5 py-0.5 rounded-full">
                <span class="w-1 h-1 rounded-full bg-green-400"></span>Cancel anytime
            </span>
        </div>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 pt-8 pb-4">

    {{-- Pricing cards --}}
    <div class="mb-14">
        <div class="text-center mb-6 max-w-xl mx-auto">
            <p class="text-[10px] font-bold uppercase tracking-widest text-green-400 mb-2">Trusted by hundreds of members</p>
            <h2 class="text-xl font-black text-gray-900 dark:text-white mb-1">Choose Your Plan</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">

            {{-- Weekly --}}
            <div class="relative bg-white dark:bg-gray-900 rounded-2xl border-2 border-gray-200 dark:border-gray-800 p-7 flex flex-col hover:border-green-400/50 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-green-500/5">
                <div class="mb-6">
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-500 mb-3">Weekly</p>
                    <div class="flex items-end gap-1.5 mb-2">
                        <span class="text-4xl font-black text-gray-900 dark:text-white">$10</span>
                        <span class="text-sm text-gray-400 mb-1.5">/ week</span>
                    </div>
                    <p class="text-xs text-gray-400">Try premium risk-free for one week.</p>
                </div>
                @php $weeklyFeatures = ['All daily premium tips','Early tip alerts via email','Match analysis reports','1 accumulator per week']; @endphp
                <ul class="space-y-2.5 mb-8 flex-1">
                    @foreach($weeklyFeatures as $feature)
                    <li class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        {{ $feature }}
                    </li>
                    @endforeach
                </ul>
                <button type="button" onclick="openPlanModal('weekly','1 Week Plan — $10')"
                   class="w-full text-center block py-3 rounded-xl text-sm font-bold text-green-700 dark:text-green-400 border-2 border-green-500/40 hover:bg-green-500/10 transition-all duration-200">
                    Subscribe — $10/week
                </button>
            </div>

            {{-- Monthly (highlighted) --}}
            <div class="relative bg-[#0a0f1a] rounded-2xl border-2 border-green-500 p-7 flex flex-col shadow-2xl shadow-green-500/10 hover:-translate-y-0.5 transition-all duration-200">
                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2">
                    <span class="bg-green-500 text-black text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-lg">Most Popular</span>
                </div>
                <div class="mb-6">
                    <p class="text-xs font-bold uppercase tracking-widest text-green-400 mb-3">Monthly</p>
                    <div class="flex items-end gap-1.5 mb-2">
                        <span class="text-4xl font-black text-white">$20</span>
                        <span class="text-sm text-gray-400 mb-1.5">/ month</span>
                    </div>
                    <p class="text-xs text-green-400/70">Save 50% vs weekly — best value.</p>
                </div>
                @php $monthlyFeatures = ['Everything in Weekly','Priority tip delivery','Daily accumulator slips','Odds value alerts','Private WhatsApp group access','Monthly performance report']; @endphp
                <ul class="space-y-2.5 mb-8 flex-1">
                    @foreach($monthlyFeatures as $feature)
                    <li class="flex items-center gap-2 text-sm text-gray-300">
                        <svg class="w-4 h-4 text-green-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        {{ $feature }}
                    </li>
                    @endforeach
                </ul>
                <button type="button" onclick="openPlanModal('monthly','1 Month Plan — $20')"
                   class="w-full text-center block py-3 rounded-xl text-sm font-black text-black bg-green-400 hover:bg-green-300 shadow-lg shadow-green-500/30 transition-all duration-200">
                    Subscribe — $20/month
                </button>
            </div>

            {{-- 2-Month --}}
            <div class="relative bg-white dark:bg-gray-900 rounded-2xl border-2 border-yellow-400/60 dark:border-yellow-500/40 p-7 flex flex-col hover:-translate-y-0.5 hover:shadow-xl hover:shadow-yellow-500/10 transition-all duration-200">
                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2">
                    <span class="bg-yellow-400 text-black text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-lg whitespace-nowrap">Best Deal</span>
                </div>
                <div class="mb-6">
                    <p class="text-xs font-bold uppercase tracking-widest text-yellow-600 dark:text-yellow-400 mb-3">2 Months</p>
                    <div class="flex items-end gap-1.5 mb-2">
                        <span class="text-4xl font-black text-gray-900 dark:text-white">$30</span>
                        <span class="text-sm text-gray-400 mb-1.5">/ 2 months</span>
                    </div>
                    <p class="text-xs text-yellow-600 dark:text-yellow-400/80">Save 25% vs monthly — lowest price.</p>
                </div>
                @php $twoMonthFeatures = ['Everything in Monthly','Priority tip delivery','Daily accumulator slips','Odds value alerts','Expert Q&A access','Dedicated support','2x monthly performance reports']; @endphp
                <ul class="space-y-2.5 mb-8 flex-1">
                    @foreach($twoMonthFeatures as $feature)
                    <li class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-4 h-4 text-yellow-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        {{ $feature }}
                    </li>
                    @endforeach
                </ul>
                <button type="button" onclick="openPlanModal('two_months','2 Month Plan — $30')"
                   class="w-full text-center block py-3 rounded-xl text-sm font-black text-black bg-yellow-400 hover:bg-yellow-300 shadow-lg shadow-yellow-500/20 transition-all duration-200">
                    Subscribe — $30/2 months
                </button>
            </div>

        </div>

        {{-- Other Payment Options --}}
        <div class="mt-6 mb-2">
            <p class="text-center text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-3">More Payment Options</p>
            <div class="grid grid-cols-2 gap-3 max-w-lg mx-auto">

                {{-- Eversend --}}
                <div class="flex items-center gap-3 px-4 py-3 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-100 dark:bg-white/5">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#6C47FF;">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="white">
                            <circle cx="12" cy="12" r="10" fill="none" stroke="white" stroke-width="2"/>
                            <path d="M7 12h10M7 8h10M7 16h7" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-black text-gray-900 dark:text-white">Eversend</p>
                        <p class="text-[10px] text-gray-500">Send to <span class="font-bold text-gray-700 dark:text-gray-300">+256763920010</span></p>
                    </div>
                </div>

                {{-- Binance --}}
                <div class="flex items-center gap-3 px-4 py-3 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-100 dark:bg-white/5">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#F3BA2F;">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="black">
                            <path d="M12 2l2.4 2.4-2.4 2.4-2.4-2.4L12 2z"/>
                            <path d="M4.8 9.6l2.4 2.4-2.4 2.4-2.4-2.4 2.4-2.4z"/>
                            <path d="M12 9.6l2.4 2.4-2.4 2.4-2.4-2.4 2.4-2.4z"/>
                            <path d="M19.2 9.6l2.4 2.4-2.4 2.4-2.4-2.4 2.4-2.4z"/>
                            <path d="M12 17.2l2.4 2.4-2.4 2.4-2.4-2.4 2.4-2.4z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-black text-gray-900 dark:text-white">Binance Pay</p>
                        <p class="text-[10px] text-gray-500">Pay ID: <span class="font-bold text-gray-700 dark:text-gray-300">1190652548</span></p>
                    </div>
                </div>

            </div>
            <p class="text-center text-[10px] text-gray-400 mt-2">After paying via Eversend or Binance, <a href="https://wa.me/256763920010" class="text-green-500 font-bold hover:underline">WhatsApp us</a> with your receipt for instant activation.</p>
        </div>

        {{-- Pesapal Trust Banner --}}
        <div class="mt-4 flex flex-col items-center gap-3">
            <div class="flex items-center gap-3 px-5 py-3 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-100 dark:bg-white/5 w-full max-w-lg">
                <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-green-500/20 border border-green-500/30 flex-shrink-0">
                    <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-black text-gray-900 dark:text-white">Payments powered by <span class="text-green-500">PesaPal</span></p>
                    <p class="text-[10px] mt-0.5 text-gray-600 dark:text-gray-400">A trusted, secure payment gateway used across Africa & worldwide — supporting cards, mobile money & bank transfers.</p>
                </div>
            </div>
            <div class="flex items-center gap-4 flex-wrap justify-center">
                <div class="flex items-center gap-1.5 text-[10px] text-gray-500">
                    <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    256-bit SSL Encryption
                </div>
                <div class="flex items-center gap-1.5 text-[10px] text-gray-500">
                    <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Card & Mobile Money
                </div>
                <div class="flex items-center gap-1.5 text-[10px] text-gray-500">
                    <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Instant Access After Payment
                </div>
                <div class="flex items-center gap-1.5 text-[10px] text-gray-500">
                    <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Trusted by 1M+ Users
                </div>
            </div>
        </div>

    </div>

    {{-- How to subscribe --}}
    <div class="border border-gray-200 dark:border-gray-800 rounded-xl px-5 py-4 mb-10">
        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 text-center mb-3">How to Subscribe</p>
        <div class="flex items-center justify-center gap-2 sm:gap-6 flex-wrap">
            @foreach([['1','Choose a Plan'],['2','Pay Securely'],['3','Instant Access']] as [$n,$label])
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-full bg-green-500/15 border border-green-500/30 flex items-center justify-center flex-shrink-0">
                    <span class="text-[10px] font-black text-green-500">{{ $n }}</span>
                </div>
                <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ $label }}</span>
            </div>
            @if($n < 3)
            <svg class="w-3 h-3 text-gray-300 dark:text-gray-700 hidden sm:block flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
            @endif
            @endforeach
        </div>
    </div>

    {{-- Testimonials --}}
    <div class="mt-14 mb-4">
        <p class="text-center text-xs font-bold uppercase tracking-widest text-gray-500 dark:text-gray-600 mb-8">What Our Members Say</p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">

            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 flex flex-col gap-4">
                <div class="flex items-center gap-1">
                    @for($i = 0; $i < 5; $i++)
                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed flex-1">"Joined the monthly plan and made back the subscription cost on the very first weekend. The accumulator tips alone are worth it — 3 wins from 4 last month."</p>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-green-500 flex items-center justify-center flex-shrink-0"><span class="text-sm font-black text-white">K</span></div>
                    <div>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">Kevin O.</p>
                        <p class="text-xs text-gray-400">Monthly Member · Uganda</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-green-400/40 dark:border-green-500/30 p-6 flex flex-col gap-4 relative">
                <div class="absolute -top-3 left-5"><span class="bg-green-500 text-white text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-full">Top Review</span></div>
                <div class="flex items-center gap-1 mt-1">
                    @for($i = 0; $i < 5; $i++)
                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed flex-1">"I was skeptical at first but the free tips were already good. Upgraded to VIP and the difference is night and day — detailed analysis, not just random picks."</p>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-blue-500 flex items-center justify-center flex-shrink-0"><span class="text-sm font-black text-white">A</span></div>
                    <div>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">Amara S.</p>
                        <p class="text-xs text-gray-400">2-Month Member · Kenya</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 flex flex-col gap-4">
                <div class="flex items-center gap-1">
                    @for($i = 0; $i < 5; $i++)
                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed flex-1">"The Telegram alerts are a game changer. Tips come in before odds shift — I've been able to grab the best lines every time. Renewing every month without question."</p>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-purple-500 flex items-center justify-center flex-shrink-0"><span class="text-sm font-black text-white">J</span></div>
                    <div>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">James M.</p>
                        <p class="text-xs text-gray-400">Weekly Member · Tanzania</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <p class="text-center text-xs text-gray-500 dark:text-gray-600 mt-4 leading-relaxed">
        18+ only. Betting involves risk — only bet what you can afford to lose. Past performance does not guarantee future results. Please gamble responsibly.
    </p>

    {{-- WhatsApp Support --}}
    <div class="flex justify-center mt-6 mb-2">
        <a href="https://wa.me/256763920010" target="_blank" rel="noopener noreferrer"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-white transition-all duration-200 hover:opacity-90 shadow-md"
           style="background:#25D366;">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
            Chat with Support on WhatsApp
        </a>
    </div>

</div>

@endif

{{-- ── Subscribe Modal ── --}}
<div id="plan-modal"
     class="fixed inset-0 z-50 flex items-center justify-center p-5 hidden"
     style="background:rgba(0,0,0,0.82);backdrop-filter:blur(6px);"
     onclick="if(event.target===this) closePlanModal()">

    <div class="relative w-full max-w-sm rounded-2xl p-7" style="background:#1c2333;">

        <button type="button" onclick="closePlanModal()"
                class="absolute top-4 right-4 w-7 h-7 flex items-center justify-center rounded-full text-gray-400 hover:text-white transition-colors"
                style="background:rgba(255,255,255,0.08);">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <h2 class="text-2xl font-black text-white mb-1">Subscribe to Premium</h2>
        <p id="modal-plan-label" class="text-base font-bold mb-6" style="color:#4ade80;">Monthly — $20</p>

        <form method="POST" action="{{ route('premium.request') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="plan" id="modal-plan-input" value="monthly">
            <input type="hidden" name="payment_method" value="mobile_money">

            <div>
                <label class="block text-sm font-semibold text-gray-200 mb-2">Full Name *</label>
                <input type="text" name="name" required placeholder="John Doe"
                       class="w-full px-4 py-3.5 rounded-xl text-white placeholder-gray-500 outline-none text-sm transition-all"
                       style="background:#111827;border:1.5px solid transparent;"
                       onfocus="this.style.borderColor='#4ade80'" onblur="this.style.borderColor='transparent'">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-200 mb-2">Email Address *</label>
                <input type="email" name="email" required placeholder="your@email.com"
                       class="w-full px-4 py-3.5 rounded-xl text-white placeholder-gray-500 outline-none text-sm transition-all"
                       style="background:#111827;border:1.5px solid transparent;"
                       onfocus="this.style.borderColor='#4ade80'" onblur="this.style.borderColor='transparent'">
            </div>

            <button type="submit"
                    class="w-full py-4 rounded-xl text-base font-black text-black flex items-center justify-center gap-2.5 mt-2 transition-opacity hover:opacity-90"
                    style="background:#22c55e;">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
                </svg>
                Pay Securely with PesaPal
            </button>

            <div class="mt-2 flex flex-col items-center gap-1.5">
                <p class="text-xs text-center flex items-center justify-center gap-1.5" style="color:#4ade80;">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
                    </svg>
                    Secured by <span class="font-black">PesaPal</span> — Trusted Worldwide
                </p>
                <p class="text-[10px] text-center text-gray-500">Card · Mobile Money · Bank Transfer · 256-bit SSL</p>
            </div>
        </form>

    </div>
</div>

<script>
    function openPlanModal(plan, label) {
        document.getElementById('modal-plan-input').value = plan;
        document.getElementById('modal-plan-label').textContent = label;
        document.getElementById('plan-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        setTimeout(() => document.querySelector('#plan-modal input[name="name"]').focus(), 100);
    }
    function closePlanModal() {
        document.getElementById('plan-modal').classList.add('hidden');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closePlanModal();
    });
</script>

@endsection
