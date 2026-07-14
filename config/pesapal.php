<?php

return [
    'consumer_key'    => env('PESAPAL_CONSUMER_KEY'),
    'consumer_secret' => env('PESAPAL_CONSUMER_SECRET'),
    'is_live'         => env('PESAPAL_IS_LIVE', true),
    'ipn_id'          => env('PESAPAL_IPN_ID'),
];
