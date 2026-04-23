@extends('layouts.admin')
@section('title', 'Contact Messages')
@section('heading', 'Contact Messages')

@section('content')

<div class="bg-white rounded-xl border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-3">
            <h2 class="font-semibold text-gray-900">All Messages</h2>
            @php $unread = $messages->where('is_read', false)->count(); @endphp
            @if($unread > 0)
                <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $unread }} unread</span>
            @endif
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-400">{{ $messages->total() }} subscribers</span>
            <form method="POST" action="{{ route('admin.contacts.send-tips') }}"
                  onsubmit="return confirm('Send today\'s tips to all {{ $messages->total() }} subscriber(s)?')">
                @csrf
                <button type="submit"
                        class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors">
                    <span>📧</span> Send Today's Tips Now
                </button>
            </form>
        </div>
    </div>

    @if($messages->isEmpty())
        <div class="px-6 py-16 text-center text-gray-400">
            <p class="text-4xl mb-3">✉️</p>
            <p class="font-medium">No messages yet.</p>
        </div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($messages as $msg)
            <div class="flex flex-col sm:flex-row sm:items-start gap-3 px-4 sm:px-6 py-4 hover:bg-gray-50 transition-colors {{ $msg->is_read ? '' : 'bg-blue-50/40' }}">
                <div class="flex items-start gap-3 flex-1 min-w-0">
                    <div class="w-9 h-9 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <span class="text-sm font-bold text-green-700">{{ strtoupper($msg->name[0]) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-sm font-semibold text-gray-900">{{ $msg->name }}</span>
                            <span class="text-xs text-gray-400">{{ $msg->email }}</span>
                            @if(!$msg->is_read)
                                <span class="text-[10px] font-bold text-white bg-red-500 px-1.5 py-0.5 rounded uppercase">New</span>
                            @endif
                            <span class="text-xs text-gray-400 ml-auto">{{ $msg->created_at->format('d M Y, g:i A') }}</span>
                        </div>
                        <p class="text-sm font-medium text-gray-700 mt-0.5">{{ $msg->subject }}</p>
                        <p class="text-xs text-gray-400 truncate mt-0.5">{{ $msg->message }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 sm:flex-shrink-0 sm:ml-2">
                    <a href="{{ route('admin.contacts.show', $msg) }}"
                       class="flex-1 sm:flex-none text-center px-3 py-1.5 text-xs font-medium bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        Read
                    </a>
                    <form method="POST" action="{{ route('admin.contacts.destroy', $msg) }}"
                          onsubmit="return confirm('Delete this message?')" class="flex-1 sm:flex-none">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full px-3 py-1.5 text-xs font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition-colors">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        @if($messages->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
            <span>Showing {{ $messages->firstItem() }}–{{ $messages->lastItem() }} of {{ $messages->total() }}</span>
            <div class="flex gap-1">
                @if($messages->onFirstPage())
                    <span class="px-3 py-1 bg-gray-100 rounded-lg text-gray-400 cursor-not-allowed">← Prev</span>
                @else
                    <a href="{{ $messages->previousPageUrl() }}" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">← Prev</a>
                @endif
                @if($messages->hasMorePages())
                    <a href="{{ $messages->nextPageUrl() }}" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Next →</a>
                @else
                    <span class="px-3 py-1 bg-gray-100 rounded-lg text-gray-400 cursor-not-allowed">Next →</span>
                @endif
            </div>
        </div>
        @endif
    @endif
</div>

@endsection
