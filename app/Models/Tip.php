<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tip extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sport_id',
        'match_date',
        'home_team',
        'away_team',
        'league',
        'prediction',
        'tip_type',
        'odds',
        'confidence',
        'status',
        'notes',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'match_date'   => 'date',
            'odds'         => 'decimal:2',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'confidence'   => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('match_date', $date);
    }

    public function getMatchupAttribute(): string
    {
        return "{$this->home_team} vs {$this->away_team}";
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'won'     => 'text-green-600',
            'lost'    => 'text-red-600',
            'void'    => 'text-gray-500',
            default   => 'text-yellow-600',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'won'     => 'bg-green-100 text-green-800',
            'lost'    => 'bg-red-100 text-red-800',
            'void'    => 'bg-gray-100 text-gray-600',
            default   => 'bg-yellow-100 text-yellow-800',
        };
    }
}
