<?php

namespace App\Http\Controllers;

use App\Models\BettingTip;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResultsController extends Controller
{
    public function index(Request $request): View
    {
        $selectedLeague = $request->query('league');
        $selectedStatus = $request->query('status');

        $query = BettingTip::football()
            ->whereDate('match_time', '<', today())
            ->where('status', '!=', 'pending')
            ->when($selectedLeague, fn ($q) => $q->where('league', $selectedLeague))
            ->when($selectedStatus, fn ($q) => $q->where('status', $selectedStatus))
            ->orderBy('match_time', 'desc');

        $results = $query->paginate(20)->withQueryString();

        $leagues = BettingTip::football()
            ->whereDate('match_time', '<', today())
            ->where('status', '!=', 'pending')
            ->whereNotNull('league')
            ->distinct()
            ->orderBy('league')
            ->pluck('league');

        $won   = BettingTip::football()->where('status', 'won')->count();
        $lost  = BettingTip::football()->where('status', 'lost')->count();
        $total = $won + $lost;

        $stats = [
            'won'      => $won,
            'lost'     => $lost,
            'total'    => $total,
            'rate'     => $total > 0 ? round(($won / $total) * 100) : 0,
            'won_pct'  => $total > 0 ? round(($won  / $total) * 100) : 0,
            'lost_pct' => $total > 0 ? round(($lost / $total) * 100) : 0,
        ];

        return view('results', compact('results', 'leagues', 'stats', 'selectedLeague', 'selectedStatus'));
    }
}
