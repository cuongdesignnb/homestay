<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SePay Webhook Configuration (theo package sepayvn/laravel-sepay)
    |--------------------------------------------------------------------------
    */
    
    // Token xác thực webhook (lấy từ SePay dashboard)
    'webhook_token' => env('SEPAY_WEBHOOK_TOKEN'),
    
    // Pattern để match nội dung chuyển khoản
    // QUAN TRỌNG: Dùng SEPAY_MATCH_PATTERN theo package gốc
    // VD: HT1234567890 → pattern = 'HT', info = '1234567890'
    'pattern' => env('SEPAY_MATCH_PATTERN', 'HT'),
    
    /*
    |--------------------------------------------------------------------------
    | Bank Account Configuration
    |--------------------------------------------------------------------------
    */
    
    'bank' => [
        'name' => env('SEPAY_BANK_NAME', 'MBBank'),
        'code' => env('SEPAY_BANK_CODE', 'MB'),
        'account_number' => env('SEPAY_BANK_ACCOUNT', ''),
        'account_name' => env('SEPAY_BANK_ACCOUNT_NAME', ''),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | QR Code Configuration
    |--------------------------------------------------------------------------
    */
    
    'payment_timeout' => env('SEPAY_PAYMENT_TIMEOUT', 30),
    'qr_api_url' => 'https://img.vietqr.io/image',
];
