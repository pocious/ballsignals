<?php

namespace App\Http\Controllers;

use App\Models\BettingTip;
use Illuminate\View\View;

class VipHistoryController extends Controller
{
    public function index(): View
    {
        $from = today()->subDays(29)->startOfDay();
        $to   = today()->subDay()->endOfDay();

        $tips = BettingTip::where('is_premium', true)
            ->whereBetween('match_time', [$from, $to])
            ->whereIn('status', ['won', 'lost'])
            ->orderBy('match_time', 'desc')
            ->get();

        // Group by date, build per-day stats
        $days = $tips->groupBy(fn($t) => $t->match_time->toDateString())
            ->map(function ($dayTips, $date) {
                $won  = $dayTips->where('status', 'won');
                $lost = $dayTips->where('status', 'lost');

                $accumOdds = $won->filter(fn($t) => $t->odds > 0)
                    ->reduce(fn($carry, $t) => $carry * (float) $t->odds, 1.0);

                return [
                    'date'       => $date,
                    'tips'       => $dayTips->sortBy('match_time')->values(),
                    'won'        => $won->count(),
                    'lost'       => $lost->count(),
                    'total'      => $dayTips->count(),
                    'accum_odds' => $won->count() > 0 ? round($accumOdds, 2) : null,
                    'all_won'    => $lost->count() === 0 && $won->count() > 0,
                ];
            })
            ->sortKeysDesc();

        // Overall month stats
        $totalWon  = $tips->where('status', 'won')->count();
        $totalLost = $tips->where('status', 'lost')->count();
        $total     = $totalWon + $totalLost;
        $winRate   = $total > 0 ? round(($totalWon / $total) * 100) : 0;

        $perfectDays = $days->filter(fn($d) => $d['all_won'] && $d['total'] > 0)->count();

        return view('vip-history', compact('days', 'totalWon', 'totalLost', 'total', 'winRate', 'perfectDays', 'from', 'to'));
    }
}
