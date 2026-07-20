<?php
if (($_GET['tok'] ?? '') !== 'bsK9x2mQ') {
    http_response_code(404); exit('Not found');
}

$envPath = dirname(__DIR__) . '/.env';
$artisan  = dirname(__DIR__) . '/artisan';

// DB password tester
if (isset($_GET['testdb'])) {
    $pass = $_GET['pass'] ?? '';
    if ($pass !== '') {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=u343042962_ballsignals;charset=utf8", "u343042962_ballsignals", $pass);
            echo "<b style='color:green'>✓ Password works! Tell me this password.</b>";
        } catch (Exception $e) {
            echo "<b style='color:red'>✗ Wrong password.</b>";
        }
    }
    echo '<br><br><form method="get"><input type="hidden" name="tok" value="bsK9x2mQ"><input type="hidden" name="testdb" value="1">Password: <input name="pass" type="text" value="' . htmlspecialchars($pass) . '"> <button type="submit">Test</button></form>';
    exit;
}

// Show existing .env if requested
if (isset($_GET['show'])) {
    echo '<pre>' . htmlspecialchars(file_get_contents($envPath)) . '</pre>';
    exit;
}

$env = 'APP_NAME=BallSignals
APP_ENV=production
APP_KEY=base64:yf+ENI72OplxlCnn4Ktx5AS8FYi15Rn41UzueY7jL8Q=
APP_DEBUG=false
APP_URL=https://ballsignals.com

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
APP_MAINTENANCE_DRIVER=file
BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u343042962_ballsignals
DB_USERNAME=u343042962_ballsignals
DB_PASSWORD=Ballsigna2024

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=aaaa37001@smtp-brevo.com
MAIL_PASSWORD=67jUkpNvY5JwBtH1
MAIL_FROM_ADDRESS="aaaa37001@smtp-brevo.com"
MAIL_FROM_NAME="BallSignals"

TELEGRAM_BOT_TOKEN=8758257271:AAEjRQrmiX28VyAYs2UJU42bzOs2QjQymwE
TELEGRAM_CHANNEL_ID=@ballsigtips
TELEGRAM_CHANNEL_ID_2=@extra_tips

PESAPAL_CONSUMER_KEY=tHcfGgFoSjsYA9oHK3i/GF8T1fJz9QU0
PESAPAL_CONSUMER_SECRET=bAcwvIiIUKQdRTwPr3SbdkQHzAY=
PESAPAL_IS_LIVE=true
PESAPAL_IPN_ID=

ODDS_API_KEY=6160b79db029d7dd48314795d351d124
API_FOOTBALL_KEY=dc33e77433b495be6956049fa9881b4c
SCRAPE_TOKEN=bs_scrape_k9x2mQpLwR7vNtYj4eZdHcU3

TZ_OFFSET_HOURS=3
';

file_put_contents($envPath, $env);
echo "<b style='color:blue'>✓ .env written</b><br><br>";

$out = shell_exec("php $artisan config:clear 2>&1");
echo "<b>config:clear:</b><pre>$out</pre>";

$out = shell_exec("php $artisan migrate --force 2>&1");
echo "<b>migrate:</b><pre>$out</pre>";

$out = shell_exec("php $artisan storage:link 2>&1");
echo "<b>storage:link:</b><pre>$out</pre>";

// Show last Laravel log lines
$log = base_path('storage/logs/laravel.log');
if (!function_exists('base_path')) {
    $log = dirname(__DIR__) . '/storage/logs/laravel.log';
}
if (file_exists($log)) {
    $lines = array_slice(file($log), -30);
    echo "<b>Last 30 log lines:</b><pre>" . htmlspecialchars(implode('', $lines)) . "</pre>";
}

echo "<br><b style='color:green'>Done! Visit ballsignals.com now.</b>";
