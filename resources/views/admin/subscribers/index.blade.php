@extends('layouts.admin')
@section('title', 'Subscribers')
@section('heading', 'Subscribers')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-sm text-gray-500">Total Subscribers</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $total }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-sm text-gray-500">New Today</p>
        <p class="text-3xl font-bold text-green-600 mt-1">{{ $recent }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5 sm:col-span-1 col-span-2">
        <p class="text-sm text-gray-500 mb-2">Send Tips to All</p>
        <form method="POST" action="{{ route('admin.contacts.send-tips') }}"
              onsubmit="return confirm('Send today\'s tips to all {{ $total }} subscriber(s)?')">
            @csrf
            <button type="submit"
                    class="w-full px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors">
                📧 Send Today's Tips Now
            </button>
        </form>
    </div>
</div>

{{-- Subscriber list --}}
<div class="bg-white rounded-xl border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="font-semibold text-gray-900">All Subscribers</h2>
        <span class="text-sm text-gray-400">{{ $subscribers->total() }} unique email{{ $subscribers->total() !== 1 ? 's' : '' }}</span>
    </div>

    @if($subscribers->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <div class="text-4xl mb-3">👥</div>
            <p class="font-medium">No subscribers yet.</p>
            <p class="text-sm mt-1">Anyone who subscribes via the Subscribe page will appear here.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Subscriber</th>
                        <th class="px-5 py-3 text-left">Email</th>
                        <th class="px-5 py-3 text-center">Messages</th>
                        <th class="px-5 py-3 text-left">First Contact</th>
                        <th class="px-5 py-3 text-left">Last Contact</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($subscribers as $sub)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                    <span class="text-xs font-bold text-green-700">{{ strtoupper($sub->name[0]) }}</span>
                                </div>
                                <span class="font-medium text-gray-900">{{ $sub->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <a href="mailto:{{ $sub->email }}"
                               class="text-green-600 hover:underline">{{ $sub->email }}</a>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-block w-7 h-7 bg-gray-100 rounded-full text-xs font-bold text-gray-700 leading-7 text-center">
                                {{ $sub->message_count }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-500 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($sub->first_contact)->format('d M Y') }}
                            <div class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($sub->first_contact)->diffForHumans() }}</div>
                        </td>
                        <td class="px-5 py-3 text-gray-500 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($sub->last_contact)->format('d M Y') }}
                            <div class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($sub->last_contact)->diffForHumans() }}</div>
                        </td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <a href="mailto:{{ $sub->email }}"
                               class="inline-block px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors mr-1">
                                Email
                            </a>
                            <form method="POST"
                                  action="{{ route('admin.subscribers.destroy', urlencode($sub->email)) }}"
                                  class="inline-block"
                                  onsubmit="return confirm('Remove {{ $sub->name }} ({{ $sub->email }}) from subscribers? All their messages will be deleted.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                                    Remove
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($subscribers->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
            <span>Showing {{ $subscribers->firstItem() }}–{{ $subscribers->lastItem() }} of {{ $subscribers->total() }}</span>
            <div class="flex gap-1">
                @if($subscribers->onFirstPage())
                    <span class="px-3 py-1 bg-gray-100 rounded-lg text-gray-400 cursor-not-allowed">← Prev</span>
                @else
                    <a href="{{ $subscribers->previousPageUrl() }}" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">← Prev</a>
                @endif
                @if($subscribers->hasMorePages())
                    <a href="{{ $subscribers->nextPageUrl() }}" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Next →</a>
                @else
                    <span class="px-3 py-1 bg-gray-100 rounded-lg text-gray-400 cursor-not-allowed">Next →</span>
                @endif
            </div>
        </div>
        @endif
    @endif
</div>

@endsection
