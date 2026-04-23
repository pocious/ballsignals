@extends('layouts.app')
@section('title', $post->title . ' — BallSignals')
@section('meta_description', $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 155))
@section('canonical', route('blog.show', $post->slug))
@section('og_type', 'article')
@push('schema')
@php
$__articleSchema = json_encode([
    '@context'      => 'https://schema.org',
    '@type'         => 'Article',
    'headline'      => $post->title,
    'description'   => $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 155),
    'author'        => ['@type' => 'Person', 'name' => $post->author],
    'publisher'     => ['@type' => 'Organization', 'name' => 'BallSignals', 'url' => url('/')],
    'datePublished' => ($post->published_at ?? $post->created_at)->toIso8601String(),
    'dateModified'  => $post->updated_at->toIso8601String(),
    'url'           => route('blog.show', $post->slug),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp
<script type="application/ld+json">{!! $__articleSchema !!}</script>
@endpush

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-8">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-400 dark:text-gray-600 mb-6">
        <a href="{{ route('home') }}" class="hover:text-green-500 transition-colors">Home</a>
        <span>/</span>
        <a href="{{ route('blog.index') }}" class="hover:text-green-500 transition-colors">Blog</a>
        <span>/</span>
        <span class="text-gray-600 dark:text-gray-400 truncate">{{ $post->title }}</span>
    </div>

    {{-- Post header --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-3">
            <span class="text-xs font-bold uppercase tracking-widest text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/30 px-2.5 py-1 rounded-full">
                {{ $post->category }}
            </span>
            <span class="text-xs text-gray-400">{{ $post->read_time }}</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white leading-tight mb-4">
            {{ $post->title }}
        </h1>
        @if($post->excerpt)
            <p class="text-base text-gray-500 dark:text-gray-400 leading-relaxed border-l-2 border-green-500 pl-4">
                {{ $post->excerpt }}
            </p>
        @endif
        <div class="flex items-center gap-3 mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
            <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                <span class="text-xs font-black text-black">{{ strtoupper(substr($post->author, 0, 1)) }}</span>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $post->author }}</p>
                <p class="text-xs text-gray-400">{{ $post->published_at?->format('l, d M Y') ?? $post->created_at->format('l, d M Y') }}</p>
            </div>
        </div>
    </div>

    {{-- Post content --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 mb-8">
        <div class="prose prose-sm dark:prose-invert max-w-none
                    prose-headings:font-bold prose-headings:text-gray-900 dark:prose-headings:text-white
                    prose-p:text-gray-600 dark:prose-p:text-gray-400 prose-p:leading-relaxed
                    prose-a:text-green-600 dark:prose-a:text-green-400 prose-a:no-underline hover:prose-a:underline
                    prose-strong:text-gray-800 dark:prose-strong:text-gray-200
                    prose-li:text-gray-600 dark:prose-li:text-gray-400">
            {!! nl2br(e($post->content)) !!}
        </div>
    </div>

    {{-- Telegram CTA --}}
    <a href="https://t.me/ballsigtips" target="_blank" rel="noopener noreferrer"
       class="flex items-center justify-between gap-4 px-5 py-4 rounded-2xl mb-8
              bg-[#229ED9]/10 border border-[#229ED9]/30 hover:bg-[#229ED9]/20 transition-all duration-200 group">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#229ED9] flex items-center justify-center flex-shrink-0 shadow-lg shadow-[#229ED9]/30">
                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.248l-1.97 9.289c-.145.658-.537.818-1.084.508l-3-2.21-1.447 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.12L7.48 14.056l-2.95-.924c-.64-.203-.653-.64.136-.954l11.57-4.461c.537-.194 1.006.131.326.531z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-gray-900 dark:text-white">Get daily tips on Telegram</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Join @ballsigtips for free picks & premium signals</p>
            </div>
        </div>
        <svg class="w-4 h-4 text-[#229ED9] flex-shrink-0 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </a>

    {{-- Related posts --}}
    @if($related->isNotEmpty())
    <div>
        <h3 class="text-sm font-bold uppercase tracking-widest text-gray-400 dark:text-gray-600 mb-4">Related Posts</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @foreach($related as $r)
            <a href="{{ route('blog.show', $r->slug) }}"
               class="group bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 hover:border-green-500/40 transition-all duration-200">
                <span class="text-[10px] font-bold uppercase text-green-600 dark:text-green-400">{{ $r->category }}</span>
                <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 mt-1 line-clamp-2 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors">{{ $r->title }}</p>
                <p class="text-[10px] text-gray-400 mt-2">{{ $r->read_time }}</p>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
