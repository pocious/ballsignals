@extends('layouts.app')
@section('title', 'Get Free Daily Football Tips by Email — BallSignals')
@section('meta_description', 'Subscribe to BallSignals free daily football tips email. Get expert predictions delivered straight to your inbox every morning — completely free, no spam.')
@section('canonical', route('subscribe'))

@section('content')

{{-- Hero --}}
<div class="bg-[#0a0f1a] border-b border-white/5">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-10 text-center">

        <div class="inline-flex items-center gap-1.5 bg-green-500/10 border border-green-500/25 text-green-400 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-4">
            <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
            Free · No Credit Card
        </div>

        <h1 class="text-3xl sm:text-4xl font-black text-white leading-tight mb-3">
            Get <span class="text-green-400">Daily Tips</span><br>Straight to Your Inbox
        </h1>
        <p class="text-gray-400 text-sm sm:text-base max-w-md mx-auto leading-relaxed">
            Join thousands of members receiving expert football predictions every morning — completely free.
        </p>

        {{-- Trust pills --}}
        <div class="flex flex-wrap items-center justify-center gap-2 mt-5">
            @foreach(['Free Forever', 'Daily at 8 AM', 'Expert Picks', 'No Spam', 'Unsubscribe Anytime'] as $pill)
            <span class="text-[11px] font-semibold text-gray-400 bg-white/5 border border-white/10 px-3 py-1 rounded-full">
                {{ $pill }}
            </span>
            @endforeach
        </div>
    </div>
</div>

<div class="max-w-xl mx-auto px-4 sm:px-6 py-10">

    {{-- What you get --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-8">
        @foreach([
            ['⚽', "Today's Tips", "Free picks for today's matches delivered every morning."],
            ['📊', 'Confidence Rated', 'Each tip scored 1–5 stars by our analysts.'],
            ['📈', 'Odds Included', 'Best odds highlighted so you know the value.'],
        ] as [$icon, $title, $desc])
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 text-center">
            <div class="text-2xl mb-2">{{ $icon }}</div>
            <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">{{ $title }}</p>
            <p class="text-xs text-gray-400 leading-relaxed">{{ $desc }}</p>
        </div>
        @endforeach
    </div>

    {{-- Subscription form --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8">

        @if(session('success'))
            <div class="flex flex-col items-center text-center py-6">
                <div class="w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h2 class="text-lg font-black text-gray-900 dark:text-white mb-2">You're In! 🎉</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                    {{ session('success') }}
                </p>
                <a href="{{ route('home') }}"
                   class="mt-5 px-6 py-2.5 bg-green-500 text-black text-sm font-black rounded-xl hover:bg-green-400 transition-colors">
                    View Today's Tips →
                </a>
            </div>
        @else
            <div class="text-center mb-6">
                <h2 class="text-lg font-black text-gray-900 dark:text-white mb-1">Subscribe for Free</h2>
                <p class="text-sm text-gray-400">Enter your details — we'll send today's tips right away.</p>
            </div>

            <form method="POST" action="{{ route('subscribe.send') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">
                        Your Name
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           placeholder="e.g. James"
                           class="w-full px-4 py-3 text-sm rounded-xl border
                                  {{ $errors->has('name') ? 'border-red-500 bg-red-500/5' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800' }}
                                  text-gray-900 dark:text-white placeholder-gray-400
                                  focus:outline-none focus:ring-2 focus:ring-green-500/50 focus:border-green-500 transition-colors">
                    @error('name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">
                        Email Address
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           placeholder="you@example.com"
                           class="w-full px-4 py-3 text-sm rounded-xl border
                                  {{ $errors->has('email') ? 'border-red-500 bg-red-500/5' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800' }}
                                  text-gray-900 dark:text-white placeholder-gray-400
                                  focus:outline-none focus:ring-2 focus:ring-green-500/50 focus:border-green-500 transition-colors">
                    @error('email')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full py-3.5 text-sm font-black text-black bg-green-400 rounded-xl
                               hover:bg-green-300 shadow-lg shadow-green-500/20 hover:shadow-green-500/40
                               transition-all duration-200 hover:-translate-y-px">
                    Subscribe — It's Free ⚽
                </button>

                <p class="text-[11px] text-center text-gray-400 leading-relaxed">
                    By subscribing you agree to receive daily football tips by email.
                    No spam. Unsubscribe any time.
                </p>
            </form>
        @endif
    </div>

    {{-- Social proof --}}
    <div class="mt-6 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5">
        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-3 text-center">Also join us on</p>
        <div class="flex items-center justify-center gap-3">
            <a href="https://t.me/ballsigtips" target="_blank" rel="noopener noreferrer"
               class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#229ED9]/10 border border-[#229ED9]/30
                      hover:bg-[#229ED9]/20 transition-all duration-200 group">
                <svg class="w-4 h-4 text-[#229ED9]" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.248l-1.97 9.289c-.145.658-.537.818-1.084.508l-3-2.21-1.447 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.12L7.48 14.056l-2.95-.924c-.64-.203-.653-.64.136-.954l11.57-4.461c.537-.194 1.006.131.326.531z"/>
                </svg>
                <span class="text-xs font-semibold text-[#229ED9]">Telegram Channel</span>
            </a>
            <a href="https://facebook.com/ballsignals" target="_blank" rel="noopener noreferrer"
               class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#1877F2]/10 border border-[#1877F2]/30
                      hover:bg-[#1877F2]/20 transition-all duration-200 group">
                <svg class="w-4 h-4 text-[#1877F2]" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M24 12.073C24 5.406 18.627 0 12 0S0 5.406 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.41c0-3.025 1.792-4.697 4.533-4.697 1.312 0 2.686.236 2.686.236v2.97h-1.513c-1.491 0-1.956.93-1.956 1.886v2.268h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/>
                </svg>
                <span class="text-xs font-semibold text-[#1877F2]">Facebook Page</span>
            </a>
        </div>
    </div>

</div>

@endsection
