<?php

return [

    'merchant_id' => env('MIDTRANS_MERCHANT_ID', ''),

    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),

    'server_key' => env('MIDTRANS_SERVER_KEY', ''),

    'signature_secret_key' => env('MIDTRANS_SIGNATURE_SECRET_KEY', ''),

    'is_production' => filter_var(
        env('MIDTRANS_IS_PRODUCTION', false),
        FILTER_VALIDATE_BOOL
    ),

    'is_sanitized' => filter_var(
        env('MIDTRANS_IS_SANITIZED', true),
        FILTER_VALIDATE_BOOL
    ),

    'is_3ds' => filter_var(
        env('MIDTRANS_IS_3DS', true),
        FILTER_VALIDATE_BOOL
    ),

    'snap_script_url' => filter_var(
        env('MIDTRANS_IS_PRODUCTION', false),
        FILTER_VALIDATE_BOOL
    )
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',

    'snap_api_url' => filter_var(
        env('MIDTRANS_IS_PRODUCTION', false),
        FILTER_VALIDATE_BOOL
    )
        ? 'https://app.midtrans.com/snap/v1/transactions'
        : 'https://app.sandbox.midtrans.com/snap/v1/transactions',

    'status_api_url' => filter_var(
        env('MIDTRANS_IS_PRODUCTION', false),
        FILTER_VALIDATE_BOOL
    )
        ? 'https://api.midtrans.com/v2'
        : 'https://api.sandbox.midtrans.com/v2',

];