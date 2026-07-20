@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')

{{-- ── New Payment Notification Banner ── --}}
@if($newPayments->isNotEmpty())
<div class="mb-6 rounded-xl border border-green-300 bg-green-50 px-5 py-4">
    <div class="flex items-start gap-3">
        <span class="text-2xl">💰</span>
        <div class="flex-1">
            <p class="font-bold text-green-800 text-sm">
                {{ $newPayments->count() }} new payment{{ $newPayments->count() > 1 ? 's' : '' }} in the last 24 hours!
            </p>
            <div class="mt-2 space-y-1">
                @foreach($newPayments as $np)
                <p class="text-xs text-green-700">
                    <strong>{{ $np->name }}</strong> ({{ $np->email }}) — {{ $np->plan_label }} plan ·
                    <span class="font-semibold">${{ number_format(App\Models\SubscriptionRequest::$plans[$np->plan]['amount_usd'] ?? 0, 2) }}</span> ·
                    paid {{ $np->approved_at->diffForHumans() }}
                </p>
                @endforeach
            </div>
        </div>
        <a href="{{ route('admin.subscription-requests.index') }}"
           class="flex-shrink-0 px-3 py-1.5 bg-green-600 text-white text-xs font-bold rounded-lg hover:bg-green-700 transition-colors">
            View All
        </a>
    </div>
</div>
@endif

