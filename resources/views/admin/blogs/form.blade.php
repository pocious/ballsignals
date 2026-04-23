<div class="space-y-5">

    {{-- Title --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Title <span class="text-red-500">*</span></label>
        <input type="text" name="title" required
               value="{{ old('title', $blog->title ?? '') }}"
               placeholder="e.g. How to pick Over 2.5 Goals bets"
               class="w-full px-3 py-2.5 rounded-lg border @error('title') border-red-400 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        @error('title')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    {{-- Category + Author --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Category <span class="text-red-500">*</span></label>
            <select name="category" required
                    class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500">
                @foreach(\App\Models\Blog::$categories as $cat)
                    <option value="{{ $cat }}" {{ old('category', $blog->category ?? '') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
            @error('category')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Author</label>
            <input type="text" name="author"
                   value="{{ old('author', $blog->author ?? 'BallSignals') }}"
                   class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
    </div>

    {{-- Excerpt --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Excerpt <span class="text-gray-400 text-xs">(short summary)</span></label>
        <textarea name="excerpt" rows="2" maxlength="300" placeholder="Brief description shown on blog listing..."
                  class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 resize-none">{{ old('excerpt', $blog->excerpt ?? '') }}</textarea>
        @error('excerpt')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    {{-- Content --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Content <span class="text-red-500">*</span></label>
        <textarea name="content" rows="14" required placeholder="Write your post here..."
                  class="w-full px-3 py-2.5 rounded-lg border @error('content') border-red-400 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-green-500 resize-y font-mono">{{ old('content', $blog->content ?? '') }}</textarea>
        @error('content')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    {{-- Publish toggle --}}
    <div class="flex items-center gap-2 pt-1">
        <input type="checkbox" name="is_published" id="is_published" value="1"
               {{ old('is_published', $blog->is_published ?? false) ? 'checked' : '' }}
               class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
        <label for="is_published" class="text-sm font-medium text-gray-700">Publish immediately</label>
    </div>

</div>
