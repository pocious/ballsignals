<?php

namespace App\Http\Controllers;

use App\Models\BettingTip;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FootballController extends Controller
{
    public function index(Request $request): View
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
            'odds_asc'  => (clone $baseQuery)->orderBy('odds', 'asc'),
            'odds_desc' => (clone $baseQuery)->orderBy('odds', 'desc'),
            default     => (clone $baseQuery)->orderByRaw('DATE(match_time) ASC, match_time ASC'),
        };

        $tipsByDate = $sortedQuery->get()
            ->groupBy(fn ($tip) => $tip->match_time->toDateString());

        $leagues = BettingTip::football()
            ->where('match_time', '>=', today()->startOfDay())
            ->where('match_time', '<=', today()->addDays(7)->endOfDay())
            ->whereNotNull('league')
            ->distinct()
            ->orderBy('league')
            ->pluck('league');

        $won   = BettingTip::football()->whereDate('match_time', today()->subDay())->where('status', 'won')->count();
        $lost  = BettingTip::football()->whereDate('match_time', today()->subDay())->where('status', 'lost')->count();
        $total = $won + $lost;

        $stats = [
            'won'   => $won,
            'lost'  => $lost,
            'total' => $total,
            'rate'  => $total > 0 ? round(($won / $total) * 100) : 0,
        ];

        $yesterdayTips = BettingTip::football()
            ->whereDate('match_time', today()->subDay())
            ->whereIn('status', ['won', 'lost'])
            ->orderBy('match_time')
            ->get();

        $premiumTips = BettingTip::football()
            ->where('match_time', '>=', today()->startOfDay())
            ->where('match_time', '<=', today()->addDays(7)->endOfDay())
            ->where('is_premium', true)
            ->orderBy('match_time')
            ->get()
            ->groupBy(fn ($tip) => $tip->match_time->toDateString());

        $canSeePremium = auth()->check() && auth()->user()->role === 'admin';
        if (!$canSeePremium && session('vip_email') && session('vip_token')) {
            $canSeePremium = \App\Models\SubscriptionRequest::where('email', session('vip_email'))
                ->where('status', 'approved')
                ->where('expires_at', '>', now())
                ->where('session_token', session('vip_token'))
                ->exists();
        }

        return view('football', compact('tipsByDate', 'leagues', 'stats', 'selectedLeague', 'selectedSort', 'yesterdayTips', 'premiumTips', 'canSeePremium'));
    }
}
