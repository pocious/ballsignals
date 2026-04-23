<?php

namespace App\Http\Controllers;

use App\Models\Blog;

class SitemapController extends Controller
{
    public function index()
    {
        $posts = Blog::published()->orderByDesc('published_at')->get(['slug', 'updated_at']);

        return response()
            ->view('sitemap', compact('posts'))
            ->header('Content-Type', 'application/xml');
    }
}
