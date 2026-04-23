<?php

namespace App\Http\Controllers;

use App\Models\BettingTip;
use App\Models\SubscriptionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PremiumController extends Controller
{
    public function index(): View
    {
        $stats = [
            'won'   => BettingTip::football()->where('status', 'won')->count(),
            'lost'  => BettingTip::football()->where('status', 'lost')->count(),
            'total' => BettingTip::football()->whereIn('status', ['won', 'lost'])->count(),
        ];
        $stats['rate'] = $stats['total'] > 0
            ? round(($stats['won'] / $stats['total']) * 100)
            : 0;

        $subscription = $this->resolveActiveSubscription();

        $vipTips = null;
        if ($subscription) {
            $vipTips = BettingTip::where('is_premium', true)
                ->whereDate('match_time', '>=', today())
                ->orderBy('match_time')
                ->get();
        }

        return view('premium', compact('stats', 'subscription', 'vipTips'));
    }

    public function access(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        $sub = SubscriptionRequest::where('email', $request->email)
            ->where('status', 'approved')
            ->where('expires_at', '>', now())
            ->latest('approved_at')
            ->first();

        if (! $sub) {
            return back()->withErrors(['email' => 'No active premium subscription found for that email.'])->withInput();
        }

        session(['vip_email' => $request->email]);

        return redirect()->route('premium');
    }

    public function revoke(): RedirectResponse
    {
        session()->forget('vip_email');
        return redirect()->route('premium');
    }

    private function resolveActiveSubscription(): ?SubscriptionRequest
    {
        $email = session('vip_email');
        if (! $email) {
            return null;
        }

        $sub = SubscriptionRequest::where('email', $email)
            ->where('status', 'approved')
            ->where('expires_at', '>', now())
            ->latest('approved_at')
            ->first();

        if (! $sub) {
            session()->forget('vip_email');
        }

        return $sub;
    }
}
