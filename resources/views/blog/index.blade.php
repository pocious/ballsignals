@extends('layouts.app')
@section('title', 'Football Betting Tips Blog — BallSignals')
@section('meta_description', 'Football betting analysis, match previews, strategy guides, and league news from the BallSignals expert team. Data-driven insights to sharpen your predictions.')
@section('canonical', route('blog.index'))

@section('content')

{{-- ── Latest News Hero ── --}}
@if($latestNews->isNotEmpty())
<div class="bg-gray-100 dark:bg-[#0a0f1a] border-b border-gray-200 dark:border-white/5 mb-0">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">

        <div class="flex items-center gap-3 mb-5">
            <div class="inline-flex items-center gap-1.5 bg-red-500/10 border border-red-500/30 text-red-500 dark:text-red-400 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-red-500 dark:bg-red-400 animate-pulse"></span>
                Latest News
            </div>
            <span class="text-xs text-gray-500 dark:text-gray-500">Fresh from our analysts</span>
        </div>

        @php $featured = $latestNews->first(); $sideNews = $latestNews->skip(1); @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            {{-- Featured large card --}}
            <a href="{{ route('blog.show', $featured->slug) }}"
               class="lg:col-span-2 group relative bg-white dark:bg-gradient-to-br dark:from-gray-900 dark:to-[#0d1420] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden hover:border-green-500/40 transition-all duration-200 flex flex-col shadow-sm dark:shadow-none">
                <div class="h-1 bg-gradient-to-r from-green-500 to-emerald-400"></div>
                <div class="p-6 flex flex-col flex-1">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 px-2 py-0.5 rounded-full">
                            {{ $featured->category }}
                        </span>
                        <span class="text-[10px] text-gray-400">{{ $featured->published_at?->diffForHumans() ?? $featured->created_at->diffForHumans() }}</span>
                    </div>
                    <h2 class="text-lg font-black text-gray-900 dark:text-white leading-snug mb-3 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors">
                        {{ $featured->title }}
                    </h2>
                    @if($featured->excerpt)
                        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed line-clamp-3 flex-1">{{ $featured->excerpt }}</p>
                    @endif
                    <div class="flex items-center justify-between mt-5 pt-4 border-t border-gray-100 dark:border-white/5">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center">
                                <span class="text-[9px] font-black text-black">{{ strtoupper(substr($featured->author, 0, 1)) }}</span>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $featured->author }}</span>
                        </div>
                        <span class="text-xs font-semibold text-green-600 dark:text-green-400 group-hover:underline">Read more →</span>
                    </div>
                </div>
            </a>

            {{-- Side news list --}}
            <div class="flex flex-col gap-3">
                @foreach($sideNews as $news)
                <a href="{{ route('blog.show', $news->slug) }}"
                   class="group flex-1 bg-white dark:bg-gray-900/60 rounded-2xl border border-gray-200 dark:border-gray-700/50 overflow-hidden hover:border-green-500/40 dark:hover:border-green-500/30 transition-all duration-200 flex flex-col shadow-sm dark:shadow-none">
                    <div class="h-0.5 bg-gradient-to-r from-green-500/60 to-transparent"></div>
                    <div class="p-4 flex flex-col flex-1">
                        <div class="flex items-center gap-1.5 mb-2">
                            <span class="text-[9px] font-bold uppercase tracking-widest text-green-600 dark:text-green-400/80 bg-green-50 dark:bg-green-500/10 px-1.5 py-0.5 rounded-full">
                                {{ $news->category }}
                            </span>
                            <span class="text-[9px] text-gray-400 dark:text-gray-600">{{ $news->published_at?->diffForHumans() ?? $news->created_at->diffForHumans() }}</span>
                        </div>
                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 leading-snug group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors line-clamp-2 flex-1">
                            {{ $news->title }}
                        </h3>
                        <div class="flex items-center gap-1.5 mt-3">
                            <div class="w-4 h-4 rounded-full bg-green-600 flex items-center justify-center">
                                <span class="text-[8px] font-black text-black">{{ strtoupper(substr($news->author, 0, 1)) }}</span>
                            </div>
                            <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $news->author }}</span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>

        </div>
    </div>
</div>
@endif

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">BallSignals Blog</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Betting analysis, football insights, and strategy guides.</p>
    </div>

    {{-- Category filter --}}
    @if($categories->isNotEmpty())
    <div class="flex items-center gap-2 flex-wrap mb-6">
        <a href="{{ route('blog.index') }}"
           class="px-3 py-1.5 rounded-full text-xs font-semibold transition-colors
                  {{ !$category ? 'bg-green-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
            All
        </a>
        @foreach($categories as $cat)
        <a href="{{ route('blog.index', ['category' => $cat]) }}"
           class="px-3 py-1.5 rounded-full text-xs font-semibold transition-colors
                  {{ $category === $cat ? 'bg-green-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
            {{ $cat }}
        </a>
        @endforeach
    </div>
    @endif

    {{-- Posts grid --}}
    @if($posts->isEmpty())
        <div class="text-center py-20 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800">
            <p class="text-4xl mb-3">✍️</p>
            <p class="font-semibold text-gray-700 dark:text-gray-300">No posts yet</p>
            <p class="text-sm text-gray-400 mt-1">Check back soon for betting tips and analysis.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($posts as $post)
            <a href="{{ route('blog.show', $post->slug) }}"
               class="group bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden hover:border-green-500/40 dark:hover:border-green-500/40 hover:shadow-lg hover:shadow-green-500/5 transition-all duration-200">
                {{-- Category banner --}}
                <div class="h-1.5 bg-gradient-to-r from-green-500 to-green-400"></div>
                <div class="p-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/30 px-2 py-0.5 rounded-full">
                            {{ $post->category }}
                        </span>
                        <span class="text-[10px] text-gray-400 dark:text-gray-600">{{ $post->read_time }}</span>
                    </div>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white leading-snug mb-2 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors line-clamp-2">
                        {{ $post->title }}
                    </h2>
                    @if($post->excerpt)
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed line-clamp-3 mb-4">{{ $post->excerpt }}</p>
                    @endif
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <div class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center">
                                <span class="text-[8px] font-black text-black">{{ strtoupper(substr($post->author, 0, 1)) }}</span>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $post->author }}</span>
                        </div>
                        <span class="text-xs text-gray-400 dark:text-gray-600">
                            {{ $post->published_at?->format('d M Y') ?? $post->created_at->format('d M Y') }}
                        </span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($posts->hasPages())
            <div class="mt-8">{{ $posts->links() }}</div>
        @endif
    @endif

</div>
@endsection
