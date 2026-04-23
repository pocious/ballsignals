<?php

namespace App\Http\Controllers;

use App\Models\BettingTip;

class TipOfTheDayController extends Controller
{
    public function index()
    {
        $tip = BettingTip::football()
            ->whereDate('match_time', today())
            ->where('is_premium', false)
            ->orderByDesc('confidence')
            ->first();

        $recentWins = BettingTip::football()
            ->where('status', 'won')
            ->orderByDesc('match_time')
            ->take(5)
            ->get();

        $stats = [
            'won'   => BettingTip::football()->where('status', 'won')->count(),
            'lost'  => BettingTip::football()->where('status', 'lost')->count(),
            'total' => BettingTip::football()->whereIn('status', ['won', 'lost'])->count(),
        ];
        $stats['rate'] = $stats['total'] > 0 ? round(($stats['won'] / $stats['total']) * 100) : 0;

        return view('tip-of-the-day', compact('tip', 'recentWins', 'stats'));
    }
}
