<?php

namespace App\Http\Controllers;

use App\Models\BettingTip;
use App\Services\LiveScoreService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LiveMatchesController extends Controller
{
    public function index(LiveScoreService $scores): View
    {
        $liveTips = BettingTip::where('status', 'pending')
            ->where('match_time', '<=', now())
            ->where('match_time', '>=', now()->subMinutes(115))
            ->where('is_premium', false)
            ->orderBy('match_time', 'asc')
            ->get();

        $liveVipTips = BettingTip::where('status', 'pending')
            ->where('match_time', '<=', now())
            ->where('match_time', '>=', now()->subMinutes(115))
            ->where('is_premium', true)
            ->orderBy('match_time', 'asc')
            ->get();

        $upcomingTips = BettingTip::where('status', 'pending')
            ->where('match_time', '>', now())
            ->where('match_time', '<=', now()->addHours(2))
            ->where('is_premium', false)
            ->orderBy('match_time', 'asc')
            ->get();

        $canSeePremium = Auth::check() && Auth::user()->role === 'admin';
        if (! $canSeePremium && session('vip_email') && session('vip_token')) {
            $canSeePremium = \App\Models\SubscriptionRequest::where('email', session('vip_email'))
                ->where('status', 'approved')
                ->where('expires_at', '>', now())
                ->where('session_token', session('vip_token'))
                ->exists();
        }

        try {
            $worldLiveMatches = $scores->getLiveMatches();
        } catch (\Throwable) {
            $worldLiveMatches = [];
        }

        return view('live-matches', compact(
            'liveTips', 'liveVipTips', 'upcomingTips', 'canSeePremium', 'worldLiveMatches'
        ));
    }
}
