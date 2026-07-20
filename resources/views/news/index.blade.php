@extends('layouts.app')
@section('title', 'Football News — BallSignals')
@section('meta_description', 'Latest football news from BBC Sport, Sky Sports, The Guardian and ESPN FC — updated daily.')
@section('canonical', route('news.index'))

@section('content')

<div class="bg-gray-50 dark:bg-[#0a0f1a] border-b border-gray-200 dark:border-white/5">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
        <div class="flex items-center gap-3 mb-1">
            <div class="inline-flex items-center gap-1.5 bg-red-500/10 border border-red-500/30 text-red-500 dark:text-red-400 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-red-500 dark:bg-red-400 animate-pulse"></span>
                Live Feed
            </div>
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white">Football News</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Latest headlines from BBC Sport, Sky Sports, The Guardian & ESPN FC</p>
    </div>
</div>

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">

    @if($articles->isEmpty())
        <div class="text-center py-16">
            <p class="text-gray-400 text-sm">No news articles yet. Run <code class="bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded text-xs">php artisan news:fetch-football</code> to fetch them.</p>
        </div>
    @else

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($articles as $article)
            <a href="{{ $article->url }}" target="_blank" rel="noopener noreferrer"
               class="group flex flex-col bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700/50 overflow-hidden hover:border-green-400 dark:hover:border-green-500 hover:shadow-lg transition-all duration-200">

                {{-- Image --}}
                @if($article->image)
                    <div class="aspect-video overflow-hidden bg-gray-100 dark:bg-gray-800 flex-shrink-0">
                        <img src="{{ $article->image }}" alt="{{ $article->title }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                             loading="lazy" onerror="this.parentElement.style.display='none'">
                    </div>
                @else
                    <div class="aspect-video bg-gradient-to-br from-gray-800 to-gray-900 flex-shrink-0"></div>
                @endif

                {{-- Content --}}
                <div class="flex flex-col flex-1 p-4">
                    {{-- Source badge --}}
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-2 py-0.5 rounded-full">
                            {{ $article->source }}
                        </span>
                        @if($article->published_at)
                            <span class="text-[10px] text-gray-400 dark:text-gray-500 flex-shrink-0">
                                {{ $article->published_at->diffForHumans() }}
                            </span>
                        @endif
                    </div>

                    {{-- Title --}}
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white leading-snug mb-2 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors line-clamp-3">
                        {{ $article->title }}
                    </h2>

                    {{-- Description --}}
                    @if($article->description)
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed line-clamp-2 flex-1">
                            {{ $article->description }}
                        </p>
                    @endif

                    {{-- Read more --}}
                    <div class="mt-3 flex items-center gap-1 text-[11px] font-bold text-green-600 dark:text-green-400">
                        Read article
                        <svg class="w-3 h-3 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($articles->hasPages())
            <div class="mt-8">
                {{ $articles->links() }}
            </div>
        @endif

    @endif
</div>

@endsection
