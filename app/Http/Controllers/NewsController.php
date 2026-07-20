<?php

namespace App\Http\Controllers;

use App\Models\FootballNews;

class NewsController extends Controller
{
    public function index()
    {
        $articles = FootballNews::orderByDesc('published_at')
            ->paginate(18);

        return view('news.index', compact('articles'));
    }
}
