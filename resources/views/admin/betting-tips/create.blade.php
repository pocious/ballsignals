@extends('layouts.admin')

@section('title', 'New Betting Tip')
@section('heading', 'New Betting Tip')

@section('content')

<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-200 p-6">

        <form method="POST" action="{{ route('admin.betting-tips.store') }}" class="space-y-5">
            @csrf

            @include('admin.betting-tips.partials.form')

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold text-sm rounded-lg transition-colors">
                    Create Tip
                </button>
                <a href="{{ route('admin.betting-tips.index') }}"
                   class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-sm rounded-lg transition-colors">
                    Cancel
                </a>
            </div>
        </form>

    </div>
</div>

@endsection
