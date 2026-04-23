<?php

namespace App\Http\Controllers;

use App\Models\BettingTip;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $selectedLeague = $request->query('league');
        $selectedSort   = $request->query('sort', 'time');

        $baseQuery = BettingTip::football()
            ->where('match_time', '>=', today())
            ->forLeague($selectedLeague);

        $sortedQuery = match ($selectedSort) {
            'odds_asc'  => (clone $baseQuery)->orderBy('odds', 'asc'),
            'odds_desc' => (clone $baseQuery)->orderBy('odds', 'desc'),
            default     => (clone $baseQuery)->orderBy('match_time', 'asc'),
        };

        $allTips = $sortedQuery->get();

        // Free tips grouped by date
        $tipsByDate = $allTips->where('is_premium', false)
            ->groupBy(fn ($tip) => $tip->match_time->toDateString());

        // Premium upcoming tips — always all leagues, unaffected by the league filter
        $premiumBaseQuery = BettingTip::football()->where('match_time', '>=', today())->where('is_premium', true);
        $premiumSortedQuery = match ($selectedSort) {
            'odds_asc'  => (clone $premiumBaseQuery)->orderBy('odds', 'asc'),
            'odds_desc' => (clone $premiumBaseQuery)->orderBy('odds', 'desc'),
            default     => (clone $premiumBaseQuery)->orderBy('match_time', 'asc'),
        };
        $premiumTipsByDate = $premiumSortedQuery->get()->groupBy(fn ($tip) => $tip->match_time->toDateString());

        // Leagues from all upcoming tips for filter bar
        $leagues = BettingTip::football()
            ->where('match_time', '>=', today())
            ->whereNotNull('league')
            ->distinct()
            ->orderBy('league')
            ->pluck('league');

        $won     = BettingTip::football()->where('status', 'won')->count();
        $lost    = BettingTip::football()->where('status', 'lost')->count();
        $pending = BettingTip::football()->where('status', 'pending')->count();
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
            ->orderBy('match_time', 'asc')
            ->get();

        $premiumYesterdayTips = BettingTip::football()
            ->whereDate('match_time', today()->subDay())
            ->where('is_premium', true)
            ->orderBy('match_time', 'asc')
            ->get();

        return view('welcome', compact('tipsByDate', 'premiumTipsByDate', 'leagues', 'stats', 'selectedLeague', 'selectedSort', 'yesterdayTips', 'premiumYesterdayTips'));
    }
}
