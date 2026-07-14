@extends('layouts.app')
@section('title', 'Payment Status — BallSignals')

@section('content')
<div class="max-w-lg mx-auto px-4 py-16 text-center">

    @if($status === 'success')
        <div class="w-20 h-20 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-3">Payment Successful!</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed mb-2">
            Your <strong class="text-gray-800 dark:text-gray-200">{{ $sub->plan_label }}</strong> subscription is now active.
        </p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">
            Access expires on <strong class="text-gray-800 dark:text-gray-200">{{ $sub->expires_at->format('d M Y') }}</strong>.
        </p>
        <a href="{{ route('premium') }}"
           class="inline-block px-8 py-3 bg-green-500 hover:bg-green-400 text-white font-black rounded-xl transition-colors">
            View VIP Tips →
        </a>
        <p class="text-xs text-gray-400 mt-4">
            Enter <strong>{{ $sub->email }}</strong> on the VIP page to access your tips.
        </p>

    @elseif($status === 'pending')
        <div class="w-20 h-20 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-3">Payment Processing</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed mb-8">
            {{ $message ?? 'Your payment is being confirmed. This usually takes a few seconds for mobile money.' }}
        </p>
        @if(!empty($email))
            <a href="{{ route('premium') }}"
               class="inline-block px-8 py-3 bg-yellow-400 hover:bg-yellow-300 text-black font-black rounded-xl transition-colors">
                Check Access
            </a>
            <p class="text-xs text-gray-400 mt-4">Enter <strong>{{ $email }}</strong> on the VIP page once confirmed.</p>
        @else
            <a href="{{ route('premium') }}"
               class="inline-block px-8 py-3 bg-yellow-400 hover:bg-yellow-300 text-black font-black rounded-xl transition-colors">
                Go to Premium Page
            </a>
        @endif

    @elseif($status === 'failed')
        <div class="w-20 h-20 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-3">Payment Failed</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed mb-8">
            {{ $message ?? 'Your payment could not be processed. Please try again.' }}
        </p>
        <a href="{{ route('premium') }}"
           class="inline-block px-8 py-3 bg-gray-800 hover:bg-gray-700 text-white font-black rounded-xl transition-colors">
            Try Again
        </a>

    @else
        <div class="w-20 h-20 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-3">Something Went Wrong</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed mb-8">
            {{ $message ?? 'An unexpected error occurred.' }}
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('premium') }}"
               class="px-8 py-3 bg-gray-800 hover:bg-gray-700 text-white font-black rounded-xl transition-colors">
                Try Again
            </a>
            <a href="https://wa.me/256763920010" target="_blank" rel="noopener noreferrer"
               class="px-8 py-3 bg-green-500 hover:bg-green-400 text-white font-black rounded-xl transition-colors">
                WhatsApp Support
            </a>
        </div>
    @endif

</div>
@endsection
