<?php

namespace App\Console\Commands;

use App\Mail\DailyTipsNotification;
use App\Models\BettingTip;
use App\Models\ContactMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDailyTips extends Command
{
    protected $signature   = 'tips:send-daily';
    protected $description = 'Send today\'s free tips to all contact form subscribers';

    public function handle(): void
    {
        $tips = BettingTip::football()
            ->whereDate('match_time', today())
            ->where('is_premium', false)
            ->orderBy('match_time', 'asc')
            ->get();

        $subscribers = ContactMessage::select('email', 'name')
            ->distinct('email')
            ->get();

        if ($subscribers->isEmpty()) {
            $this->info('No subscribers found.');
            return;
        }

        $sent = 0;
        foreach ($subscribers as $subscriber) {
            Mail::to($subscriber->email)
                ->send(new DailyTipsNotification($tips));
            $sent++;
        }

        $this->info("Sent daily tips to {$sent} subscriber(s). Tips count: {$tips->count()}");
    }
}
