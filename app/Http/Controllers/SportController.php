<?php

namespace App\Http\Controllers;

use App\Models\BettingTip;
use App\Models\SubscriptionRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SportController extends Controller
{
    private const SPORTS = [
        'tennis' => [
            'name'    => 'Tennis',
            'db'      => 'Tennis',
            'icon'    => '🎾',
            'color'   => 'lime',
            'live_minutes' => 120,
        ],
        'cricket' => [
            'name'    => 'Cricket',
            'db'      => 'Cricket',
            'icon'    => '🏏',
            'color'   => 'emerald',
            'live_minutes' => 480,
        ],
        'mma' => [
            'name'    => 'MMA',
            'db'      => 'MMA',
            'icon'    => '🥊',
            'color'   => 'red',
            'live_minutes' => 60,
        ],
        'baseball' => [
            'name'    => 'Baseball',
            'db'      => 'Baseball',
            'icon'    => '⚾',
            'color'   => 'blue',
            'live_minutes' => 180,
        ],
        'american-football' => [
            'name'    => 'American Football',
            'db'      => 'American Football',
            'icon'    => '🏈',
            'color'   => 'orange',
            'live_minutes' => 210,
        ],
        'hockey' => [
            'name'    => 'Hockey',
            'db'      => 'Hockey',
            'icon'    => '🏒',
            'color'   => 'cyan',
            'live_minutes' => 75,
        ],
        'rugby' => [
            'name'    => 'Rugby',
            'db'      => 'Rugby',
            'icon'    => '🏉',
            'color'   => 'purple',
            'live_minutes' => 90,
        ],
    ];

    public function index(Request $request, string $sport): View
    {
        $config = self::SPORTS[$sport] ?? abort(404);

        $selectedLeague = $request->query('league');
        $selectedSort   = $request->query('sort', 'time');

        $baseQuery = BettingTip::where('sport', $config['db'])
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
            default     => (clone $baseQuery)->orderByRaw('DATE(match_time) ASC, match_time DESC'),
        };

        $tipsByDate = $sortedQuery->get()
            ->groupBy(fn ($tip) => $tip->match_time->toDateString());

        $leagues = BettingTip::where('sport', $config['db'])
            ->where('match_time', '>=', today()->startOfDay())
            ->where('match_time', '<=', today()->addDays(7)->endOfDay())
            ->whereNotNull('league')
            ->distinct()
            ->orderBy('league')
            ->pluck('league');

        $won   = BettingTip::where('sport', $config['db'])->whereDate('match_time', today()->subDay())->where('status', 'won')->count();
        $lost  = BettingTip::where('sport', $config['db'])->whereDate('match_time', today()->subDay())->where('status', 'lost')->count();
        $total = $won + $lost;

        $stats = [
            'won'   => $won,
            'lost'  => $lost,
            'total' => $total,
            'rate'  => $total > 0 ? round(($won / $total) * 100) : 0,
        ];

        $yesterdayTips = BettingTip::where('sport', $config['db'])
            ->whereDate('match_time', today()->subDay())
            ->whereIn('status', ['won', 'lost'])
            ->orderBy('match_time')
            ->get();

        $canSeePremium = auth()->check() && auth()->user()->role === 'admin';
        if (!$canSeePremium && session('vip_email') && session('vip_token')) {
            $canSeePremium = SubscriptionRequest::where('email', session('vip_email'))
                ->where('status', 'approved')
                ->where('expires_at', '>', now())
                ->where('session_token', session('vip_token'))
                ->exists();
        }

        return view('sport', compact(
            'tipsByDate', 'leagues', 'stats', 'selectedLeague', 'selectedSort',
            'yesterdayTips', 'canSeePremium', 'config', 'sport'
        ));
    }
}
