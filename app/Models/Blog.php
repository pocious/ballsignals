<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'category', 'excerpt', 'content',
        'author', 'is_published', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public static array $categories = [
        'Betting Tips', 'Analysis', 'League News',
        'Strategy', 'Odds Guide', 'General',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->orderByDesc('published_at');
    }

    public function getReadTimeAttribute(): string
    {
        $words = str_word_count(strip_tags($this->content));
        return max(1, (int) ceil($words / 200)) . ' min read';
    }
}
