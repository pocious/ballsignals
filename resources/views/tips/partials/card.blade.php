<div class="bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
    {{-- Sport Header --}}
    <div class="bg-gray-50 border-b border-gray-100 px-4 py-3 flex items-center justify-between">
        <span class="text-sm font-medium text-gray-600">
            {{ $tip->sport->icon }} {{ $tip->sport->name }}
        </span>
        <span class="text-xs px-2 py-1 rounded-full font-medium {{ $tip->status_badge }}">
            {{ ucfirst($tip->status) }}
        </span>
    </div>

    <div class="p-4">
        {{-- Teams --}}
        <p class="font-semibold text-gray-900 text-base">{{ $tip->matchup }}</p>
        @if($tip->league)
            <p class="text-xs text-gray-500 mt-0.5">{{ $tip->league }}</p>
        @endif

        {{-- Tip Detail --}}
        <div class="mt-3 flex items-center justify-between">
            <div>
                <span class="text-xs text-gray-500 uppercase tracking-wide">Pick</span>
                <p class="font-bold text-green-700 text-lg">{{ $tip->prediction }}</p>
                <p class="text-xs text-gray-500">{{ $tip->tip_type }}</p>
            </div>
            <div class="text-right">
                <span class="text-xs text-gray-500 uppercase tracking-wide">Odds</span>
                <p class="font-bold text-gray-900 text-xl">{{ $tip->odds }}</p>
            </div>
        </div>

        {{-- Confidence Stars --}}
        @if($tip->confidence)
        <div class="mt-3 flex items-center gap-1">
            @for($i = 1; $i <= 5; $i++)
                <span class="{{ $i <= $tip->confidence ? 'text-yellow-400' : 'text-gray-200' }} text-sm">★</span>
            @endfor
            <span class="text-xs text-gray-500 ml-1">Confidence</span>
        </div>
        @endif

        {{-- Match Date --}}
        <div class="mt-3 pt-3 border-t border-gray-100 text-xs text-gray-500 flex items-center justify-between">
            <span>📅 {{ $tip->match_date->format('M j, Y') }}</span>
            @if($tip->notes)
                <span class="text-green-600 font-medium cursor-default" title="{{ $tip->notes }}">+ Analyst note</span>
            @endif
        </div>
    </div>
</div>
