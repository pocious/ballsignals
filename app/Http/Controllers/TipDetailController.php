<?php

namespace App\Http\Controllers;

use App\Models\BettingTip;
use Illuminate\View\View;

class TipDetailController extends Controller
{
    public function show(BettingTip $bettingTip): View
    {
        $premiumToday = BettingTip::where('is_premium', true)
            ->whereDate('match_time', today())
            ->orderBy('match_time')
            ->get();

        $combinedOdds = $premiumToday->whereNotNull('odds')->reduce(
            fn($carry, $t) => round($carry + $t->odds, 2), 0.0
        );

        return view('tip-detail', compact('bettingTip', 'premiumToday', 'combinedOdds'));
    }
}
