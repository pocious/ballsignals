<?php

namespace App\Console\Commands;

use App\Models\BettingTip;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SendTelegramTips extends Command
{
    protected $signature   = 'tips:telegram {--results : Post yesterday\'s won/lost results summary instead of today\'s tips}';
    protected $description = 'Post today\'s tips (or yesterday\'s results) to the Telegram channel';

    public function handle(): int
    {
        $token    = config('services.telegram.token');
        $channels = array_filter([
            config('services.telegram.channel'),
            config('services.telegram.channel2'),
        ]);

        if (!$token || empty($channels)) {
            $this->error('Set TELEGRAM_BOT_TOKEN and TELEGRAM_CHANNEL_ID in .env');
            return self::FAILURE;
        }

        $message = $this->option('results')
            ? $this->buildResultsMessage()
            : $this->buildTipsMessage();

        if (!$message) {
            $this->info('Nothing to post.');
            return self::SUCCESS;
        }

        $chunks = $this->splitMessage($message);

        foreach ($channels as $channel) {
            foreach ($chunks as $chunk) {
                $response = Http::withoutVerifying()->post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id'                  => $channel,
                    'text'                     => $chunk,
                    'parse_mode'               => 'HTML',
                    'disable_web_page_preview' => true,
                ]);

                if (!$response->successful()) {
                    $this->error("Telegram error [{$channel}]: " . $response->body());
                    return self::FAILURE;
                }

                if (count($chunks) > 1) {
                    sleep(1);
                }
            }
            $this->info("Posted to {$channel}");
        }

        return self::SUCCESS;
    }

    private function buildTipsMessage(): ?string
    {
        $freeTips = BettingTip::football()
            ->whereDate('match_time', today())
            ->where('is_premium', false)
            ->orderBy('match_time', 'desc')
            ->limit(10)
            ->get();

        $premiumTips = BettingTip::football()
            ->whereDate('match_time', today())
            ->where('is_premium', true)
            ->orderBy('match_time', 'desc')
            ->get();

        if ($freeTips->isEmpty() && $premiumTips->isEmpty()) {
            return null;
        }

        $date  = now()->format('l, j F Y');
        $lines = [$this->bold('BALLSIGNALS TIPS') . " — {$date}\n"];

        if ($freeTips->isNotEmpty()) {
            $lines[] = $this->bold('FREE FOOTBALL TIPS') . "\n";
            foreach ($freeTips as $i => $tip) {
                $num  = $i + 1;
                $time = $tip->match_time->format('g:i A');
                $odds = $tip->odds ? number_format($tip->odds, 2) : '—';

                $lines[] = $this->bold("{$num}. {$tip->home_team} vs {$tip->away_team}");
                $lines[] = $this->bold("   {$tip->league}  ·  {$time}");
                $lines[] = $this->bold("   {$tip->prediction}  |  Odds: {$odds}");
                $lines[] = '';
            }
        }

        $lines[] = '━━━━━━━━━━━━━━━━━━';

        if ($premiumTips->isNotEmpty()) {
            $combinedOdds = $premiumTips->reduce(fn ($c, $t) => $c * ($t->odds ?? 1.0), 1.0);
            $lines[] = $this->bold('VIP PREMIUM') . " — {$premiumTips->count()} exclusive picks today";
            $lines[] = 'Combined Odds: <b>' . number_format($combinedOdds, 2) . '</b>';
            $lines[] = 'Unlock access: ' . config('app.url');
        } else {
            $lines[] = $this->bold('VIP PREMIUM') . " picks coming soon.";
            $lines[] = config('app.url');
        }

        $lines[] = '';
        $lines[] = $this->bold('for more predictions: ') . config('app.url');
        $lines[] = '';
        $lines[] = '<i>18+ · Please bet responsibly</i>';

        return implode("\n", $lines);
    }

    private function bold(string $text): string
    {
        $upper  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lower  = 'abcdefghijklmnopqrstuvwxyz';
        $digits = '0123456789';
        $boldU  = ['𝐀','𝐁','𝐂','𝐃','𝐄','𝐅','𝐆','𝐇','𝐈','𝐉','𝐊','𝐋','𝐌','𝐍','𝐎','𝐏','𝐐','𝐑','𝐒','𝐓','𝐔','𝐕','𝐖','𝐗','𝐘','𝐙'];
        $boldL  = ['𝐚','𝐛','𝐜','𝐝','𝐞','𝐟','𝐠','𝐡','𝐢','𝐣','𝐤','𝐥','𝐦','𝐧','𝐨','𝐩','𝐪','𝐫','𝐬','𝐭','𝐮','𝐯','𝐰','𝐱','𝐲','𝐳'];
        $boldD  = ['𝟎','𝟏','𝟐','𝟑','𝟒','𝟓','𝟔','𝟕','𝟖','𝟗'];

        $result = '';
        foreach (mb_str_split($text) as $char) {
            $uPos = strpos($upper, $char);
            $lPos = strpos($lower, $char);
            $dPos = strpos($digits, $char);
            if ($uPos !== false) {
                $result .= $boldU[$uPos];
            } elseif ($lPos !== false) {
                $result .= $boldL[$lPos];
            } elseif ($dPos !== false) {
                $result .= $boldD[$dPos];
            } else {
                $result .= $char;
            }
        }
        return $result;
    }

    private function splitMessage(string $message, int $limit = 4000): array
    {
        if (strlen($message) <= $limit) {
            return [$message];
        }

        $chunks = [];
        $lines  = explode("\n", $message);
        $chunk  = '';

        foreach ($lines as $line) {
            $candidate = $chunk === '' ? $line : $chunk . "\n" . $line;
            if (strlen($candidate) > $limit) {
                if ($chunk !== '') {
                    $chunks[] = $chunk;
                }
                $chunk = $line;
            } else {
                $chunk = $candidate;
            }
        }

        if ($chunk !== '') {
            $chunks[] = $chunk;
        }

        return $chunks;
    }

    private function buildResultsMessage(): ?string
    {
        $tips = BettingTip::football()
            ->whereDate('match_time', today()->subDay())
            ->where('is_premium', false)
            ->whereIn('status', ['won', 'lost'])
            ->orderBy('match_time', 'asc')
            ->get();

        if ($tips->isEmpty()) {
            return null;
        }

        $date  = today()->subDay()->format('l, j F Y');
        $won   = $tips->where('status', 'won')->count();
        $lost  = $tips->where('status', 'lost')->count();
        $total = $tips->count();
        $pct   = round($won / $total * 100);

        $lines = [];
        $lines[] = $this->bold('BALLSIGNALS RESULTS') . " — {$date}";
        $lines[] = '━━━━━━━━━━━━━━━━━━';
        $lines[] = '';

        foreach ($tips as $tip) {
            $label = $tip->status === 'won' ? 'WON' : 'LOST';
            $odds  = $tip->odds ? '  @ ' . number_format($tip->odds, 2) : '';
            $lines[] = $this->bold("{$tip->home_team} vs {$tip->away_team}") . " — {$label}";
            $lines[] = '   ' . $tip->prediction . $odds;
            $lines[] = '';
        }

        $lines[] = '━━━━━━━━━━━━━━━━━━';
        $lines[] = $this->bold("RECORD: {$won}/{$total}") . " ({$pct}% win rate)";
        $lines[] = '';
        $lines[] = $this->bold('More tips: ') . config('app.url');
        $lines[] = '';
        $lines[] = '<i>18+ · Please bet responsibly</i>';

        return implode("\n", $lines);
    }
}
