@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')

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
                    <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $tip->match_time->format('M j, Y H:i') }}</td>
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
                    <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $tip->match_time->format('M j, Y H:i') }}</td>
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
