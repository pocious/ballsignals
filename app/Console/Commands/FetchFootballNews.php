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

        // Null out any previously stored remote URLs (hotlink-blocked on mobile)
        FootballNews::whereNotNull('image')
            ->where('image', 'not like', '/images/news/%')
            ->update(['image' => null]);

        // Null out references to local image files that no longer exist on disk
        foreach (FootballNews::where('image', 'like', '/images/news/%')->get(['id', 'image']) as $article) {
            if (!file_exists(public_path($article->image))) {
                $article->update(['image' => null]);
            }
        }

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

                    // Extract image from media:thumbnail, media:content, enclosure, or description img
                    $image = null;
                    $ns    = $item->getNamespaces(true);

                    if (isset($ns['media'])) {
                        // Use XPath — children() silently returns empty for self-closing elements with attrs
                        $item->registerXPathNamespace('media', $ns['media']);
                        $res = $item->xpath('media:thumbnail/@url');
                        if (!empty($res)) $image = str_replace('/standard/240/', '/standard/640/', (string) $res[0]);
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

                    $existing = FootballNews::where('guid', $guid)->first();

                    // Download image locally to avoid hotlink blocking
                    $localImage = null;
                    if ($image) {
                        $localImage = $this->downloadImage($image, $guid, $link);
                    }

                    if ($existing) {
                        // Update image if: no image in DB, or local file has gone missing
                        $fileMissing = $existing->image
                            && str_starts_with($existing->image, '/images/news/')
                            && !file_exists(public_path($existing->image));
                        if ($localImage && (!$existing->image || $fileMissing)) {
                            $existing->update(['image' => $localImage]);
                        }
                        continue;
                    }

                    FootballNews::create([
                        'guid'         => $guid,
                        'title'        => $title,
                        'description'  => $desc ?: null,
                        'url'          => $link,
                        'image'        => $localImage,
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

    private function downloadImage(string $url, string $guid, string $referer = ''): ?string
    {
        try {
            $dir = public_path('images/news');
            if (!is_dir($dir)) mkdir($dir, 0755, true);

            $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) $ext = 'jpg';

            $filename = $guid . '.' . $ext;
            $filepath = $dir . '/' . $filename;

            if (file_exists($filepath)) return '/images/news/' . $filename;

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT        => 8,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
                CURLOPT_REFERER        => $referer ?: $url,
                CURLOPT_HTTPHEADER     => ['Accept: image/avif,image/webp,image/apng,image/*,*/*;q=0.8'],
            ]);
            $data = curl_exec($ch);
            curl_close($ch); // @phpstan-ignore-line (deprecated in PHP 8.4 but still works)

            if ($data && strlen($data) > 1000) {
                file_put_contents($filepath, $data);
                return '/images/news/' . $filename;
            }
        } catch (\Exception) {}

        return null;
    }
}
