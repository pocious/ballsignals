<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->query('category');

        $posts = Blog::published()
            ->when($category, fn($q) => $q->where('category', $category))
            ->paginate(9);

        $categories = Blog::published()->distinct()->pluck('category');

        $latestNews = Blog::published()
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        return view('blog.index', compact('posts', 'categories', 'category', 'latestNews'));
    }

    public function show(string $slug)
    {
        $post = Blog::where('slug', $slug)->where('is_published', true)->firstOrFail();

        $related = Blog::published()
            ->where('id', '!=', $post->id)
            ->where('category', $post->category)
            ->take(3)
            ->get();

        return view('blog.show', compact('post', 'related'));
    }
}
