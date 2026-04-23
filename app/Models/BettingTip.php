<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BettingTip extends Model
{
    use HasFactory;

    protected $fillable = [
        'sport',
        'home_team',
        'away_team',
        'country',
        'league',
        'prediction',   // "1", "X", or "2"
        'confidence',   // 1–5
        'odds',
        'home_odds',
        'draw_odds',
        'away_odds',
        'match_time',
        'status',
        'is_premium',
    ];

    protected function casts(): array
    {
        return [
            'match_time' => 'datetime',
            'is_premium' => 'boolean',
            'confidence' => 'integer',
            'odds'       => 'decimal:2',
            'home_odds'  => 'decimal:2',
            'draw_odds'  => 'decimal:2',
            'away_odds'  => 'decimal:2',
        ];
    }

    public static array $predictions = [
        'Home Win', 'Draw', 'Away Win',
        'Over 0.5', 'Over 1.5', 'Over 2.5', 'Over 3.5',
        'Under 0.5', 'Under 1.5', 'Under 2.5', 'Under 3.5',
        'BTTS - Yes', 'BTTS - No',
        'Double Chance 1X', 'Double Chance X2', 'Double Chance 12',
        'HT Home Win', 'HT Draw', 'HT Away Win',
        'Over 3.5 Corners', 'Over 9.5 Corners', 'Over 1.5 Cards',
    ];

    public static array $sports = [
        'Football', 'Basketball', 'Tennis', 'Baseball',
        'Hockey', 'Rugby', 'Cricket', 'Volleyball',
    ];

    public static array $sportIcons = [
        'Football'   => '⚽',
        'Basketball' => '🏀',
        'Tennis'     => '🎾',
        'Baseball'   => '⚾',
        'Hockey'     => '🏒',
        'Rugby'      => '🏉',
        'Cricket'    => '🏏',
        'Volleyball' => '🏐',
    ];

    public function scopeFootball($query)
    {
        return $query->where('sport', 'Football');
    }

    public function scopeForLeague($query, ?string $league)
    {
        return $league ? $query->where('league', $league) : $query;
    }

    public function scopeForCountry($query, ?string $country)
    {
        return $country ? $query->where('country', $country) : $query;
    }

    public function getMatchupAttribute(): string
    {
        return "{$this->home_team} vs {$this->away_team}";
    }

    public function getSportIconAttribute(): string
    {
        return static::$sportIcons[$this->sport] ?? '🏆';
    }

    public function getLeaguePathAttribute(): string
    {
        $parts = array_filter(['Football', $this->country, $this->league]);
        return implode(' / ', $parts);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'won'   => 'bg-green-500 text-white',
            'lost'  => 'bg-red-500 text-white',
            default => 'bg-yellow-400/20 text-yellow-700 dark:text-yellow-300 border border-yellow-400/50 shadow-sm shadow-yellow-400/20 animate-pulse',
        };
    }

    public function getAdminStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'won'   => 'bg-green-500 text-white',
            'lost'  => 'bg-red-500 text-white',
            default => 'bg-yellow-400/20 text-yellow-700 border border-yellow-400/50',
        };
    }
}
