<?php

namespace App\Console\Commands;

use App\Models\FootballNews;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchFootballNews extends Command
{
    protected $signature   = 'news:fetch-football';
    protected $description = 'Fetch football news from RSS feeds and cache in DB';

    private const FEEDS = [
        'BBC Sport'       => 'https://feeds.bbci.co.uk/sport/football/rss.xml',
        'Sky Sports'      => 'https://www.skysports.com/rss/12040',
        'The Guardian'    => 'https://www.theguardian.com/football/rss',
        'ESPN FC'         => 'https://www.espn.com/espn/rss/soccer/news',
    ];

    public function handle(): int
    {
        $inserted = 0;

        foreach (self::FEEDS as $source => $url) {
            try {
                $resp = Http::withoutVerifying()
                    ->timeout(10)
                    ->withHeaders(['User-Agent' => 'BallSignals/1.0 (RSS reader)'])
                    ->get($url);

                if (!$resp->successful()) {
                    $this->warn("  [{$source}] HTTP {$resp->status()}");
                    continue;
                }

                $xml = @simplexml_load_string($resp->body());
                if (!$xml) {
                    $this->warn("  [{$source}] Invalid XML");
                    continue;
                }

                $items = $xml->channel->item ?? [];
                $count = 0;

                foreach ($items as $item) {
                    $guid  = trim((string) ($item->guid ?? $item->link ?? ''));
                    $title = trim((string) ($item->title ?? ''));
                    $link  = trim((string) ($item->link ?? ''));
                    $desc  = trim(strip_tags((string) ($item->description ?? '')));
                    $pubDate = trim((string) ($item->pubDate ?? ''));

                    if (!$guid || !$title || !$link || strlen($link) > 255) continue;

                    // Hash guid so it always fits varchar(255)
                    $guid = md5($guid);

                    // Strip 4-byte UTF-8 chars (emoji etc.) that MySQL latin1/utf8mb3 rejects
                    $strip4 = fn($s) => $s ? preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $s) : $s;
                    $title = $strip4($title);
                    $desc  = $strip4($desc);

                    // Limit description length
                    if (mb_strlen($desc) > 300) $desc = mb_substr($desc, 0, 297) . '…';

                    // Parse date
                    $publishedAt = null;
                    if ($pubDate) {
                        try { $publishedAt = new \DateTime($pubDate); } catch (\Exception) {}
                    }

                    // Extract image from media:content, enclosure, or description
                    $image = null;
                    $ns    = $item->getNamespaces(true);

                    if (isset($ns['media'])) {
                        $media = $item->children($ns['media']);
                        $image = (string) ($media->content['url'] ?? $media->thumbnail['url'] ?? '');
                    }
                    if (!$image && isset($ns['enclosure'])) {
                        $image = (string) ($item->enclosure['url'] ?? '');
                    }
                    if (!$image) {
                        // Try to extract from description HTML
                        preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', (string) ($item->description ?? ''), $m);
                        $image = $m[1] ?? null;
                    }

                    if (FootballNews::where('guid', $guid)->exists()) continue;

                    FootballNews::create([
                        'guid'         => $guid,
                        'title'        => $title,
                        'description'  => $desc ?: null,
                        'url'          => $link,
                        'image'        => $image ?: null,
                        'source'       => $source,
                        'published_at' => $publishedAt,
                    ]);

                    $count++;
                    $inserted++;
                }

                $this->info("  [{$source}] +{$count} articles");

            } catch (\Exception $e) {
                $this->warn("  [{$source}] Error: {$e->getMessage()}");
            }
        }

        // Keep only the latest 200 articles
        $oldest = FootballNews::orderByDesc('published_at')->skip(200)->first();
        if ($oldest) {
            FootballNews::where('published_at', '<', $oldest->published_at)->delete();
        }

        $this->info("\nDone — {$inserted} new articles stored.");
        return self::SUCCESS;
    }
}
