<?php

namespace App\Console\Commands;

use App\Models\SubscriptionRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class RenewSubscriptions extends Command
{
    protected $signature   = 'subscriptions:renew';
    protected $description = 'Send renewal reminder emails to subscribers whose plans are expiring soon';

    public function handle(): void
    {
        $reminded = 0;
        $expired  = 0;

        // ── 1. Remind subscribers expiring in 1–3 days ────────────────────────
        $expiringSoon = SubscriptionRequest::where('status', 'approved')
            ->whereBetween('expires_at', [now(), now()->addDays(3)])
            ->whereNull('renewal_notified_at')
            ->get();

        foreach ($expiringSoon as $sub) {
            $renewUrl = $this->renewUrl($sub);
            $daysLeft = (int) now()->diffInDays($sub->expires_at, false);

            try {
                Mail::send('emails.renewal-reminder', [
                    'sub'      => $sub,
                    'renewUrl' => $renewUrl,
                    'daysLeft' => max(1, $daysLeft),
                ], function ($m) use ($sub, $daysLeft) {
                    $urgency = $daysLeft <= 1 ? '⚠️ Last chance — ' : '';
                    $m->to($sub->email, $sub->name)
                      ->subject("{$urgency}Your BallSignals VIP subscription expires in {$daysLeft} day(s)");
                });

                $sub->update(['renewal_notified_at' => now()]);
                $reminded++;
                $this->line("  Reminded: {$sub->email} (expires in {$daysLeft} day(s))");
            } catch (\Exception $e) {
                $this->error("  Failed to email {$sub->email}: {$e->getMessage()}");
            }
        }

        // ── 2. Notify newly expired subscribers ───────────────────────────────
        $justExpired = SubscriptionRequest::where('status', 'approved')
            ->where('expires_at', '<', now())
            ->whereNull('renewal_notified_at')
            ->get();

        foreach ($justExpired as $sub) {
            $renewUrl = $this->renewUrl($sub);

            try {
                Mail::send('emails.subscription-expired', [
                    'sub'      => $sub,
                    'renewUrl' => $renewUrl,
                ], function ($m) use ($sub) {
                    $m->to($sub->email, $sub->name)
                      ->subject('Your BallSignals VIP access has expired — renew now');
                });

                // Mark expired + notified
                $sub->update([
                    'status'              => 'expired',
                    'renewal_notified_at' => now(),
                ]);
                $expired++;
                $this->line("  Expired + notified: {$sub->email}");
            } catch (\Exception $e) {
                $sub->update(['status' => 'expired']);
                $this->error("  Failed to email {$sub->email}: {$e->getMessage()}");
            }
        }

        $this->info("Renewal reminders sent: {$reminded} | Expired notifications: {$expired}");
    }

    private function renewUrl(SubscriptionRequest $sub): string
    {
        // Signed URL valid for 7 days — opens the premium page with modal pre-filled
        return URL::temporarySignedRoute('premium.renew', now()->addDays(7), [
            'email' => $sub->email,
            'plan'  => $sub->plan,
            'name'  => $sub->name,
            'phone' => $sub->phone ?? '',
        ]);
    }
}
