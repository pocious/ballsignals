<?php

namespace App\Http\Controllers;

use App\Models\FootballNews;

class NewsController extends Controller
{
    public function index()
    {
        $selectedSource = request('source');

        $sources = FootballNews::whereNotNull('image')
            ->distinct()
            ->orderBy('source')
            ->pluck('source');

        $articles = FootballNews::whereNotNull('image')
            ->when($selectedSource, fn($q) => $q->where('source', $selectedSource))
            ->orderByDesc('published_at')
            ->paginate(18)->withQueryString();

        return view('news.index', compact('articles', 'sources', 'selectedSource'));
    }

    public function show(FootballNews $footballNews)
    {
        $related = FootballNews::whereNotNull('image')
            ->where('id', '!=', $footballNews->id)
            ->where('source', $footballNews->source)
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $otherSources = FootballNews::whereNotNull('image')
            ->where('id', '!=', $footballNews->id)
            ->where('source', '!=', $footballNews->source)
            ->orderByDesc('published_at')
            ->limit(6)
            ->get()
            ->groupBy('source');

        return view('news.show', compact('footballNews', 'related', 'otherSources'));
    }
}
