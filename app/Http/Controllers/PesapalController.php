<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionRequest;
use App\Services\PesapalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PesapalController extends Controller
{
    public function __construct(private PesapalService $pesapal) {}

    public function callback(Request $request)
    {
        $trackingId  = $request->query('OrderTrackingId');
        $merchantRef = $request->query('OrderMerchantReference');

        if (! $trackingId || ! $merchantRef) {
            return view('pesapal.callback', [
                'status'  => 'error',
                'message' => 'Invalid payment response received.',
            ]);
        }

        $sub = SubscriptionRequest::where('pesapal_merchant_ref', $merchantRef)->first();

        if (! $sub) {
            return view('pesapal.callback', [
                'status'  => 'error',
                'message' => 'Subscription record not found.',
            ]);
        }

        // Update tracking ID if not stored yet
        if (! $sub->pesapal_tracking_id) {
            $sub->update(['pesapal_tracking_id' => $trackingId]);
        }

        try {
            $statusData    = $this->pesapal->getTransactionStatus($trackingId);
            $paymentStatus = strtolower($statusData['payment_status_description'] ?? '');

            if ($paymentStatus === 'completed') {
                $this->approveSubscription($sub);
                session(['vip_email' => $sub->email]);
                return redirect()->route('premium')->with('vip_activated', true);
            }

            if ($paymentStatus === 'failed' || $paymentStatus === 'invalid') {
                return view('pesapal.callback', [
                    'status'  => 'failed',
                    'message' => 'Payment was not completed. Please try again.',
                ]);
            }

            return view('pesapal.callback', [
                'status'  => 'pending',
                'message' => 'Payment is being processed. Your access will be activated automatically once confirmed.',
                'email'   => $sub->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Pesapal callback error', ['error' => $e->getMessage()]);
            return view('pesapal.callback', [
                'status'  => 'pending',
                'message' => 'Payment received. Your access will be activated shortly. Enter your email on the VIP page to check.',
                'email'   => $sub->email,
            ]);
        }
    }

    // Pesapal calls this server-to-server when payment status changes
    public function ipn(Request $request)
    {
        $trackingId  = $request->query('OrderTrackingId');
        $merchantRef = $request->query('OrderMerchantReference');

        if (! $trackingId || ! $merchantRef) {
            return response()->json(['status' => 'error'], 400);
        }

        $sub = SubscriptionRequest::where('pesapal_merchant_ref', $merchantRef)->first();

        if (! $sub || $sub->status === 'approved') {
            return $this->ipnAck($trackingId, $merchantRef);
        }

        // Reject IPN if the tracking ID doesn't match what Pesapal gave us at order time
        if ($sub->pesapal_tracking_id && $sub->pesapal_tracking_id !== $trackingId) {
            Log::warning('Pesapal IPN tracking ID mismatch', [
                'expected' => $sub->pesapal_tracking_id,
                'received' => $trackingId,
                'ref'      => $merchantRef,
            ]);
            return response()->json(['status' => 'error'], 400);
        }

        try {
            $statusData    = $this->pesapal->getTransactionStatus($trackingId);
            $paymentStatus = strtolower($statusData['payment_status_description'] ?? '');

            if ($paymentStatus === 'completed') {
                $this->approveSubscription($sub);
            }
        } catch (\Exception $e) {
            Log::error('Pesapal IPN error', ['error' => $e->getMessage(), 'ref' => $merchantRef]);
        }

        return $this->ipnAck($trackingId, $merchantRef);
    }

    private function approveSubscription(SubscriptionRequest $sub): void
    {
        if ($sub->status === 'approved') {
            return;
        }

        $plan = SubscriptionRequest::$plans[$sub->plan] ?? null;
        $days = $plan['days'] ?? 30;

        $sub->update([
            'status'      => 'approved',
            'approved_at' => now(),
            'expires_at'  => now()->addDays($days),
        ]);
    }

    private function ipnAck(string $trackingId, string $merchantRef)
    {
        return response()->json([
            'orderNotificationType'  => 'IPNCHANGE',
            'orderTrackingId'        => $trackingId,
            'orderMerchantReference' => $merchantRef,
            'status'                 => '200',
        ]);
    }
}
