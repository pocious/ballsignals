<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BettingTip;

class SitemapController extends Controller
{
    public function index()
    {
        $posts = Blog::published()->orderByDesc('published_at')->get(['slug', 'updated_at']);

        $tips = BettingTip::where('is_premium', false)
            ->orderByDesc('match_time')
            ->get(['id', 'match_time', 'updated_at']);

        return response()
            ->view('sitemap', compact('posts', 'tips'))
            ->header('Content-Type', 'application/xml');
    }
}
