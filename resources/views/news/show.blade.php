@extends('layouts.app')
@section('title', $footballNews->title . ' — BallSignals')
@section('meta_description', $footballNews->description ?? $footballNews->title)
@section('canonical', route('news.show', $footballNews))

@section('content')

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-6">

    {{-- Back link --}}
    <a href="{{ route('news.index') }}"
       class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors mb-6">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
        </svg>
        All News
    </a>

    {{-- Article card --}}
    <article class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700/50 overflow-hidden shadow-sm">

        {{-- Hero image --}}
        @if($footballNews->image)
            <div class="aspect-video overflow-hidden bg-gray-100 dark:bg-gray-800">
                <img src="{{ $footballNews->image }}" alt="{{ $footballNews->title }}"
                     class="w-full h-full object-cover"
                     referrerpolicy="no-referrer" onerror="this.parentElement.style.background='linear-gradient(135deg,#1a3a2e,#0f2010)';this.style.display='none'">
            </div>
        @endif

        <div class="p-5 sm:p-7">

            {{-- Source + date --}}
            <div class="flex items-center gap-3 mb-4">
                <span class="text-[10px] font-bold uppercase tracking-widest text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-2.5 py-1 rounded-full">
                    {{ $footballNews->source }}
                </span>
                @if($footballNews->published_at)
                    <span class="text-xs text-gray-400 dark:text-gray-500">
                        {{ $footballNews->published_at->diffForHumans() }}
                        &middot;
                        {{ $footballNews->published_at->format('d M Y') }}
                    </span>
                @endif
            </div>

            {{-- Title --}}
            <h1 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white leading-snug mb-4">
                {{ $footballNews->title }}
            </h1>

            {{-- Description --}}
            @if($footballNews->description)
                <p class="text-sm sm:text-base text-gray-600 dark:text-gray-300 leading-relaxed mb-6">
                    {{ $footballNews->description }}
                </p>
            @endif

            {{-- Divider --}}
            <div class="border-t border-gray-100 dark:border-gray-800 pt-5">
                <p class="text-xs text-gray-400 dark:text-gray-500 mb-4">
                    This is a summary from <span class="font-semibold text-gray-600 dark:text-gray-300">{{ $footballNews->source }}</span>.
                    Read the full story on their website.
                </p>
                <a href="{{ $footballNews->url }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-500 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-colors">
                    Read full article on {{ $footballNews->source }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            </div>

        </div>
    </article>

    {{-- Related articles from same source --}}
    @if($related->isNotEmpty())
    <div class="mt-8">
        <h2 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-4">
            More from {{ $footballNews->source }}
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @foreach($related as $item)
            <a href="{{ route('news.show', $item) }}"
               class="group flex flex-col bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700/50 overflow-hidden hover:border-green-400 dark:hover:border-green-500 transition-all duration-200">
                @if($item->image)
                    <div class="aspect-video overflow-hidden bg-gray-100 dark:bg-gray-800 flex-shrink-0">
                        <img src="{{ $item->image }}" alt="{{ $item->title }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                             loading="lazy" referrerpolicy="no-referrer" onerror="this.parentElement.style.background='linear-gradient(135deg,#1a3a2e,#0f2010)';this.style.display='none'">
                    </div>
                @endif
                <div class="p-3">
                    <p class="text-xs font-bold text-gray-800 dark:text-white leading-snug group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors line-clamp-2">
                        {{ $item->title }}
                    </p>
                    @if($item->published_at)
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1.5">{{ $item->published_at->diffForHumans() }}</p>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Articles from other sources --}}
    @foreach($otherSources as $source => $items)
    <div class="mt-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                From {{ $source }}
            </h2>
            <a href="{{ route('news.index', ['source' => $source]) }}"
               class="text-xs font-semibold text-green-600 dark:text-green-400 hover:underline flex items-center gap-1">
                See all
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($items as $item)
            <a href="{{ route('news.show', $item) }}"
               class="group flex gap-3 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700/50 overflow-hidden hover:border-green-400 dark:hover:border-green-500 transition-all duration-200 p-3">
                @if($item->image)
                    <div class="w-20 h-16 flex-shrink-0 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800">
                        <img src="{{ $item->image }}" alt="{{ $item->title }}"
                             class="w-full h-full object-cover"
                             loading="lazy" referrerpolicy="no-referrer" onerror="this.parentElement.style.background='linear-gradient(135deg,#1a3a2e,#0f2010)';this.style.display='none'">
                    </div>
                @endif
                <div class="flex flex-col justify-center min-w-0">
                    <p class="text-xs font-bold text-gray-800 dark:text-white leading-snug group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors line-clamp-2">
                        {{ $item->title }}
                    </p>
                    @if($item->published_at)
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1">{{ $item->published_at->diffForHumans() }}</p>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endforeach

</div>

@endsection
