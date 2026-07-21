<?php

namespace App\Http\Controllers;

use App\Models\FootballNews;

class NewsController extends Controller
{
    public function index()
    {
        $articles = FootballNews::whereNotNull('image')
            ->orderByDesc('published_at')
            ->paginate(18);

        return view('news.index', compact('articles'));
    }

    public function show(FootballNews $footballNews)
    {
        $related = FootballNews::whereNotNull('image')
            ->where('id', '!=', $footballNews->id)
            ->where('source', $footballNews->source)
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('news.show', compact('footballNews', 'related'));
    }
}
