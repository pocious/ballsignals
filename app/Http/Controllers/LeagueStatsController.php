<?php

namespace App\Http\Controllers;

use App\Models\BettingTip;
use Illuminate\View\View;

class LeagueStatsController extends Controller
{
    public function index(): View
    {
        $leagueStats = BettingTip::football()
            ->whereIn('status', ['won', 'lost'])
            ->whereNotNull('league')
            ->get()
            ->groupBy('league')
            ->map(function ($tips) {
                $won   = $tips->where('status', 'won')->count();
                $lost  = $tips->where('status', 'lost')->count();
                $total = $won + $lost;
                return [
                    'league'   => $tips->first()->league,
                    'won'      => $won,
                    'lost'     => $lost,
                    'total'    => $total,
                    'rate'     => $total > 0 ? round(($won / $total) * 100) : 0,
                    'won_pct'  => $total > 0 ? round(($won  / $total) * 100) : 0,
                    'lost_pct' => $total > 0 ? round(($lost / $total) * 100) : 0,
                ];
            })
            ->sortByDesc('rate')
            ->values();

        $overall = [
            'won'   => $leagueStats->sum('won'),
            'lost'  => $leagueStats->sum('lost'),
            'total' => $leagueStats->sum('total'),
        ];
        $overall['rate'] = $overall['total'] > 0
            ? round(($overall['won'] / $overall['total']) * 100)
            : 0;

        return view('league-stats', compact('leagueStats', 'overall'));
    }
}