{{-- Stats Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">

    <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5">
        <p class="text-xs sm:text-sm text-gray-500">Total Tips</p>
        <p class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5">
        <p class="text-xs sm:text-sm text-gray-500">Pending</p>
        <p class="text-2xl sm:text-3xl font-bold text-yellow-600 mt-1">{{ $stats['pending'] }}</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5">
        <p class="text-xs sm:text-sm text-gray-500">Won</p>
        <p class="text-2xl sm:text-3xl font-bold text-green-600 mt-1">{{ $stats['won'] }}</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5">
        <p class="text-xs sm:text-sm text-gray-500">Lost</p>
        <p class="text-2xl sm:text-3xl font-bold text-red-600 mt-1">{{ $stats['lost'] }}</p>
    </div>

    <a href="{{ route('admin.contacts.index') }}"
       class="col-span-2 sm:col-span-1 bg-white rounded-xl border p-4 sm:p-5 hover:border-red-300 transition-colors
              {{ $stats['unread'] > 0 ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
        <p class="text-xs sm:text-sm text-gray-500">Unread Messages</p>
        <p class="text-2xl sm:text-3xl font-bold mt-1 {{ $stats['unread'] > 0 ? 'text-red-600' : 'text-gray-900' }}">
            {{ $stats['unread'] }}
        </p>
    </a>

</div>

{{-- ── Today's VIP Tips ── --}}
<div class="bg-white rounded-xl border-2 border-yellow-400 mb-8">
    <div class="px-6 py-4 border-b border-yellow-100 flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-2">
            <span class="text-[10px] font-black text-black bg-yellow-400 px-2 py-0.5 rounded uppercase tracking-wide">VIP</span>
            <h2 class="font-bold text-gray-900">Today's VIP Tips</h2>
            <span class="text-xs text-gray-400">{{ today()->format('d M Y') }}</span>
            @php $vipCount = $todayVipTips->count(); @endphp
            @if($vipCount < 2)
                <span class="text-[10px] font-bold text-orange-600 bg-orange-50 border border-orange-200 px-2 py-0.5 rounded-full">
                    {{ $vipCount }}/2 tips — add {{ 2 - $vipCount }} more
                </span>
            @else
                <span class="text-[10px] font-bold text-green-700 bg-green-50 border border-green-200 px-2 py-0.5 rounded-full">
                    {{ $vipCount }} tips ready
                </span>
            @endif
        </div>
        <a href="{{ route('admin.betting-tips.create', ['vip' => 1]) }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 bg-yellow-400 hover:bg-yellow-300 text-black text-sm font-black rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            Add VIP Tip
        </a>
    </div>

    @if($todayVipTips->isEmpty())
        <div class="px-6 py-8 text-center">
            <p class="text-sm text-gray-400 mb-3">No VIP tips added for today yet.</p>
            <a href="{{ route('admin.betting-tips.create', ['vip' => 1]) }}"
               class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-yellow-400 hover:bg-yellow-300 text-black text-sm font-black rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Add Today's First VIP Tip
            </a>
        </div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($todayVipTips as $vip)
            <div class="px-6 py-3.5 flex items-center justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-sm font-bold text-gray-900 whitespace-nowrap">
                            {{ $vip->home_team }} <span class="font-normal text-gray-400">vs</span> {{ $vip->away_team }}
                        </p>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full
                            {{ $vip->status === 'won' ? 'bg-green-100 text-green-700' : ($vip->status === 'lost' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                            {{ ucfirst($vip->status) }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $vip->match_time->format('g:i A') }}
                        @if($vip->league) · {{ $vip->league }}@endif
                        · <span class="font-semibold text-gray-600">{{ $vip->prediction }}</span>
                        @if($vip->odds) · <span class="text-yellow-600 font-bold">{{ number_format($vip->odds, 2) }}</span>@endif
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="{{ route('admin.betting-tips.edit', $vip) }}"
                       class="text-xs text-blue-600 hover:underline font-semibold">Edit</a>
                    <form method="POST" action="{{ route('admin.betting-tips.destroy', $vip) }}"
                          onsubmit="return confirm('Delete this VIP tip?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-semibold">Delete</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

{{-- ── Premium Subscriptions Overview ── --}}
<div class="bg-white rounded-xl border-2 border-green-400 mb-8">
    <div class="px-6 py-4 border-b border-green-100 flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-2">
            <span class="text-lg">💎</span>
            <h2 class="font-bold text-gray-900">Premium Subscriptions</h2>
        </div>
        <a href="{{ route('admin.subscription-requests.index') }}"
           class="text-sm text-green-600 font-semibold hover:underline">View all →</a>
    </div>

    {{-- Sub Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-0 divide-x divide-y sm:divide-y-0 divide-gray-100 border-b border-gray-100">
        <div class="px-6 py-4">
            <p class="text-xs text-gray-500 mb-1">Active Subscribers</p>
            <p class="text-2xl font-black text-green-600">{{ $subStats['active'] }}</p>
        </div>
        <div class="px-6 py-4">
            <p class="text-xs text-gray-500 mb-1">Pending Payments</p>
            <p class="text-2xl font-black text-yellow-500">{{ $subStats['pending'] }}</p>
        </div>
        <div class="px-6 py-4">
            <p class="text-xs text-gray-500 mb-1">Expiring in 3 Days</p>
            <p class="text-2xl font-black {{ $subStats['expiring_soon'] > 0 ? 'text-red-500' : 'text-gray-400' }}">
                {{ $subStats['expiring_soon'] }}
            </p>
        </div>
        <div class="px-6 py-4">
            <p class="text-xs text-gray-500 mb-1">Total Revenue</p>
            <p class="text-2xl font-black text-gray-900">${{ number_format($subStats['total_revenue'], 0) }}</p>
        </div>
    </div>

    {{-- Recent Paid Subscribers --}}
    @if($recentSubs->isNotEmpty())
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-green-50 text-gray-500 text-xs uppercase tracking-wider">
                <tr>
                    <th class="px-4 py-3 text-left">Subscriber</th>
                    <th class="px-4 py-3 text-left">Plan</th>
                    <th class="px-4 py-3 text-left">Amount</th>
                    <th class="px-4 py-3 text-left">Progress</th>
                    <th class="px-4 py-3 text-left">Expires</th>
                    <th class="px-4 py-3 text-left">Paid</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($recentSubs as $sub)
                @php
                    $plan        = App\Models\SubscriptionRequest::$plans[$sub->plan];
                    $totalDays   = (int) $plan['days'];
                    $elapsed     = $sub->approved_at ? (int) now()->diffInDays($sub->approved_at) : 0;
                    $remaining   = $sub->expires_at  ? max(0, (int) now()->diffInDays($sub->expires_at, false)) : 0;
                    $progress    = $totalDays > 0 ? min(100, (int) round(($elapsed / $totalDays) * 100)) : 0;
                    $isNew       = (bool) ($sub->approved_at && $sub->approved_at->gte(now()->subHours(24)));
                    $rowClass    = 'hover:bg-gray-50 transition-colors ' . ($isNew ? 'bg-green-50/60' : '');
                    $barClass    = 'h-full rounded-full ' . ($progress >= 80 ? 'bg-red-400' : 'bg-green-400');
                @endphp
                <tr class="{{ $rowClass }}">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-black text-green-700">{{ strtoupper($sub->name[0]) }}</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 text-xs whitespace-nowrap">
                                    {{ $sub->name }}
                                    @if($isNew)<span class="ml-1 px-1.5 py-0.5 bg-green-500 text-white text-[9px] font-black rounded-full">NEW</span>@endif
                                </p>
                                <p class="text-[11px] text-gray-400">{{ $sub->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                            {{ $sub->plan_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3 font-bold text-gray-900 whitespace-nowrap">
                        ${{ number_format($plan['amount_usd'], 2) }}
                    </td>
                    <td class="px-4 py-3 min-w-[120px]">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                <div class="{{ $barClass }}" data-progress="{{ $progress }}"></div>
                            </div>
                            <span class="text-[10px] text-gray-400 whitespace-nowrap">{{ $remaining }}d left</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                        {{ $sub->expires_at ? $sub->expires_at->format('d M Y') : '—' }}
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">
                        {{ $sub->approved_at ? $sub->approved_at->diffForHumans() : '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
        <div class="px-6 py-8 text-center text-gray-400 text-sm">No paid subscribers yet.</div>
    @endif

    {{-- Pending (awaiting payment) --}}
    @if($pendingSubs->isNotEmpty())
    <div class="border-t border-gray-100 px-6 py-4">
        <p class="text-xs font-bold uppercase tracking-widest text-yellow-600 mb-3">⏳ Pending — Awaiting Payment</p>
        <div class="space-y-2">
            @foreach($pendingSubs as $ps)
            <div class="flex items-center justify-between text-xs text-gray-600 bg-yellow-50 rounded-lg px-3 py-2">
                <span><strong>{{ $ps->name }}</strong> · {{ $ps->email }}</span>
                <span class="font-semibold text-yellow-700">{{ $ps->plan_label }} · {{ $ps->created_at->diffForHumans() }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

{{-- Recent Tips --}}
<div class="bg-white rounded-xl border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="font-semibold text-gray-900">Recent Betting Tips</h2>
        <a href="{{ route('admin.betting-tips.create') }}"
           class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
            + New Tip
        </a>
    </div>

    @if($recent->isEmpty())
        <div class="px-6 py-12 text-center text-gray-400">
            <p class="text-lg">No tips yet.</p>
            <a href="{{ route('admin.betting-tips.create') }}" class="text-green-600 text-sm mt-2 inline-block hover:underline">
                Create your first tip →
            </a>
        </div>
    @else
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                <tr>
                    <th class="px-4 py-3 text-left">Match</th>
                    <th class="px-4 py-3 text-left">Prediction</th>
                    <th class="px-4 py-3 text-left">Odds</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Match Time</th>
                    <th class="px-4 py-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($recent as $tip)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">{{ $tip->matchup }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $tip->prediction }}</td>
                    <td class="px-4 py-3 font-semibold">{{ $tip->odds }}</td>
                    <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $tip->match_time->format('M j, Y g:i A') }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $tip->admin_status_badge }}">
                            {{ ucfirst($tip->status) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    @endif
</div>

{{-- VIP / Premium Tips Management --}}
<div class="bg-white rounded-xl border-2 border-yellow-400 mt-8">
    <div class="px-6 py-4 border-b border-yellow-100 flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-black bg-yellow-400 text-black uppercase tracking-wide">⭐ VIP</span>
            <h2 class="font-semibold text-gray-900">Premium Tips Management</h2>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-3 text-xs font-semibold">
                <span class="text-gray-500">Total: <span class="text-gray-900 font-bold">{{ $premiumStats['total'] }}</span></span>
                <span class="text-yellow-600">Pending: <span class="font-bold">{{ $premiumStats['pending'] }}</span></span>
                <span class="text-green-600">Won: <span class="font-bold">{{ $premiumStats['won'] }}</span></span>
                <span class="text-red-500">Lost: <span class="font-bold">{{ $premiumStats['lost'] }}</span></span>
            </div>
            <a href="{{ route('admin.betting-tips.create') }}"
               class="px-4 py-2 bg-yellow-400 text-black text-sm font-bold rounded-lg hover:bg-yellow-300 transition-colors whitespace-nowrap">
                + New VIP Tip
            </a>
        </div>
    </div>

    @if($premiumTips->isEmpty())
        <div class="px-6 py-10 text-center text-gray-400">
            <p class="text-base font-semibold mb-1">No VIP tips yet.</p>
            <p class="text-sm mb-3">Create a tip and check the "Premium" checkbox to add it here.</p>
            <a href="{{ route('admin.betting-tips.create') }}" class="text-yellow-600 text-sm hover:underline font-semibold">
                Create VIP tip →
            </a>
        </div>
    @else
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-yellow-50 text-gray-500 text-xs uppercase tracking-wider">
                <tr>
                    <th class="px-4 py-3 text-left">Match</th>
                    <th class="px-4 py-3 text-left">League</th>
                    <th class="px-4 py-3 text-left">Prediction</th>
                    <th class="px-4 py-3 text-left">Odds</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Match Time</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($premiumTips as $tip)
                <tr class="hover:bg-yellow-50/50 transition-colors">
                    <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">{{ $tip->matchup }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                        {{ $tip->league ?? '—' }}
                        @if($tip->country)
                            <span class="text-gray-400">· {{ $tip->country }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-700 font-medium">{{ $tip->prediction }}</td>
                    <td class="px-4 py-3 font-bold text-gray-900">{{ $tip->odds ? number_format($tip->odds, 2) : '—' }}</td>
                    <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $tip->match_time->format('M j, Y g:i A') }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-bold {{ $tip->admin_status_badge }}">
                            {{ ucfirst($tip->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.betting-tips.edit', $tip) }}"
                               class="px-3 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold transition-colors">
                                Edit
                            </a>
                            @if($tip->status === 'pending')
                            <form method="POST" action="{{ route('admin.betting-tips.mark-status', $tip) }}" class="inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="won">
                                <button type="submit" onclick="return confirm('Mark as Won?')"
                                        class="px-3 py-1 rounded-lg bg-green-100 hover:bg-green-200 text-green-700 text-xs font-bold transition-colors">
                                    Won
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.betting-tips.mark-status', $tip) }}" class="inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="lost">
                                <button type="submit" onclick="return confirm('Mark as Lost?')"
                                        class="px-3 py-1 rounded-lg bg-red-100 hover:bg-red-200 text-red-700 text-xs font-bold transition-colors">
                                    Lost
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        <div class="px-6 py-3 border-t border-gray-100 text-xs text-gray-400">
            Showing {{ $premiumTips->count() }} most recent VIP tips · sorted by match time
        </div>
    @endif
</div>

{{-- Recent Contact Messages --}}
<div class="bg-white rounded-xl border border-gray-200 mt-8">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-4 flex-wrap">
        <h2 class="font-semibold text-gray-900">Recent Contact Messages</h2>
        <div class="flex items-center gap-3">
            <form method="POST" action="{{ route('admin.contacts.send-tips') }}"
                  onsubmit="return confirm('Send today\'s tips to all subscribers?')">
                @csrf
                <button type="submit"
                        class="flex items-center gap-2 px-3 py-1.5 bg-green-600 text-white text-xs font-semibold rounded-lg hover:bg-green-700 transition-colors">
                    📧 Send Today's Tips
                </button>
            </form>
            <a href="{{ route('admin.contacts.index') }}"
               class="text-sm text-green-600 hover:underline">View all →</a>
        </div>
    </div>

    @if($recentMessages->isEmpty())
        <div class="px-6 py-10 text-center text-gray-400 text-sm">No messages yet.</div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($recentMessages as $msg)
            <a href="{{ route('admin.contacts.show', $msg) }}"
               class="flex items-start gap-4 px-6 py-4 hover:bg-gray-50 transition-colors">
                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="text-sm font-bold text-green-700">{{ strtoupper($msg->name[0]) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-semibold text-gray-900 {{ $msg->is_read ? '' : 'font-bold' }}">
                            {{ $msg->name }}
                        </p>
                        @if(!$msg->is_read)
                            <span class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0"></span>
                        @endif
                        <span class="text-xs text-gray-400 ml-auto flex-shrink-0">{{ $msg->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-xs font-medium text-gray-600 truncate">{{ $msg->subject }}</p>
                    <p class="text-xs text-gray-400 truncate mt-0.5">{{ $msg->message }}</p>
                </div>
            </a>
            @endforeach
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-progress]').forEach(function(el) {
        el.style.width = el.getAttribute('data-progress') + '%';
    });
</script>
@endpush
