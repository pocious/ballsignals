<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BettingTip;
use App\Models\ContactMessage;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total'    => BettingTip::count(),
            'pending'  => BettingTip::where('status', 'pending')->count(),
            'won'      => BettingTip::where('status', 'won')->count(),
            'lost'     => BettingTip::where('status', 'lost')->count(),
            'unread'   => ContactMessage::where('is_read', false)->count(),
        ];

        $recent = BettingTip::where(function ($q) {
            $q->where('is_premium', false)->orWhereNull('is_premium');
        })->latest()->take(5)->get();
        $recentMessages  = ContactMessage::latest()->take(5)->get();

        $premiumTips = BettingTip::where('is_premium', true)
            ->orderBy('match_time', 'asc')
            ->take(10)
            ->get();

        $premiumStats = [
            'total'   => BettingTip::where('is_premium', true)->count(),
            'pending' => BettingTip::where('is_premium', true)->where('status', 'pending')->count(),
            'won'     => BettingTip::where('is_premium', true)->where('status', 'won')->count(),
            'lost'    => BettingTip::where('is_premium', true)->where('status', 'lost')->count(),
        ];

        return view('admin.dashboard', compact('stats', 'recent', 'recentMessages', 'premiumTips', 'premiumStats'));
    }
}
