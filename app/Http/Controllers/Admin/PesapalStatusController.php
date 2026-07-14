<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PesapalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;

class PesapalStatusController extends Controller
{
    public function __construct(private PesapalService $pesapal) {}

    private function httpClient(): PendingRequest
    {
        $client = Http::acceptJson()->timeout(30);
        if (app()->isLocal()) {
            $client = $client->withoutVerifying();
        }
        return $client;
    }

    public function index()
    {
        $baseUrl = config('pesapal.is_live')
            ? 'https://pay.pesapal.com/v3'
            : 'https://cybqa.pesapal.com/pesapalv3';

        $status = ['connected' => false, 'token' => null, 'ipns' => [], 'error' => null];

        try {
            $token              = $this->pesapal->getToken();
            $status['connected'] = true;
            $status['token']    = substr($token, 0, 20) . '...';

            $ipnList = $this->httpClient()->withToken($token)
                ->get("{$baseUrl}/api/URLSetup/GetIpnList");

            $status['ipns'] = $ipnList->ok() ? (array) $ipnList->json() : [];
        } catch (\Exception $e) {
            $status['error'] = $e->getMessage();
        }

        $ipnId      = config('pesapal.ipn_id');
        $callbackUrl = route('pesapal.callback');
        $ipnUrl     = route('pesapal.ipn');
        $isLive     = config('pesapal.is_live');

        return view('admin.pesapal-status', compact('status', 'ipnId', 'callbackUrl', 'ipnUrl', 'isLive', 'baseUrl'));
    }

    public function registerIpn(Request $request)
    {
        $baseUrl = config('pesapal.is_live')
            ? 'https://pay.pesapal.com/v3'
            : 'https://cybqa.pesapal.com/pesapalv3';

        try {
            $token = $this->pesapal->getToken();

            $response = $this->httpClient()->withToken($token)
                ->post("{$baseUrl}/api/URLSetup/RegisterIPN", [
                    'url'                   => route('pesapal.ipn'),
                    'ipn_notification_type' => 'GET',
                ]);

            if ($response->failed() || empty($response->json('ipn_id'))) {
                return back()->withErrors(['ipn' => 'Registration failed: ' . $response->body()]);
            }

            $ipnId      = $response->json('ipn_id');
            $envPath    = base_path('.env');
            $envContent = file_get_contents($envPath);

            if (str_contains($envContent, 'PESAPAL_IPN_ID=')) {
                $envContent = preg_replace('/^PESAPAL_IPN_ID=.*/m', "PESAPAL_IPN_ID={$ipnId}", $envContent);
            } else {
                $envContent .= "\nPESAPAL_IPN_ID={$ipnId}";
            }

            file_put_contents($envPath, $envContent);

            return back()->with('ipn_success', "IPN registered successfully. ID: {$ipnId}");
        } catch (\Exception $e) {
            return back()->withErrors(['ipn' => $e->getMessage()]);
        }
    }
}
