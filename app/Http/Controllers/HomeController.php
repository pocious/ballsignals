<?php

namespace App\Http\Controllers;

use App\Models\BettingTip;
use App\Models\FootballNews;
use App\Services\LiveScoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request, LiveScoreService $scores): View
    {
        $selectedLeague = $request->query('league');
        $selectedSort   = $request->query('sort', 'time');

        $baseQuery = BettingTip::football()
            ->where('match_time', '>=', today()->startOfDay())
            ->where('match_time', '<=', today()->addDays(7)->endOfDay())
            ->where('is_premium', false)
            ->whereNotExists(fn ($q) => $q->from('betting_tips as bt2')
                ->whereColumn('bt2.home_team', 'betting_tips.home_team')
                ->whereColumn('bt2.away_team', 'betting_tips.away_team')
                ->whereColumn('bt2.match_time', 'betting_tips.match_time')
                ->where('bt2.is_premium', 1))
            ->forLeague($selectedLeague);

        $sortedQuery = match ($selectedSort) {
            'odds_asc'  => (clone $baseQuery)->orderByRaw('DATE(match_time) ASC, CASE WHEN match_time < NOW() THEN 1 ELSE 0 END ASC, odds ASC'),
            'odds_desc' => (clone $baseQuery)->orderByRaw('DATE(match_time) ASC, CASE WHEN match_time < NOW() THEN 1 ELSE 0 END ASC, odds DESC'),
            default     => (clone $baseQuery)->orderByRaw('DATE(match_time) ASC, CASE WHEN match_time < NOW() THEN 1 ELSE 0 END ASC, match_time ASC'),
        };

        // Free tips grouped by date
        $tipsByDate = $sortedQuery->get()
            ->groupBy(fn ($tip) => $tip->match_time->toDateString());

        // Premium upcoming tips — today + next 7 days, top 2 per day by confidence then odds
        $premiumTipsByDate = BettingTip::football()
            ->where('match_time', '>=', today()->startOfDay())
            ->where('match_time', '<=', today()->addDays(7)->endOfDay())
            ->where('is_premium', true)
            ->orderByRaw('DATE(match_time) ASC, match_time ASC')
            ->orderByDesc('confidence')
            ->orderByDesc('odds')
            ->get()
            ->groupBy(fn ($tip) => $tip->match_time->toDateString());

        // Whether the current visitor can see premium predictions (admin or active VIP session)
        $canSeePremium = Auth::check() && Auth::user()->role === 'admin';
        if (!$canSeePremium && session('vip_email') && session('vip_token')) {
            $canSeePremium = \App\Models\SubscriptionRequest::where('email', session('vip_email'))
                ->where('status', 'approved')
                ->where('expires_at', '>', now())
                ->where('session_token', session('vip_token'))
                ->exists();
        }

        // Leagues from all upcoming tips for filter bar
        $leagues = BettingTip::football()
            ->where('match_time', '>=', today()->startOfDay())
            ->where('match_time', '<=', today()->addDays(7)->endOfDay())
            ->whereNotNull('league')
            ->distinct()
            ->orderBy('league')
            ->pluck('league');

        // Next 5 upcoming free tips for the sample section
        $sampleTips = BettingTip::football()
            ->where('match_time', '>', today()->endOfDay())
            ->where('is_premium', false)
            ->orderBy('match_time')
            ->limit(5)
            ->get();

        $won     = BettingTip::football()->whereDate('match_time', today()->subDay())->where('status', 'won')->count();
        $lost    = BettingTip::football()->whereDate('match_time', today()->subDay())->where('status', 'lost')->count();
        $pending = BettingTip::football()->whereDate('match_time', today()->subDay())->where('status', 'pending')->count();
        $total   = $won + $lost;

        $stats = [
            'won'      => $won,
            'lost'     => $lost,
            'pending'  => $pending,
            'total'    => $total,
            'rate'     => $total > 0 ? round(($won / $total) * 100) : 0,
            'won_pct'  => $total > 0 ? round(($won  / $total) * 100) : 0,
            'lost_pct' => $total > 0 ? round(($lost / $total) * 100) : 0,
        ];

        $yesterdayTips = BettingTip::football()
            ->whereDate('match_time', today()->subDay())
            ->where('is_premium', false)
            ->whereIn('status', ['won', 'lost'])
            ->orderBy('match_time', 'asc')
            ->get();

        $premiumYesterdayTips = BettingTip::football()
            ->whereDate('match_time', today()->subDay())
            ->where('is_premium', true)
            ->whereIn('status', ['won', 'lost'])
            ->orderBy('match_time', 'asc')
            ->get();

        $basketballYesterdayTips = BettingTip::basketball()
            ->whereDate('match_time', today()->subDay())
            ->whereIn('status', ['won', 'lost'])
            ->orderBy('match_time', 'asc')
            ->get();

        // Basketball tips — next 7 days, free only, exclude matches that have a premium version
        $basketballTips = BettingTip::basketball()
            ->where('match_time', '>=', today()->startOfDay())
            ->where('match_time', '<=', today()->addDays(7)->endOfDay())
            ->where('is_premium', false)
            ->whereNotExists(fn ($q) => $q->from('betting_tips as bt2')
                ->whereColumn('bt2.home_team', 'betting_tips.home_team')
                ->whereColumn('bt2.away_team', 'betting_tips.away_team')
                ->whereColumn('bt2.match_time', 'betting_tips.match_time')
                ->where('bt2.is_premium', 1))
            ->orderByRaw('CASE WHEN match_time < NOW() THEN 1 ELSE 0 END ASC, match_time ASC')
            ->get();

        try {
            $worldTomorrowMatches = $scores->getScheduledMatches(today()->addDay()->toDateString());
        } catch (\Throwable) {
            $worldTomorrowMatches = [];
        }

        $latestNews = FootballNews::orderByDesc('published_at')->take(4)->get();

        return view('welcome', compact('tipsByDate', 'premiumTipsByDate', 'leagues', 'stats', 'selectedLeague', 'selectedSort', 'yesterdayTips', 'premiumYesterdayTips', 'basketballYesterdayTips', 'sampleTips', 'canSeePremium', 'basketballTips', 'worldTomorrowMatches', 'latestNews'));
    }
}
