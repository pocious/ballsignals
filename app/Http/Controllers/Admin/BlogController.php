<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index()
    {
        $posts = Blog::latest()->paginate(15);
        return view('admin.blogs.index', compact('posts'));
    }

    public function create()
    {
        return view('admin.blogs.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:200'],
            'category'     => ['required', 'string', 'in:' . implode(',', Blog::$categories)],
            'excerpt'      => ['nullable', 'string', 'max:300'],
            'content'      => ['required', 'string'],
            'author'       => ['nullable', 'string', 'max:100'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $data['slug']         = Str::slug($data['title']);
        $data['author']       = $data['author'] ?? 'BallSignals';
        $data['is_published'] = $request->boolean('is_published');
        $data['published_at'] = $data['is_published'] ? now() : null;

        Blog::create($data);

        return redirect()->route('admin.blogs.index')->with('success', 'Post published successfully.');
    }

    public function edit(Blog $blog)
    {
        return view('admin.blogs.edit', compact('blog'));
    }

    public function update(Request $request, Blog $blog)
    {
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:200'],
            'category'     => ['required', 'string', 'in:' . implode(',', Blog::$categories)],
            'excerpt'      => ['nullable', 'string', 'max:300'],
            'content'      => ['required', 'string'],
            'author'       => ['nullable', 'string', 'max:100'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $data['slug']         = Str::slug($data['title']);
        $data['author']       = $data['author'] ?? 'BallSignals';
        $data['is_published'] = $request->boolean('is_published');
        if ($data['is_published'] && !$blog->published_at) {
            $data['published_at'] = now();
        }

        $blog->update($data);

        return redirect()->route('admin.blogs.index')->with('success', 'Post updated successfully.');
    }

    public function destroy(Blog $blog)
    {
        $blog->delete();
        return redirect()->route('admin.blogs.index')->with('success', 'Post deleted.');
    }
}
