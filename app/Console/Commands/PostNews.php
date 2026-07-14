<?php

namespace App\Console\Commands;

use App\Models\Blog;
use Illuminate\Console\Command;

class PostNews extends Command
{
    protected $signature = 'news:post {--sport=football : football or basketball}';

    protected $description = 'Fetch trending sports news and auto-publish a weekly blog roundup';

    public function handle(): int
    {
        $sport  = $this->option('sport');
        $python = $this->findPython();

        if (!$python) {
            $this->error('Python 3 not found. Install it from https://python.org');
            return self::FAILURE;
        }

        $script = base_path('scraper/fetch_news.py');
        if (!file_exists($script)) {
            $this->error("News fetcher script not found: {$script}");
            return self::FAILURE;
        }

        $this->info("Fetching {$sport} news ...");

        $raw      = [];
        $exitCode = 0;
        exec('"' . $python . '" "' . $script . '" --sport ' . escapeshellarg($sport) . ' 2>&1', $raw, $exitCode);

        // Find the JSON line (stdout starts with '{')
        $jsonLine = '';
        foreach ($raw as $line) {
            if (str_starts_with(ltrim($line), '{')) {
                $jsonLine = $line;
                break;
            }
        }

        $data = $jsonLine ? json_decode($jsonLine, true) : null;

        if (!$data || isset($data['error'])) {
            $this->error('Failed to fetch news: ' . ($data['error'] ?? 'No JSON output'));
            foreach ($raw as $line) {
                $this->line('  ' . $line);
            }
            return self::FAILURE;
        }

        // Prevent duplicate post for the same day
        if (Blog::where('slug', $data['slug'])->exists()) {
            $this->warn("Post already published today: {$data['slug']}");
            return self::SUCCESS;
        }

        Blog::create([
            'title'        => $data['title'],
            'slug'         => $data['slug'],
            'category'     => $data['category'],
            'excerpt'      => $data['excerpt'],
            'content'      => $data['content'],
            'author'       => 'BallSignals',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->info("Published: {$data['title']}");
        return self::SUCCESS;
    }

    private function findPython(): ?string
    {
        foreach (['python', 'py', 'python3'] as $cmd) {
            exec('"' . $cmd . '" --version 2>&1', $out, $code);
            if ($code === 0 && !empty($out)) {
                return $cmd;
            }
        }
        return null;
    }
}
