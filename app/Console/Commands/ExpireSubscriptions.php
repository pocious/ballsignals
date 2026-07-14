<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('subscriptions:expire')]
#[Description('Mark approved subscriptions as expired when their expiry date has passed')]
class ExpireSubscriptions extends Command
{
    public function handle(): void
    {
        $count = \App\Models\SubscriptionRequest::where('status', 'approved')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);

        $this->info("Marked {$count} subscription(s) as expired.");
    }
}
