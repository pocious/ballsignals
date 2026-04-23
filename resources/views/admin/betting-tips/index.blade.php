@extends('layouts.admin')

@section('title', 'Betting Tips')
@section('heading', 'Betting Tips')

@section('content')

<div class="bg-white rounded-xl border border-gray-200">

    {{-- Table Header --}}
    <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <p class="text-sm text-gray-500">{{ $tips->total() }} tip{{ $tips->total() !== 1 ? 's' : '' }} total</p>
        </div>
        <a href="{{ route('admin.betting-tips.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
            <span>+</span> New Tip
        </a>
    </div>

    {{-- Table --}}
    @if($tips->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <div class="text-4xl mb-3">📋</div>
            <p class="font-medium">No betting tips yet.</p>
            <a href="{{ route('admin.betting-tips.create') }}"
               class="mt-3 inline-block text-green-600 text-sm hover:underline">
                Create your first tip →
            </a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Match</th>
                        <th class="px-5 py-3 text-left">League</th>
                        <th class="px-5 py-3 text-left">Prediction</th>
                        <th class="px-5 py-3 text-left">Odds</th>
                        <th class="px-5 py-3 text-left">Match Time</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Mark Result</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($tips as $tip)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3 font-medium text-gray-900 whitespace-nowrap">
                            {{ $tip->matchup }}
                        </td>
                        <td class="px-5 py-3 text-gray-500">{{ $tip->league ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-700 font-medium">{{ $tip->prediction }}</td>
                        <td class="px-5 py-3 font-bold text-gray-900">{{ $tip->odds }}</td>
                        <td class="px-5 py-3 text-gray-500 whitespace-nowrap">
                            {{ $tip->match_time->format('M j, Y') }}<br>
                            <span class="text-xs text-gray-400">{{ $tip->match_time->format('H:i') }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $tip->admin_status_badge }}">
                                {{ ucfirst($tip->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap">
                            {{-- Quick status buttons --}}
                            @if($tip->status !== 'won')
                            <form method="POST" action="{{ route('admin.betting-tips.mark-status', $tip) }}" class="inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="won">
                                <button type="submit" title="Mark Won"
                                        class="px-2.5 py-1 text-xs font-bold text-green-700 bg-green-100 hover:bg-green-200 rounded-lg transition-colors mr-1">
                                    ✓ Won
                                </button>
                            </form>
                            @endif
                            @if($tip->status !== 'lost')
                            <form method="POST" action="{{ route('admin.betting-tips.mark-status', $tip) }}" class="inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="lost">
                                <button type="submit" title="Mark Lost"
                                        class="px-2.5 py-1 text-xs font-bold text-red-700 bg-red-100 hover:bg-red-200 rounded-lg transition-colors mr-1">
                                    ✗ Lost
                                </button>
                            </form>
                            @endif
                            @if($tip->status !== 'pending')
                            <form method="POST" action="{{ route('admin.betting-tips.mark-status', $tip) }}" class="inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="pending">
                                <button type="submit" title="Reset to Pending"
                                        class="px-2.5 py-1 text-xs font-bold text-yellow-700 bg-yellow-100 hover:bg-yellow-200 rounded-lg transition-colors mr-1">
                                    ↺
                                </button>
                            </form>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.betting-tips.edit', $tip) }}"
                               class="inline-block px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors mr-1">
                                Edit
                            </a>
                            <form method="POST" action="{{ route('admin.betting-tips.destroy', $tip) }}"
                                  class="inline-block"
                                  onsubmit="return confirm('Delete this tip? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($tips->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $tips->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
