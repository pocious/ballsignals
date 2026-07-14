<?php

namespace App\Console\Commands;

use App\Services\PesapalService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class PesapalSetup extends Command
{
    protected $signature   = 'pesapal:setup';
    protected $description = 'Register IPN with Pesapal and verify the connection';

    public function handle(PesapalService $pesapal): int
    {
        $this->info('');
        $this->info('=== Pesapal Setup ===');
        $this->info('Mode : ' . (config('pesapal.is_live') ? 'LIVE' : 'SANDBOX'));
        $this->info('Key  : ' . substr(config('pesapal.consumer_key'), 0, 8) . '...');
        $this->info('');

        // Step 1 — Get auth token
        $this->line('1. Authenticating with Pesapal...');
        try {
            $token = $pesapal->getToken();
            $this->info('   ✓ Token received');
        } catch (\Exception $e) {
            $this->error('   ✗ Authentication failed: ' . $e->getMessage());
            $this->warn('   Check PESAPAL_CONSUMER_KEY and PESAPAL_CONSUMER_SECRET in .env');
            return self::FAILURE;
        }

        // Step 2 — Register IPN
        $ipnUrl = route('pesapal.ipn');
        $this->line("2. Registering IPN URL: {$ipnUrl}");

        $baseUrl  = config('pesapal.is_live')
            ? 'https://pay.pesapal.com/v3'
            : 'https://cybqa.pesapal.com/pesapalv3';

        $http = Http::acceptJson()->timeout(30);
        if (app()->isLocal()) { $http = $http->withoutVerifying(); }

        $response = $http->withToken($token)
            ->post("{$baseUrl}/api/URLSetup/RegisterIPN", [
                'url'                   => $ipnUrl,
                'ipn_notification_type' => 'GET',
            ]);

        if ($response->failed() || empty($response->json('ipn_id'))) {
            $this->error('   ✗ IPN registration failed');
            $this->line('   Response: ' . $response->body());
            return self::FAILURE;
        }

        $ipnId = $response->json('ipn_id');
        $this->info("   ✓ IPN registered — ID: {$ipnId}");

        // Step 3 — Write IPN ID to .env
        $this->line('3. Saving IPN ID to .env...');
        $envPath    = base_path('.env');
        $envContent = file_get_contents($envPath);

        if (str_contains($envContent, 'PESAPAL_IPN_ID=')) {
            $envContent = preg_replace('/^PESAPAL_IPN_ID=.*/m', "PESAPAL_IPN_ID={$ipnId}", $envContent);
        } else {
            $envContent .= "\nPESAPAL_IPN_ID={$ipnId}";
        }

        file_put_contents($envPath, $envContent);
        $this->info('   ✓ PESAPAL_IPN_ID saved to .env');

        // Step 4 — List registered IPNs to confirm
        $this->line('4. Confirming registered IPNs on Pesapal...');
        $listResponse = $http->withToken($token)
            ->get("{$baseUrl}/api/URLSetup/GetIpnList");

        if ($listResponse->ok()) {
            $ipns = $listResponse->json();
            foreach ((array) $ipns as $ipn) {
                $url   = $ipn['url']    ?? '—';
                $id    = $ipn['ipn_id'] ?? '—';
                $status = $ipn['ipn_status'] ?? '—';
                $this->line("   • [{$id}] {$url} ({$status})");
            }
        }

        $this->info('');
        $this->info('✅ Pesapal is fully configured and ready to accept payments.');
        $this->info('   Callback URL : ' . route('pesapal.callback'));
        $this->info('   IPN URL      : ' . $ipnUrl);
        $this->info('');

        return self::SUCCESS;
    }
}
