@extends('layouts.admin')
@section('title', 'Blog Posts')

@section('heading', 'Blog Posts')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div></div>
    <a href="{{ route('admin.blogs.create') }}"
       class="px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors">
        + New Post
    </a>
</div>

<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    @if($posts->isEmpty())
        <div class="py-16 text-center text-gray-400">No blog posts yet.</div>
    @else
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Title</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Category</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Status</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Date</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($posts as $post)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3 font-medium text-gray-900 max-w-xs truncate">{{ $post->title }}</td>
                    <td class="px-5 py-3 text-gray-500">{{ $post->category }}</td>
                    <td class="px-5 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                            {{ $post->is_published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $post->is_published ? 'Published' : 'Draft' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-gray-400 text-xs">{{ $post->created_at->format('d M Y') }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2 justify-end">
                            <a href="{{ route('blog.show', $post->slug) }}" target="_blank"
                               class="text-xs text-blue-500 hover:underline">View</a>
                            <a href="{{ route('admin.blogs.edit', $post) }}"
                               class="text-xs text-green-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.blogs.destroy', $post) }}"
                                  onsubmit="return confirm('Delete this post?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:underline">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($posts->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">{{ $posts->links() }}</div>
        @endif
        </div>{{-- end overflow-x-auto --}}
    @endif
</div>
@endsection
