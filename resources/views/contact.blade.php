@extends('layouts.app')
@section('title', 'Contact Us — BallSignals')
@section('meta_description', 'Contact the BallSignals team. Questions about our football tips, premium service, partnerships, or feedback — we would love to hear from you.')
@section('canonical', route('contact'))
@section('robots', 'noindex, follow')

@section('content')

{{-- Hero --}}
<div class="bg-[#0a0f1a] border-b border-white/5">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-6 text-center">
        <div class="inline-flex items-center gap-1.5 bg-green-500/10 border border-green-500/25 text-green-400 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-3">
            <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>
            Get In Touch
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-white leading-tight mb-2">
            Contact <span class="text-green-400">Us</span>
        </h1>
        <p class="text-gray-400 text-xs sm:text-sm max-w-lg mx-auto leading-relaxed">
            Have a question, feedback, or partnership enquiry? We respond within 24 hours.
        </p>
    </div>
</div>

<div class="max-w-2xl mx-auto px-4 sm:px-6 py-8">

    {{-- Quick contact options --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-8">
        <a href="https://t.me/ballsigtips" target="_blank" rel="noopener noreferrer"
           class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-[#229ED9]/10 border border-[#229ED9]/30 hover:bg-[#229ED9]/20 hover:border-[#229ED9]/50 transition-all duration-200 group">
            <div class="w-10 h-10 rounded-xl bg-[#229ED9] flex items-center justify-center flex-shrink-0 shadow-lg shadow-[#229ED9]/30 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.248l-1.97 9.289c-.145.658-.537.818-1.084.508l-3-2.21-1.447 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.12L7.48 14.056l-2.95-.924c-.64-.203-.653-.64.136-.954l11.57-4.461c.537-.194 1.006.131.326.531z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-white">Telegram</p>
                <p class="text-xs text-[#229ED9]">@ballsigtips — fastest reply</p>
            </div>
        </a>

        <a href="https://facebook.com/ballsignals" target="_blank" rel="noopener noreferrer"
           class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-[#1877F2]/10 border border-[#1877F2]/30 hover:bg-[#1877F2]/20 hover:border-[#1877F2]/50 transition-all duration-200 group">
            <div class="w-10 h-10 rounded-xl bg-[#1877F2] flex items-center justify-center flex-shrink-0 shadow-lg shadow-[#1877F2]/30 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M24 12.073C24 5.406 18.627 0 12 0S0 5.406 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.41c0-3.025 1.792-4.697 4.533-4.697 1.312 0 2.686.236 2.686.236v2.97h-1.513c-1.491 0-1.956.93-1.956 1.886v2.268h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-white">Facebook</p>
                <p class="text-xs text-[#1877F2]">facebook.com/ballsignals</p>
            </div>
        </a>
    </div>

    {{-- Success message --}}
    @if(session('success'))
    <div class="mb-6 px-4 py-3 rounded-2xl bg-green-500/10 border border-green-500/30 text-green-400 text-sm text-center font-semibold">
        {{ session('success') }}
    </div>
    @endif

    {{-- Contact form --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
        <h2 class="text-sm font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-5">Send a Message</h2>

        <form method="POST" action="{{ route('contact.send') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Your Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           placeholder="e.g. James K."
                           class="w-full px-3 py-2.5 text-sm rounded-xl border
                                  {{ $errors->has('name') ? 'border-red-500 bg-red-500/5' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800' }}
                                  text-gray-900 dark:text-white placeholder-gray-400
                                  focus:outline-none focus:ring-2 focus:ring-green-500/50 focus:border-green-500 transition-colors">
                    @error('name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           placeholder="you@example.com"
                           class="w-full px-3 py-2.5 text-sm rounded-xl border
                                  {{ $errors->has('email') ? 'border-red-500 bg-red-500/5' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800' }}
                                  text-gray-900 dark:text-white placeholder-gray-400
                                  focus:outline-none focus:ring-2 focus:ring-green-500/50 focus:border-green-500 transition-colors">
                    @error('email')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Subject</label>
                <input type="text" name="subject" value="{{ old('subject') }}" required
                       placeholder="e.g. Partnership enquiry"
                       class="w-full px-3 py-2.5 text-sm rounded-xl border
                              {{ $errors->has('subject') ? 'border-red-500 bg-red-500/5' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800' }}
                              text-gray-900 dark:text-white placeholder-gray-400
                              focus:outline-none focus:ring-2 focus:ring-green-500/50 focus:border-green-500 transition-colors">
                @error('subject')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Message</label>
                <textarea name="message" rows="5" required
                          placeholder="Tell us what's on your mind..."
                          class="w-full px-3 py-2.5 text-sm rounded-xl border resize-none
                                 {{ $errors->has('message') ? 'border-red-500 bg-red-500/5' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800' }}
                                 text-gray-900 dark:text-white placeholder-gray-400
                                 focus:outline-none focus:ring-2 focus:ring-green-500/50 focus:border-green-500 transition-colors">{{ old('message') }}</textarea>
                @error('message')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full py-3 text-sm font-black text-black bg-green-400 rounded-xl
                           hover:bg-green-300 shadow-lg shadow-green-500/20 hover:shadow-green-500/40
                           transition-all duration-200 hover:-translate-y-px">
                Send Message
            </button>
        </form>
    </div>

    {{-- Disclaimer --}}
    <p class="text-[11px] text-center text-gray-500 mt-4">
        We aim to respond within 24 hours. For the fastest reply, message us on Telegram.
    </p>

</div>

@endsection
