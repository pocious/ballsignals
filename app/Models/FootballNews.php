<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FootballNews extends Model
{
    protected $table = 'football_news';

    protected $fillable = [
        'guid', 'title', 'description', 'url', 'image', 'source', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];
}
