<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BettingTip;
use App\Models\ContactMessage;
use App\Models\SubscriptionRequest;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total'    => BettingTip::count(),
            'pending'  => BettingTip::where('status', 'pending')->count(),
            'won'      => BettingTip::where('status', 'won')->count(),
            'lost'     => BettingTip::where('status', 'lost')->count(),
            'unread'   => ContactMessage::where('is_read', false)->count(),
        ];

        $recent         = BettingTip::where(function ($q) {
            $q->where('is_premium', false)->orWhereNull('is_premium');
        })->latest()->take(5)->get();
        $recentMessages = ContactMessage::latest()->take(5)->get();

        $premiumTips  = BettingTip::where('is_premium', true)->orderBy('match_time')->take(10)->get();
        $premiumStats = [
            'total'   => BettingTip::where('is_premium', true)->count(),
            'pending' => BettingTip::where('is_premium', true)->where('status', 'pending')->count(),
            'won'     => BettingTip::where('is_premium', true)->where('status', 'won')->count(),
            'lost'    => BettingTip::where('is_premium', true)->where('status', 'lost')->count(),
        ];

        // Premium subscription data
        $allApproved    = SubscriptionRequest::where('status', 'approved')->get();
        $revenueUsd     = $allApproved->sum(fn ($s) => SubscriptionRequest::$plans[$s->plan]['amount_usd'] ?? 0);

        $subStats = [
            'active'        => SubscriptionRequest::where('status', 'approved')->where('expires_at', '>', now())->count(),
            'pending'       => SubscriptionRequest::where('status', 'pending')->count(),
            'expiring_soon' => SubscriptionRequest::where('status', 'approved')
                                ->whereBetween('expires_at', [now(), now()->addDays(3)])->count(),
            'total_revenue' => $revenueUsd,
        ];

        // New payments in last 24 h (for notification banner)
        $newPayments  = SubscriptionRequest::where('status', 'approved')
                            ->where('approved_at', '>=', now()->subHours(24))
                            ->latest('approved_at')->get();

        // Recent 10 approved subscribers
        $recentSubs   = SubscriptionRequest::where('status', 'approved')
                            ->latest('approved_at')->take(10)->get();

        // Pending (unpaid/manual) waiting
        $pendingSubs  = SubscriptionRequest::where('status', 'pending')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'stats', 'recent', 'recentMessages',
            'premiumTips', 'premiumStats',
            'subStats', 'newPayments', 'recentSubs', 'pendingSubs'
        ));
    }
}
