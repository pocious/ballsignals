<?php

namespace App\Http\Controllers;

use App\Models\BettingTip;
use App\Models\SubscriptionRequest;
use App\Services\PesapalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

        // Admin users bypass the subscription gate
        $isAdmin = auth()->check() && auth()->user()->role === 'admin';

        $vipTips = null;
        if ($subscription || $isAdmin) {
            $vipTips = BettingTip::where('is_premium', true)
                ->whereDate('match_time', '>=', today())
                ->where('odds', '>=', 2.00)
                ->orderByRaw('DATE(match_time) ASC')
                ->orderByDesc('confidence')
                ->orderByDesc('odds')
                ->get()
                ->groupBy(fn ($tip) => $tip->match_time->toDateString())
                ->map(fn ($tips) => $tips->take(1))
                ->flatten();
        }

        return view('premium', compact('stats', 'subscription', 'vipTips', 'isAdmin'));
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

        // Generate a unique token for this device/session and store it in DB.
        // Any previously logged-in device will be invalidated immediately.
        $token = bin2hex(random_bytes(32));
        $sub->update(['session_token' => $token]);

        session(['vip_email' => $request->email, 'vip_token' => $token]);

        return redirect()->route('premium')->with('vip_activated', true);
    }

    public function revoke(): RedirectResponse
    {
        $email = session('vip_email');
        if ($email) {
            SubscriptionRequest::where('email', $email)
                ->update(['session_token' => null]);
        }
        session()->forget(['vip_email', 'vip_token']);
        return redirect()->route('premium');
    }

    public function renew(Request $request): RedirectResponse
    {
        $email = $request->query('email');
        $plan  = $request->query('plan');
        $name  = $request->query('name', '');
        $phone = $request->query('phone', '');

        if (! $email || ! array_key_exists($plan, SubscriptionRequest::$plans)) {
            return redirect()->route('premium');
        }

        $planData    = SubscriptionRequest::$plans[$plan];
        $merchantRef = 'BS-' . strtoupper(Str::random(10));

        $sub = SubscriptionRequest::create([
            'name'                 => $name ?: $email,
            'email'                => $email,
            'phone'                => $phone,
            'plan'                 => $plan,
            'status'               => 'pending',
            'pesapal_merchant_ref' => $merchantRef,
        ]);

        try {
            $pesapal   = app(PesapalService::class);
            $nameParts = explode(' ', trim($name ?: $email), 2);

            $result = $pesapal->submitOrder(
                merchantRef: $merchantRef,
                amount:      $planData['amount_ugx'],
                currency:    'UGX',
                description: "BallSignals {$planData['label']} Renewal",
                callbackUrl: route('pesapal.callback'),
                email:       $email,
                phone:       preg_replace('/\D/', '', $phone),
                firstName:   $nameParts[0],
                lastName:    $nameParts[1] ?? '-',
            );

            if (! empty($result['order_tracking_id'])) {
                $sub->update(['pesapal_tracking_id' => $result['order_tracking_id']]);
            }

            if (! empty($result['redirect_url'])) {
                $host = parse_url($result['redirect_url'], PHP_URL_HOST);
                if ($host && str_ends_with($host, 'pesapal.com')) {
                    return redirect()->away($result['redirect_url']);
                }
            }
        } catch (\Exception $e) {
            $sub->delete();
        }

        return redirect()->route('premium');
    }

    private function resolveActiveSubscription(): ?SubscriptionRequest
    {
        $email = session('vip_email');
        $token = session('vip_token');

        if (! $email || ! $token) {
            return null;
        }

        $sub = SubscriptionRequest::where('email', $email)
            ->where('status', 'approved')
            ->where('expires_at', '>', now())
            ->where('session_token', $token)
            ->latest('approved_at')
            ->first();

        if (! $sub) {
            session()->forget(['vip_email', 'vip_token']);
        }

        return $sub;
    }
}
