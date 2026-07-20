<?php
// One-time .env setup script — auto-deletes after use
if (($_GET['tok'] ?? '') !== 'bsK9x2mQ') {
    http_response_code(404); exit('Not found');
}

$envPath = dirname(__DIR__) . '/.env';

if (file_exists($envPath)) {
    echo '<b style="color:green">✓ .env already exists. You are good!</b>';
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
DB_HOST=127.0.0.1
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

// Run key artisan commands via shell
$artisan = dirname(__DIR__) . '/artisan';
shell_exec("php $artisan config:clear 2>&1");
shell_exec("php $artisan migrate --force 2>&1");

echo '<b style="color:green">✓ .env created and migrations ran! Site should work now.</b>';
