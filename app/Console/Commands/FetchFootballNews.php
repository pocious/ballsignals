<?php

namespace App\Console\Commands;

use App\Models\FootballNews;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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

    // Images live in storage/app/public/news/ and are served via public/storage symlink
    private const IMG_DIR = 'news';
    private const IMG_URL_PREFIX = '/storage/news/';

    public function handle(): int
    {
        $inserted = 0;

        // Ensure storage directory exists
        $dir = storage_path('app/public/' . self::IMG_DIR);
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        // Migrate any old /images/news/ paths to /storage/news/
        DB::table('football_news')
            ->where('image', 'like', '/images/news/%')
            ->update(['image' => DB::raw("REPLACE(image, '/images/news/', '/storage/news/')")]);

        // Null out any remote URLs (not local storage paths)
        FootballNews::whereNotNull('image')
            ->where('image', 'not like', self::IMG_URL_PREFIX . '%')
            ->update(['image' => null]);

        // Null out references to local files that no longer exist on disk
        foreach (FootballNews::where('image', 'like', self::IMG_URL_PREFIX . '%')->get(['id', 'image']) as $article) {
            if (!file_exists($this->storagePath($article->image))) {
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

                    // Extract image URL from RSS item
                    $image = null;
                    $ns    = $item->getNamespaces(true);

                    if (isset($ns['media'])) {
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

                    $existing   = FootballNews::where('guid', $guid)->first();
                    $localImage = $image ? $this->downloadImage($image, $guid, $link) : null;

                    if ($existing) {
                        $fileMissing = $existing->image
                            && str_starts_with($existing->image, self::IMG_URL_PREFIX)
                            && !file_exists($this->storagePath($existing->image));
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

    private function storagePath(string $imageUrl): string
    {
        // /storage/news/xxx.jpg → storage/app/public/news/xxx.jpg
        return storage_path('app/public' . substr($imageUrl, strlen('/storage')));
    }

    private function downloadImage(string $url, string $guid, string $referer = ''): ?string
    {
        try {
            $dir = storage_path('app/public/' . self::IMG_DIR);
            if (!is_dir($dir)) mkdir($dir, 0755, true);

            $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) $ext = 'jpg';

            $filename = $guid . '.' . $ext;
            $filepath = $dir . '/' . $filename;

            if (file_exists($filepath)) return self::IMG_URL_PREFIX . $filename;

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
            curl_close($ch); // @phpstan-ignore-line

            if ($data && strlen($data) > 1000 && file_put_contents($filepath, $data) !== false) {
                return self::IMG_URL_PREFIX . $filename;
            }
        } catch (\Exception) {}

        return null;
    }
}
