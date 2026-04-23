@extends('layouts.admin')

@section('title', 'Edit Betting Tip')
@section('heading', 'Edit Betting Tip')

@section('content')

<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-200 p-6">

        {{-- Match Header --}}
        <div class="mb-6 pb-5 border-b border-gray-100">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Editing</p>
            <h2 class="text-lg font-bold text-gray-900">{{ $bettingTip->matchup }}</h2>
            @if($bettingTip->league)
                <p class="text-sm text-gray-500 mt-0.5">{{ $bettingTip->league }}</p>
            @endif
        </div>

        <form method="POST" action="{{ route('admin.betting-tips.update', $bettingTip) }}" class="space-y-5">
            @csrf
            @method('PUT')

            @include('admin.betting-tips.partials.form')

            <div class="flex items-center justify-between pt-2">
                <div class="flex items-center gap-3">
                    <button type="submit"
                            class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold text-sm rounded-lg transition-colors">
                        Save Changes
                    </button>
                    <a href="{{ route('admin.betting-tips.index') }}"
                       class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-sm rounded-lg transition-colors">
                        Cancel
                    </a>
                </div>

                {{-- Danger Zone --}}
                <form method="POST" action="{{ route('admin.betting-tips.destroy', $bettingTip) }}"
                      onsubmit="return confirm('Permanently delete this tip?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2.5 text-red-600 hover:bg-red-50 font-medium text-sm rounded-lg transition-colors border border-red-200">
                        Delete Tip
                    </button>
                </form>
            </div>
        </form>

    </div>
</div>

@endsection
