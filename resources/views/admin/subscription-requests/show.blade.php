@extends('layouts.admin')
@section('title', 'Subscription Request')
@section('heading', 'Subscription Request')

@section('content')

<div class="max-w-2xl">

    {{-- Back link --}}
    <a href="{{ route('admin.subscription-requests.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-900 mb-5 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        All Requests
    </a>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        {{-- Header --}}
        <div class="px-6 py-5 border-b border-gray-100 flex items-start justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                    <span class="text-lg font-bold text-green-700">{{ strtoupper($subscriptionRequest->name[0]) }}</span>
                </div>
                <div>
                    <p class="font-bold text-gray-900 text-lg leading-tight">{{ $subscriptionRequest->name }}</p>
                    <p class="text-sm text-gray-400">{{ $subscriptionRequest->email }}</p>
                </div>
            </div>
            <span class="px-3 py-1.5 rounded-full text-sm font-semibold {{ $subscriptionRequest->status_badge }}">
                {{ ucfirst($subscriptionRequest->status) }}
            </span>
        </div>

        {{-- Details --}}
        <div class="px-6 py-5 space-y-4">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Plan</p>
                    <p class="font-semibold text-gray-900">{{ $subscriptionRequest->plan_label }}</p>
                    <p class="text-sm text-gray-500">{{ $subscriptionRequest->plan_price }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Phone</p>
                    <p class="text-gray-900">{{ $subscriptionRequest->phone ?? '—' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Submitted</p>
                    <p class="text-gray-900">{{ $subscriptionRequest->created_at->format('d M Y, g:i A') }}</p>
                </div>
                @if($subscriptionRequest->approved_at)
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Approved</p>
                    <p class="text-gray-900">{{ $subscriptionRequest->approved_at->format('d M Y, g:i A') }}</p>
                </div>
                @endif
            </div>

            @if($subscriptionRequest->expires_at)
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Access Expires</p>
                <p class="font-semibold {{ $subscriptionRequest->isExpired() ? 'text-red-600' : 'text-green-700' }}">
                    {{ $subscriptionRequest->expires_at->format('d M Y, g:i A') }}
                    @if($subscriptionRequest->isExpired())
                        <span class="text-xs font-normal text-red-400 ml-1">(expired)</span>
                    @else
                        <span class="text-xs font-normal text-gray-400 ml-1">({{ $subscriptionRequest->expires_at->diffForHumans() }})</span>
                    @endif
                </p>
            </div>
            @endif

            @if($subscriptionRequest->notes)
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Notes</p>
                <p class="text-gray-700 text-sm leading-relaxed bg-gray-50 rounded-lg px-4 py-3">
                    {{ $subscriptionRequest->notes }}
                </p>
            </div>
            @endif

        </div>

        {{-- Actions --}}
        @if($subscriptionRequest->status === 'pending')
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center gap-3 flex-wrap">
            <form method="POST" action="{{ route('admin.subscription-requests.approve', $subscriptionRequest) }}"
                  onsubmit="return confirm('Approve this subscription request?')">
                @csrf @method('PATCH')
                <button type="submit"
                        class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-colors">
                    ✓ Approve
                </button>
            </form>
            <form method="POST" action="{{ route('admin.subscription-requests.reject', $subscriptionRequest) }}"
                  onsubmit="return confirm('Reject this request?')">
                @csrf @method('PATCH')
                <button type="submit"
                        class="px-5 py-2 bg-red-100 hover:bg-red-200 text-red-700 text-sm font-semibold rounded-lg transition-colors">
                    ✗ Reject
                </button>
            </form>
            <form method="POST" action="{{ route('admin.subscription-requests.destroy', $subscriptionRequest) }}"
                  onsubmit="return confirm('Delete this request permanently?')" class="ml-auto">
                @csrf @method('DELETE')
                <button type="submit"
                        class="px-4 py-2 text-sm text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                    Delete
                </button>
            </form>
        </div>
        @else
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
            <form method="POST" action="{{ route('admin.subscription-requests.destroy', $subscriptionRequest) }}"
                  onsubmit="return confirm('Delete this request permanently?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="px-4 py-2 text-sm text-red-500 border border-red-200 hover:bg-red-50 rounded-lg transition-colors">
                    Delete Request
                </button>
            </form>
        </div>
        @endif

    </div>

</div>

@endsection
