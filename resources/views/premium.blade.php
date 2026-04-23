@extends('layouts.app')
@section('title', 'Premium Football Tips — BallSignals')
@section('meta_description', 'Unlock VIP football betting tips from BallSignals. High-confidence predictions, premium accumulator picks, and daily expert signals — starting from $15/week.')
@section('canonical', route('premium'))


@section('content')

@if($subscription)

{{-- ====== VIP ACCESS VIEW ====== --}}
<div class="bg-[#0a0f1a] border-b border-white/5">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-1.5 bg-yellow-400/10 border border-yellow-400/30 text-yellow-400 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-3">
                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 animate-pulse"></span>
                    VIP Access — {{ $subscription->plan_label }}
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white leading-tight">
                    Today's <span class="text-yellow-400">Premium Tips</span>
                </h1>
                <p class="text-gray-400 text-sm mt-1">
                    Signed in as <span class="text-white font-semibold">{{ $subscription->email }}</span>
                    &nbsp;·&nbsp; Expires {{ $subscription->expires_at->format('d M Y') }}
                    @if($subscription->expires_at->diffInDays(now()) <= 3)
                        <span class="text-red-400 font-semibold">(expiring soon)</span>
                    @endif
                </p>
            </div>
            <form method="POST" action="{{ route('premium.revoke') }}">
                @csrf
                <button type="submit"
                        class="px-4 py-2 text-xs font-semibold text-gray-400 border border-gray-700 rounded-xl hover:text-white hover:border-gray-500 transition-colors">
                    Sign Out
                </button>
            </form>
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
            <div class="bg-white dark:bg-gray-900 rounded-2xl border-2 border-yellow-400/40 overflow-hidden shadow-lg shadow-yellow-400/5 hover:border-yellow-400/70 transition-all">
                <div class="h-1 bg-gradient-to-r from-yellow-400 to-orange-400"></div>
                <div class="p-5">

                    {{-- Top row: league, time, status --}}
                    <div class="flex items-center justify-between gap-3 mb-4 flex-wrap">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-base">{{ $tip->sport_icon }}</span>
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
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $tip->status_badge }}">
                                {{ ucfirst($tip->status) }}
                            </span>
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

                </div>
            </div>
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
        <div class="inline-flex items-center gap-2 bg-green-500/10 border border-green-500/30 text-green-400 text-xs font-bold uppercase tracking-widest px-4 py-1.5 rounded-full mb-3">
            <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
            Premium Membership
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-white mb-2 leading-tight">
            Welcome — Your Daily<br>
            <span class="text-green-400">Sure Odds 2+ Await You</span>
        </h1>
        <p class="text-gray-400 text-sm max-w-xl mx-auto mb-2">
            Every single day, our analysts hand-pick sure odds of <span class="text-white font-semibold">2.00 and above</span> —
            carefully researched, high-confidence selections delivered straight to your Telegram before kick-off.
        </p>
        <p class="text-green-400/80 text-sm max-w-lg mx-auto mb-2 font-medium">
            No guesswork. No filler. Just consistent, winning value — every day.
        </p>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">

    {{-- What you get --}}
    <div class="mb-14">
        <h2 class="text-center text-xs font-bold uppercase tracking-widest text-gray-500 mb-8">What's Included</h2>
        @php
        $features = [
            ['icon' => '🎯', 'title' => 'High-Confidence Tips',  'desc' => 'Only tips with 4 and 5 star confidence ratings — no filler picks.'],
            ['icon' => '📊', 'title' => 'Daily Match Analysis',  'desc' => 'Full breakdown of form, head-to-head, team news and odds value.'],
            ['icon' => '🔔', 'title' => 'Instant Telegram Alerts','desc' => 'Get tips delivered to your Telegram the moment they are posted.'],
            ['icon' => '⚡', 'title' => 'Accumulators',           'desc' => 'Weekly curated accumulator slips with top odds combinations.'],
            ['icon' => '📈', 'title' => 'Odds Value Picks',       'desc' => 'We identify overpriced odds so you always bet with an edge.'],
            ['icon' => '🏆', 'title' => 'All Major Leagues',      'desc' => 'Premier League, La Liga, Serie A, Bundesliga, Champions League and more.'],
        ];
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($features as $f)
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 hover:border-green-500/30 transition-colors">
                <div class="text-2xl mb-3">{{ $f['icon'] }}</div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-1.5">{{ $f['title'] }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">{{ $f['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Pricing cards --}}
    <div class="mb-14">
        <h2 class="text-center text-xs font-bold uppercase tracking-widest text-gray-500 mb-2">Choose Your Plan</h2>
        <p class="text-center text-sm text-gray-400 mb-8">Subscribe via Telegram — cancel anytime.</p>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">

            {{-- Weekly --}}
            <div class="relative bg-white dark:bg-gray-900 rounded-2xl border-2 border-gray-200 dark:border-gray-800 p-7 flex flex-col hover:border-green-400/50 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-green-500/5">
                <div class="mb-6">
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-500 mb-3">Weekly</p>
                    <div class="flex items-end gap-1.5 mb-2">
                        <span class="text-4xl font-black text-gray-900 dark:text-white">$15</span>
                        <span class="text-sm text-gray-400 mb-1.5">/ week</span>
                    </div>
                    <p class="text-xs text-gray-400">Try premium risk-free for one week.</p>
                </div>
                @php
                $weeklyFeatures = [
                    'All daily premium tips',
                    'Telegram group access',
                    'Match analysis reports',
                    '1 accumulator per week',
                ];
                @endphp
                <ul class="space-y-2.5 mb-8 flex-1">
                    @foreach($weeklyFeatures as $feature)
                    <li class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ $feature }}
                    </li>
                    @endforeach
                </ul>
                <a href="#payment-instructions"
                   class="w-full text-center block py-3 rounded-xl text-sm font-bold text-green-700 dark:text-green-400 border-2 border-green-500/40 hover:bg-green-500/10 transition-all duration-200">
                    Subscribe — $15/week
                </a>
            </div>

            {{-- Monthly (highlighted) --}}
            <div class="relative bg-[#0a0f1a] rounded-2xl border-2 border-green-500 p-7 flex flex-col shadow-2xl shadow-green-500/10 hover:-translate-y-0.5 transition-all duration-200">
                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2">
                    <span class="bg-green-500 text-black text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-lg">
                        Most Popular
                    </span>
                </div>
                <div class="mb-6">
                    <p class="text-xs font-bold uppercase tracking-widest text-green-400 mb-3">Monthly</p>
                    <div class="flex items-end gap-1.5 mb-2">
                        <span class="text-4xl font-black text-white">$30</span>
                        <span class="text-sm text-gray-400 mb-1.5">/ month</span>
                    </div>
                    <p class="text-xs text-green-400/70">Save 50% vs weekly — best value.</p>
                </div>
                @php
                $monthlyFeatures = [
                    'Everything in Weekly',
                    'Priority tip delivery',
                    'Daily accumulator slips',
                    'Odds value alerts',
                    'VIP Telegram channel',
                    'Monthly performance report',
                ];
                @endphp
                <ul class="space-y-2.5 mb-8 flex-1">
                    @foreach($monthlyFeatures as $feature)
                    <li class="flex items-center gap-2 text-sm text-gray-300">
                        <svg class="w-4 h-4 text-green-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ $feature }}
                    </li>
                    @endforeach
                </ul>
                <a href="#payment-instructions"
                   class="w-full text-center block py-3 rounded-xl text-sm font-black text-black bg-green-400 hover:bg-green-300 shadow-lg shadow-green-500/30 transition-all duration-200">
                    Subscribe — $30/month
                </a>
            </div>

            {{-- 2-Month --}}
            <div class="relative bg-white dark:bg-gray-900 rounded-2xl border-2 border-yellow-400/60 dark:border-yellow-500/40 p-7 flex flex-col hover:-translate-y-0.5 hover:shadow-xl hover:shadow-yellow-500/10 transition-all duration-200">
                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2">
                    <span class="bg-yellow-400 text-black text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-lg whitespace-nowrap">
                        Best Deal
                    </span>
                </div>
                <div class="mb-6">
                    <p class="text-xs font-bold uppercase tracking-widest text-yellow-600 dark:text-yellow-400 mb-3">2 Months</p>
                    <div class="flex items-end gap-1.5 mb-2">
                        <span class="text-4xl font-black text-gray-900 dark:text-white">$45</span>
                        <span class="text-sm text-gray-400 mb-1.5">/ 2 months</span>
                    </div>
                    <p class="text-xs text-yellow-600 dark:text-yellow-400/80">Save 25% vs monthly — lowest price.</p>
                </div>
                @php
                $twoMonthFeatures = [
                    'Everything in Monthly',
                    'Priority tip delivery',
                    'Daily accumulator slips',
                    'Odds value alerts',
                    'VIP Telegram channel',
                    'Dedicated support',
                    '2x monthly performance reports',
                ];
                @endphp
                <ul class="space-y-2.5 mb-8 flex-1">
                    @foreach($twoMonthFeatures as $feature)
                    <li class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-4 h-4 text-yellow-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ $feature }}
                    </li>
                    @endforeach
                </ul>
                <a href="#payment-instructions"
                   class="w-full text-center block py-3 rounded-xl text-sm font-black text-black bg-yellow-400 hover:bg-yellow-300 shadow-lg shadow-yellow-500/20 transition-all duration-200">
                    Subscribe — $45/2 months
                </a>
            </div>

        </div>
    </div>

    {{-- How to subscribe --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-7 mb-14">
        <h2 class="text-sm font-black uppercase tracking-widest text-gray-700 dark:text-gray-300 mb-6 text-center">How to Subscribe</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">

            <div class="flex flex-col items-center text-center gap-3">
                <div class="w-10 h-10 rounded-full bg-green-500/10 border border-green-500/30 flex items-center justify-center">
                    <span class="text-sm font-black text-green-500">1</span>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800 dark:text-gray-200 mb-1">Choose a Plan</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">Pick the Weekly or Monthly plan that suits you best.</p>
                </div>
            </div>

            <div class="flex flex-col items-center text-center gap-3">
                <div class="w-10 h-10 rounded-full bg-green-500/10 border border-green-500/30 flex items-center justify-center">
                    <span class="text-sm font-black text-green-500">2</span>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800 dark:text-gray-200 mb-1">Secure Payment</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">Complete your payment securely via our payment gateway.</p>
                </div>
            </div>

            <div class="flex flex-col items-center text-center gap-3">
                <div class="w-10 h-10 rounded-full bg-green-500/10 border border-green-500/30 flex items-center justify-center">
                    <span class="text-sm font-black text-green-500">3</span>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800 dark:text-gray-200 mb-1">Instant Access</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">Your account is upgraded immediately — premium tips start flowing in.</p>
                </div>
            </div>

        </div>
    </div>

    {{-- Request Form --}}
    <div id="payment-instructions" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden scroll-mt-20">

        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800">
            <p class="text-sm font-black text-gray-900 dark:text-white">Request Premium Access</p>
            <p class="text-xs text-gray-400 mt-0.5">Fill in your details — we'll confirm and activate your access within 30 minutes.</p>
        </div>

        @if(session('sub_success'))
            <div class="px-6 py-12 text-center">
                <div class="w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="text-lg font-black text-gray-900 dark:text-white mb-2">Request Received!</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto leading-relaxed">
                    We've got your request and will reach out on WhatsApp within 30 minutes to confirm payment and activate your access.
                </p>
                <a href="https://wa.me/256763920010" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 mt-5 px-5 py-2.5 rounded-xl bg-green-500 hover:bg-green-600 text-white text-sm font-bold transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    Chat on WhatsApp — 0763 920 010
                </a>
            </div>
        @else
            <form method="POST" action="{{ route('premium.request') }}" class="px-6 py-5 space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Full Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. James Osei"
                               class="w-full px-4 py-2.5 text-sm rounded-xl border
                                      {{ $errors->has('name') ? 'border-red-500 bg-red-500/5' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800' }}
                                      text-gray-900 dark:text-white placeholder-gray-400
                                      focus:outline-none focus:ring-2 focus:ring-green-500/50 focus:border-green-500 transition-colors">
                        @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">WhatsApp / Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="e.g. 0763 920 010"
                               class="w-full px-4 py-2.5 text-sm rounded-xl border
                                      {{ $errors->has('phone') ? 'border-red-500 bg-red-500/5' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800' }}
                                      text-gray-900 dark:text-white placeholder-gray-400
                                      focus:outline-none focus:ring-2 focus:ring-green-500/50 focus:border-green-500 transition-colors">
                        @error('phone')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="you@example.com"
                           class="w-full px-4 py-2.5 text-sm rounded-xl border
                                  {{ $errors->has('email') ? 'border-red-500 bg-red-500/5' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800' }}
                                  text-gray-900 dark:text-white placeholder-gray-400
                                  focus:outline-none focus:ring-2 focus:ring-green-500/50 focus:border-green-500 transition-colors">
                    @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Select Plan</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @foreach(['weekly' => ['$15 / week', '7 days'], 'monthly' => ['$30 / month', '30 days'], 'two_months' => ['$45 / 2 months', '60 days']] as $value => [$price, $duration])
                        <label class="relative cursor-pointer">
                            <input type="radio" name="plan" value="{{ $value }}"
                                   {{ old('plan', 'monthly') === $value ? 'checked' : '' }}
                                   class="peer sr-only">
                            <div class="px-4 py-3 rounded-xl border-2 text-center transition-all
                                        border-gray-200 dark:border-gray-700
                                        peer-checked:border-green-500 peer-checked:bg-green-500/5
                                        hover:border-green-400/50">
                                <p class="text-sm font-black text-gray-900 dark:text-white">{{ $price }}</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">{{ $duration }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @error('plan')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <button type="submit"
                        class="w-full py-3.5 text-sm font-black text-black bg-green-400 rounded-xl
                               hover:bg-green-300 shadow-lg shadow-green-500/20 hover:shadow-green-500/40
                               transition-all duration-200 hover:-translate-y-px">
                    Submit Request — We'll Contact You Within 30 Min
                </button>

                <p class="text-[11px] text-center text-gray-400">
                    Or WhatsApp us directly on <a href="https://wa.me/256763920010" class="underline hover:text-green-500 transition-colors">0763 920 010</a>
                </p>

            </form>
        @endif

    </div>

    <p class="text-center text-xs text-gray-500 dark:text-gray-600 mt-8 leading-relaxed">
        18+ only. Betting involves risk — only bet what you can afford to lose. Past performance does not guarantee future results. Please gamble responsibly.
    </p>

</div>

@endif

@endsection
