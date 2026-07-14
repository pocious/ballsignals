<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class LiveScoreService
{
    private array $headers = [
        'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Accept'          => 'application/json',
        'Accept-Language' => 'en-US,en;q=0.9',
        'Referer'         => 'https://www.sofascore.com/',
        'Origin'          => 'https://www.sofascore.com',
    ];

    public function getLiveMatches(): array
    {
        return Cache::remember('world_live_scores', 60, function () {
            try {
                $response = Http::withHeaders($this->headers)
                    ->timeout(8)
                    ->get('https://api.sofascore.com/api/v1/sport/football/events/live');

                if (! $response->ok()) {
                    return [];
                }

                return $this->parseEvents($response->json('events', []), ['inprogress']);
            } catch (\Throwable) {
                return [];
            }
        });
    }

    public function getScheduledMatches(string $dateStr): array
    {
        return Cache::remember("world_scheduled_{$dateStr}", 900, function () use ($dateStr) {
            try {
                $all = [];

                // Sofascore paginates scheduled events by page
                for ($page = 0; $page <= 3; $page++) {
                    $response = Http::withHeaders($this->headers)
                        ->timeout(8)
                        ->get("https://api.sofascore.com/api/v1/sport/football/scheduled-events/{$dateStr}/{$page}");

                    if (! $response->ok()) {
                        break;
                    }

                    $events = $response->json('events', []);
                    if (empty($events)) {
                        break;
                    }

                    $all = array_merge($all, $events);
                }

                return $this->parseEvents($all, ['not_started', 'inprogress']);
            } catch (\Throwable) {
                return [];
            }
        });
    }

    private function parseEvents(array $events, array $allowedTypes): array
    {
        $matches = [];

        foreach ($events as $event) {
            $type = $event['status']['type'] ?? '';

            if (! in_array($type, $allowedTypes)) {
                continue;
            }

            $home = $event['homeTeam']['shortName'] ?? $event['homeTeam']['name'] ?? '';
            $away = $event['awayTeam']['shortName'] ?? $event['awayTeam']['name'] ?? '';

            if (! $home || ! $away) {
                continue;
            }

            $league   = $event['tournament']['name'] ?? '';
            $category = $event['tournament']['category']['name'] ?? '';
            $label    = $category ? "{$category} · {$league}" : $league;

            $ts        = $event['startTimestamp'] ?? null;
            $localTime = $ts
                ? Carbon::createFromTimestamp($ts)->setTimezone(config('app.timezone'))->format('g:i A')
                : null;

            $homeScore = $event['homeScore']['display'] ?? $event['homeScore']['current'] ?? null;
            $awayScore = $event['awayScore']['display'] ?? $event['awayScore']['current'] ?? null;

            // Live minute
            $minute = '';
            if ($type === 'inprogress') {
                $played = $event['time']['played'] ?? null;
                $minute = $played !== null ? "{$played}'" : ($event['status']['description'] ?? 'Live');
            }

            $matches[] = [
                'league'     => $label,
                'home_team'  => $home,
                'away_team'  => $away,
                'home_score' => $homeScore,
                'away_score' => $awayScore,
                'time'       => $localTime,
                'minute'     => $minute,
                'state'      => $type,
            ];
        }

        usort($matches, fn ($a, $b) => strcmp($a['time'] ?? '', $b['time'] ?? ''));

        return $matches;
    }
}
