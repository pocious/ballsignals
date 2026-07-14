@extends('layouts.admin')
@section('title', 'Pesapal Integration')
@section('heading', 'Pesapal Payment Integration')

@section('content')

{{-- Connection Status --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

    <div class="bg-white rounded-xl border {{ $status['connected'] ? 'border-green-300 bg-green-50' : 'border-red-300 bg-red-50' }} p-5">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Connection</p>
        <div class="flex items-center gap-2 mt-2">
            <span class="w-3 h-3 rounded-full {{ $status['connected'] ? 'bg-green-500' : 'bg-red-500' }}"></span>
            <p class="font-black text-gray-900 text-sm">{{ $status['connected'] ? 'Connected' : 'Failed' }}</p>
        </div>
        @if($status['token'])
            <p class="text-xs text-gray-400 mt-1 font-mono">{{ $status['token'] }}</p>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Mode</p>
        <div class="flex items-center gap-2 mt-2">
            <span class="w-3 h-3 rounded-full {{ $isLive ? 'bg-green-500' : 'bg-yellow-400' }}"></span>
            <p class="font-black text-gray-900 text-sm">{{ $isLive ? 'LIVE' : 'SANDBOX' }}</p>
        </div>
        <p class="text-xs text-gray-400 mt-1 font-mono truncate">{{ $baseUrl }}</p>
    </div>

    <div class="bg-white rounded-xl border {{ config('pesapal.ipn_id') ? 'border-green-300 bg-green-50' : 'border-yellow-300 bg-yellow-50' }} p-5">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">IPN ID</p>
        <p class="font-black text-gray-900 text-sm mt-2">
            {{ config('pesapal.ipn_id') ?: 'Not registered yet' }}
        </p>
        @if(!config('pesapal.ipn_id'))
            <p class="text-xs text-yellow-600 mt-1">Register below to enable payment notifications</p>
        @endif
    </div>

</div>

@if($status['error'])
<div class="mb-6 px-5 py-4 rounded-xl bg-red-50 border border-red-300 text-sm text-red-700">
    <strong>Error:</strong> {{ $status['error'] }}
</div>
@endif

@if(session('ipn_success'))
<div class="mb-6 px-5 py-4 rounded-xl bg-green-50 border border-green-300 text-sm text-green-700 font-semibold">
    ✓ {{ session('ipn_success') }}
</div>
@endif

@error('ipn')
<div class="mb-6 px-5 py-4 rounded-xl bg-red-50 border border-red-300 text-sm text-red-700">
    {{ $message }}
</div>
@enderror

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- URLs --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="font-bold text-gray-900 mb-4">Integration URLs</h2>
        <div class="space-y-4">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Callback URL</p>
                <p class="text-sm font-mono bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 break-all text-gray-700">{{ $callbackUrl }}</p>
                <p class="text-xs text-gray-400 mt-1">Pesapal redirects the customer here after payment</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">IPN Webhook URL</p>
                <p class="text-sm font-mono bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 break-all text-gray-700">{{ $ipnUrl }}</p>
                <p class="text-xs text-gray-400 mt-1">Pesapal calls this server-to-server to confirm payments</p>
            </div>
        </div>
    </div>

    {{-- Register IPN --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="font-bold text-gray-900 mb-1">Register / Refresh IPN</h2>
        <p class="text-xs text-gray-500 mb-4">
            This sends your IPN URL to Pesapal and saves the returned ID.
            Run this once when you go live, or whenever your domain changes.
        </p>

        @if($status['connected'])
        <form method="POST" action="{{ route('admin.pesapal.register-ipn') }}">
            @csrf
            <button type="submit"
                    class="w-full py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl transition-colors text-sm">
                Register IPN with Pesapal
            </button>
        </form>
        @else
        <div class="py-3 text-center text-sm text-red-500 font-semibold">
            Fix the connection error above before registering IPN.
        </div>
        @endif

        <div class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
            <p class="text-xs text-gray-500 font-mono">$ php artisan pesapal:setup</p>
            <p class="text-xs text-gray-400 mt-0.5">Alternative: run this command in your terminal</p>
        </div>
    </div>

</div>

{{-- Registered IPNs from Pesapal --}}
@if(!empty($status['ipns']))
<div class="bg-white rounded-xl border border-gray-200 mt-6">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="font-bold text-gray-900">Registered IPNs on Pesapal</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-4 py-3 text-left">IPN ID</th>
                    <th class="px-4 py-3 text-left">URL</th>
                    <th class="px-4 py-3 text-left">Type</th>
                    <th class="px-4 py-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($status['ipns'] as $ipn)
                <tr class="{{ ($ipn['ipn_id'] ?? '') === config('pesapal.ipn_id') ? 'bg-green-50' : '' }}">
                    <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $ipn['ipn_id'] ?? '—' }}</td>
                    <td class="px-4 py-3 text-xs text-gray-700 break-all">{{ $ipn['url'] ?? '—' }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $ipn['ipn_notification_type'] ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold
                            {{ ($ipn['ipn_status'] ?? '') === 'Active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $ipn['ipn_status'] ?? '—' }}
                        </span>
                        @if(($ipn['ipn_id'] ?? '') === config('pesapal.ipn_id'))
                            <span class="ml-1 text-[10px] text-green-600 font-bold">← current</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Credentials (masked) --}}
<div class="bg-white rounded-xl border border-gray-200 mt-6 p-6">
    <h2 class="font-bold text-gray-900 mb-4">Credentials (from .env)</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
        <div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Consumer Key</p>
            <p class="font-mono bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-700 text-xs">
                {{ substr(config('pesapal.consumer_key'), 0, 10) }}••••••••••••••••
            </p>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Consumer Secret</p>
            <p class="font-mono bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-700 text-xs">
                ••••••••••••••••••••••••••••••
            </p>
        </div>
    </div>
</div>

@endsection
