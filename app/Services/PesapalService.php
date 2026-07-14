<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PesapalService
{
    private string $baseUrl;
    private string $consumerKey;
    private string $consumerSecret;

    public function __construct()
    {
        $this->baseUrl = config('pesapal.is_live')
            ? 'https://pay.pesapal.com/v3'
            : 'https://cybqa.pesapal.com/pesapalv3';

        $this->consumerKey    = config('pesapal.consumer_key');
        $this->consumerSecret = config('pesapal.consumer_secret');
    }

    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        $client = Http::acceptJson()->timeout(30);
        // On local dev (XAMPP) SSL certs are not bundled — disable verification only there
        if (app()->isLocal()) {
            $client = $client->withoutVerifying();
        }
        return $client;
    }

    public function getToken(bool $forceRefresh = false): string
    {
        if ($forceRefresh) {
            Cache::forget('pesapal_token');
        }

        // Tokens last ~60 min; cache for 50 min to be safe
        return Cache::remember('pesapal_token', 3000, function () {
            $response = $this->http()->post("{$this->baseUrl}/api/Auth/RequestToken", [
                'consumer_key'    => $this->consumerKey,
                'consumer_secret' => $this->consumerSecret,
            ]);

            if ($response->failed() || empty($response->json('token'))) {
                Log::error('Pesapal auth failed', $response->json() ?? []);
                throw new \RuntimeException('Pesapal authentication failed.');
            }

            return $response->json('token');
        });
    }

    private function requestWithRetry(callable $fn): mixed
    {
        $response = $fn($this->getToken());

        // If Pesapal returns 401/403, the cached token is stale — refresh once and retry
        if (in_array($response->status(), [401, 403])) {
            Log::info('Pesapal token rejected, refreshing and retrying...');
            $response = $fn($this->getToken(forceRefresh: true));
        }

        return $response;
    }

    public function getOrRegisterIpn(): string
    {
        // Prefer the one pinned in .env so it survives cache clears
        if ($pinned = config('pesapal.ipn_id')) {
            return $pinned;
        }

        return Cache::rememberForever('pesapal_ipn_id', function () {
            $response = $this->requestWithRetry(fn($token) => $this->http()->withToken($token)
                ->post("{$this->baseUrl}/api/URLSetup/RegisterIPN", [
                    'url'                   => route('pesapal.ipn'),
                    'ipn_notification_type' => 'GET',
                ]));

            if ($response->failed() || empty($response->json('ipn_id'))) {
                Log::error('Pesapal IPN registration failed', $response->json() ?? []);
                throw new \RuntimeException('Pesapal IPN registration failed.');
            }

            return $response->json('ipn_id');
        });
    }

    public function submitOrder(
        string $merchantRef,
        float  $amount,
        string $currency,
        string $description,
        string $callbackUrl,
        string $email,
        string $phone,
        string $firstName,
        string $lastName,
    ): array {
        $ipnId = $this->getOrRegisterIpn();

        $response = $this->requestWithRetry(fn($token) => $this->http()->withToken($token)
            ->post("{$this->baseUrl}/api/Transactions/SubmitOrderRequest", [
                'id'              => $merchantRef,
                'currency'        => $currency,
                'amount'          => $amount,
                'description'     => $description,
                'callback_url'    => $callbackUrl,
                'notification_id' => $ipnId,
                'billing_address' => [
                    'email_address' => $email,
                    'phone_number'  => $phone,
                    'first_name'    => $firstName,
                    'last_name'     => $lastName,
                ],
            ]));

        if ($response->failed()) {
            Log::error('Pesapal order submission failed', $response->json() ?? []);
            throw new \RuntimeException('Could not initiate payment. Please try again.');
        }

        return $response->json();
    }

    public function getTransactionStatus(string $orderTrackingId): array
    {
        $response = $this->requestWithRetry(fn($token) => $this->http()->withToken($token)
            ->get("{$this->baseUrl}/api/Transactions/GetTransactionStatus", [
                'orderTrackingId' => $orderTrackingId,
            ]));

        if ($response->failed()) {
            Log::error('Pesapal status check failed', ['trackingId' => $orderTrackingId]);
            throw new \RuntimeException('Could not verify payment status.');
        }

        return $response->json();
    }
}
