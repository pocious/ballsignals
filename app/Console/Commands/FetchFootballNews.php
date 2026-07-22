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
        'BBC Sport'    => 'https://feeds.bbci.co.uk/sport/football/rss.xml',
        'Sky Sports'   => 'https://www.skysports.com/rss/12040',
        'The Guardian' => 'https://www.theguardian.com/football/rss',
        'ESPN FC'      => 'https://www.espn.com/espn/rss/soccer/news',
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
                    $guid    = trim((string) ($item->guid ?? $item->link ?? ''));
                    $title   = trim((string) ($item->title ?? ''));
                    $link    = trim((string) ($item->link ?? ''));
                    $desc    = trim(strip_tags((string) ($item->description ?? '')));
                    $pubDate = trim((string) ($item->pubDate ?? ''));

                    if (!$guid || !$title || !$link || strlen($link) > 255) continue;

                    $guid = md5($guid);

                    $strip4 = fn($s) => $s ? preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $s) : $s;
                    $title  = $strip4($title);
                    $desc   = $strip4($desc);

                    if (mb_strlen($desc) > 300) $desc = mb_substr($desc, 0, 297) . '…';

                    $publishedAt = null;
                    if ($pubDate) {
                        try { $publishedAt = new \DateTime($pubDate); } catch (\Exception) {}
                    }

                    // Extract image URL directly from RSS — stored as-is, no download
                    $image = null;
                    $ns    = $item->getNamespaces(true);

                    if (isset($ns['media'])) {
                        $item->registerXPathNamespace('media', $ns['media']);
                        $res = $item->xpath('media:thumbnail/@url');
                        if (!empty($res)) {
                            $image = str_replace('/standard/240/', '/standard/640/', (string) $res[0]);
                        }
                        if (!$image) {
                            $res = $item->xpath('media:content/@url');
                            if (!empty($res)) $image = (string) $res[0];
                        }
                    }
                    if (!$image) {
                        $enc = (string) ($item->enclosure['url'] ?? '');
                        if ($enc) $image = $enc;
                    }
                    if (!$image) {
                        preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', (string) ($item->description ?? ''), $m);
                        if (!empty($m[1])) $image = $m[1];
                    }

                    $existing = FootballNews::where('guid', $guid)
                        ->orWhere('url', $link)
                        ->first();

                    if ($existing) {
                        // Update image if article previously had none
                        if (!$existing->image && $image) {
                            $existing->update(['image' => $image]);
                        }
                        continue;
                    }

                    try {
                        FootballNews::create([
                            'guid'         => $guid,
                            'title'        => $title,
                            'description'  => $desc ?: null,
                            'url'          => $link,
                            'image'        => $image,
                            'source'       => $source,
                            'published_at' => $publishedAt,
                        ]);
                    } catch (\Exception) {
                        // Duplicate URL constraint — skip silently
                        continue;
                    }

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
