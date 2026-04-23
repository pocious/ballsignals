@extends('layouts.admin')
@section('title', 'Subscription Requests')
@section('heading', 'Subscription Requests')

@section('content')

{{-- Filter Tabs --}}
<div class="flex items-center gap-1 mb-5 flex-wrap">
    @foreach(['all' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'expired' => 'Expired'] as $key => $label)
    <a href="{{ request()->fullUrlWithQuery(['status' => $key, 'page' => 1]) }}"
       class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
              {{ $status === $key ? 'bg-green-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
        {{ $label }}
        <span class="ml-1 text-xs {{ $status === $key ? 'text-green-200' : 'text-gray-400' }}">{{ $counts[$key] }}</span>
    </a>
    @endforeach
</div>

<div class="bg-white rounded-xl border border-gray-200">

    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-4 flex-wrap">
        <h2 class="font-semibold text-gray-900">
            {{ ucfirst($status === 'all' ? 'All' : $status) }} Requests
        </h2>
        @if($counts['pending'] > 0)
            <span class="bg-yellow-100 text-yellow-700 border border-yellow-300 text-xs font-bold px-2.5 py-1 rounded-full">
                {{ $counts['pending'] }} pending review
            </span>
        @endif
    </div>

    @if($requests->isEmpty())
        <div class="px-6 py-16 text-center text-gray-400">
            <p class="text-4xl mb-3">📋</p>
            <p class="font-medium">No {{ $status === 'all' ? '' : $status }} requests yet.</p>
        </div>
    @else
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                <tr>
                    <th class="px-4 py-3 text-left">Name / Email</th>
                    <th class="px-4 py-3 text-left">Plan</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Submitted</th>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Expires</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($requests as $req)
                <tr class="hover:bg-gray-50 transition-colors {{ $req->status === 'pending' ? 'bg-yellow-50/40' : '' }}">
                    <td class="px-4 py-3">
                        <p class="font-semibold text-gray-900">{{ $req->name }}</p>
                        <p class="text-xs text-gray-400">{{ $req->email }}</p>
                        @if($req->phone)
                            <p class="text-xs text-gray-400">{{ $req->phone }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $req->plan_label }}</p>
                        <p class="text-xs text-gray-400">{{ $req->plan_price }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $req->status_badge }}">
                            {{ ucfirst($req->isExpired() && $req->status === 'approved' ? 'expired' : $req->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                        {{ $req->created_at->format('d M Y') }}
                    </td>
                    <td class="px-4 py-3 text-xs whitespace-nowrap">
                        @if($req->expires_at)
                            <span class="{{ $req->isExpired() ? 'text-red-500' : 'text-gray-500' }}">
                                {{ $req->expires_at->format('d M Y') }}
                            </span>
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.subscription-requests.show', $req) }}"
                               class="px-3 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold transition-colors">
                                View
                            </a>
                            @if($req->status === 'pending')
                            <form method="POST" action="{{ route('admin.subscription-requests.approve', $req) }}"
                                  onsubmit="return confirm('Approve this request?')">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="px-3 py-1 rounded-lg bg-green-100 hover:bg-green-200 text-green-700 text-xs font-bold transition-colors">
                                    Approve
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.subscription-requests.reject', $req) }}"
                                  onsubmit="return confirm('Reject this request?')">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="px-3 py-1 rounded-lg bg-red-100 hover:bg-red-200 text-red-700 text-xs font-bold transition-colors">
                                    Reject
                                </button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('admin.subscription-requests.destroy', $req) }}"
                                  onsubmit="return confirm('Delete this request?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="px-3 py-1 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 text-xs transition-colors">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>

        @if($requests->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
            <span>Showing {{ $requests->firstItem() }}–{{ $requests->lastItem() }} of {{ $requests->total() }}</span>
            <div class="flex gap-1">
                @if($requests->onFirstPage())
                    <span class="px-3 py-1 bg-gray-100 rounded-lg text-gray-400 cursor-not-allowed">← Prev</span>
                @else
                    <a href="{{ $requests->previousPageUrl() }}" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">← Prev</a>
                @endif
                @if($requests->hasMorePages())
                    <a href="{{ $requests->nextPageUrl() }}" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Next →</a>
                @else
                    <span class="px-3 py-1 bg-gray-100 rounded-lg text-gray-400 cursor-not-allowed">Next →</span>
                @endif
            </div>
        </div>
        @endif
    @endif

</div>

@endsection
