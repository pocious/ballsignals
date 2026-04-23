@extends('layouts.admin')
@section('title', 'Edit Blog Post')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.blogs.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <h1 class="text-xl font-bold text-gray-900">Edit Post</h1>
</div>

<div class="bg-white rounded-2xl border border-gray-200 p-6">
    <form method="POST" action="{{ route('admin.blogs.update', $blog) }}" class="space-y-5">
        @csrf @method('PUT')
        @include('admin.blogs.form')
        <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
            <a href="{{ route('admin.blogs.index') }}"
               class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="px-5 py-2 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors">
                Update Post
            </button>
        </div>
    </form>
</div>
@endsection
