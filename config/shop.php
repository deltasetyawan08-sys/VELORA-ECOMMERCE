<?php

return [
    'name' => env('SHOP_NAME', 'VELORA'),

    'tagline' => 'Find Your Style, Wear Your Confidence.',

    'shipping_cost' => (int) env('SHOP_SHIPPING_COST', 15000),

    'expiry_minutes' => (int) env('SHOP_EXPIRY_MINUTES', 60),

    'phone' => env('SHOP_PHONE', '081234567890'),

    'email' => env('SHOP_EMAIL', 'hello@velora.test'),

    'address' => env('SHOP_ADDRESS', 'Jl. Fashion Raya No. 10, Jakarta Selatan, Indonesia'),

    'free_shipping_note' => 'Ongkos kirim flat untuk seluruh Indonesia (mode demonstrasi tugas besar).',
];
