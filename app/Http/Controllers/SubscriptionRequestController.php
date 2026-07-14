<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionRequest;
use App\Services\PesapalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriptionRequestController extends Controller
{
    public function __construct(private PesapalService $pesapal) {}

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'           => 'required|string|max:100',
            'email'          => 'required|email|max:150',
            'phone'          => 'nullable|string|max:30',
            'plan'           => 'required|in:weekly,monthly,two_months',
            'payment_method' => 'required|in:mobile_money,credit_card',
        ]);

        $plan        = SubscriptionRequest::$plans[$data['plan']];
        $isMobile    = $data['payment_method'] === 'mobile_money';
        $currency    = $isMobile ? 'UGX' : 'USD';
        $amount      = $isMobile ? $plan['amount_ugx'] : $plan['amount_usd'];
        $merchantRef = 'BS-' . strtoupper(Str::random(10));

        $sub = SubscriptionRequest::create([
            'name'                 => $data['name'],
            'email'                => $data['email'],
            'phone'                => $data['phone'] ?? '',
            'plan'                 => $data['plan'],
            'status'               => 'pending',
            'pesapal_merchant_ref' => $merchantRef,
        ]);

        try {
            $nameParts = explode(' ', trim($data['name']), 2);
            $phone     = ! empty($data['phone']) ? preg_replace('/\D/', '', $data['phone']) : '';

            $result = $this->pesapal->submitOrder(
                merchantRef:  $merchantRef,
                amount:       $amount,
                currency:     $currency,
                description:  "BallSignals {$plan['label']} Subscription",
                callbackUrl:  route('pesapal.callback'),
                email:        $data['email'],
                phone:        $phone,
                firstName:    $nameParts[0],
                lastName:     $nameParts[1] ?? '-',
            );

            if (! empty($result['order_tracking_id'])) {
                $sub->update(['pesapal_tracking_id' => $result['order_tracking_id']]);
            }

            if (! empty($result['redirect_url'])) {
                $host = parse_url($result['redirect_url'], PHP_URL_HOST);
                if (! $host || ! str_ends_with($host, 'pesapal.com')) {
                    throw new \RuntimeException('Invalid payment redirect received.');
                }
                return redirect()->away($result['redirect_url']);
            }
        } catch (\Exception $e) {
            $sub->delete();
            return back()
                ->withErrors(['payment' => $e->getMessage()])
                ->withInput();
        }

        return redirect()->route('premium')->with('sub_success', true);
    }
}
