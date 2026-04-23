@extends('layouts.admin')
@section('title', 'Message from ' . $contact->name)
@section('heading', 'Contact Message')

@section('content')

<div class="max-w-2xl">

    <a href="{{ route('admin.contacts.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 mb-6 transition-colors">
        ← Back to Messages
    </a>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        {{-- Header --}}
        <div class="px-4 sm:px-6 py-5 border-b border-gray-100 flex flex-wrap items-start justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                    <span class="text-base font-bold text-green-700">{{ strtoupper($contact->name[0]) }}</span>
                </div>
                <div>
                    <p class="font-semibold text-gray-900">{{ $contact->name }}</p>
                    <a href="mailto:{{ $contact->email }}"
                       class="text-sm text-green-600 hover:underline">{{ $contact->email }}</a>
                </div>
            </div>
            <span class="text-xs text-gray-400 flex-shrink-0 mt-1">{{ $contact->created_at->format('d M Y, g:i A') }}</span>
        </div>

        {{-- Subject --}}
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">Subject</p>
            <p class="text-sm font-semibold text-gray-900">{{ $contact->subject }}</p>
        </div>

        {{-- Message body --}}
        <div class="px-6 py-5">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Message</p>
            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $contact->message }}</p>
        </div>

        {{-- Actions --}}
        <div class="px-4 sm:px-6 py-4 border-t border-gray-100 flex flex-wrap items-center gap-3">
            <a href="mailto:{{ $contact->email }}?subject=Re: {{ $contact->subject }}"
               class="px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors">
                Reply via Email
            </a>
            <form method="POST" action="{{ route('admin.contacts.destroy', $contact) }}"
                  onsubmit="return confirm('Delete this message permanently?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="px-4 py-2 text-sm font-semibold text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition-colors">
                    Delete Message
                </button>
            </form>
        </div>

    </div>
</div>

@endsection
