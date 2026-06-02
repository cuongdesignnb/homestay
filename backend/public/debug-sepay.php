<?php
/**
 * Debug SePay Webhook Configuration
 * Truy cập: https://catbacountrysidehomestay.com/api/debug-sepay
 * XÓA FILE NÀY SAU KHI DEBUG XONG!
 */

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: application/json');

$debug = [
    'status' => 'ok',
    'time' => date('Y-m-d H:i:s'),
    'config' => [
        'webhook_token_set' => !empty(config('sepay.webhook_token')),
        'webhook_token_preview' => config('sepay.webhook_token') ? substr(config('sepay.webhook_token'), 0, 15) . '...' : 'NOT SET',
        'pattern' => config('sepay.pattern'),
        'bank_account' => config('sepay.bank.account_number'),
        'bank_name' => config('sepay.bank.name'),
    ],
    'routes' => [
        'sepay_webhook' => url('/api/sepay/webhook'),
    ],
    'database' => [
        'sepay_transactions_exists' => Schema::hasTable('sepay_transactions'),
    ],
    'instructions' => [
        '1. Đăng nhập SePay Dashboard: https://my.sepay.vn',
        '2. Vào Cài đặt > Webhook',
        '3. URL Webhook: ' . url('/api/sepay/webhook'),
        '4. Auth Type: Api Key',
        '5. Token: [copy từ .env SEPAY_WEBHOOK_TOKEN]',
        '6. Nội dung chuyển khoản phải có format: HOMESTAY + mã booking (không có dấu cách)',
        '7. Ví dụ: HOMESTAYBK202512021234',
    ],
];

// Check recent transactions
try {
    $transactions = DB::table('sepay_transactions')
        ->orderBy('id', 'desc')
        ->limit(5)
        ->get(['id', 'content', 'transferAmount', 'transactionDate']);
    $debug['recent_transactions'] = $transactions;
} catch (\Exception $e) {
    $debug['recent_transactions_error'] = $e->getMessage();
}

// Check recent bookings waiting for payment
try {
    $pendingBookings = DB::table('bookings')
        ->where('payment_status', 'unpaid')
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get(['id', 'booking_number', 'final_amount', 'status', 'created_at']);
    $debug['pending_bookings'] = $pendingBookings;
} catch (\Exception $e) {
    $debug['pending_bookings_error'] = $e->getMessage();
}

// Check logs
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    $lastLines = array_slice($lines, -50);
    
    // Tìm các dòng liên quan đến SePay
    $sePayLogs = array_filter($lastLines, function($line) {
        return stripos($line, 'sepay') !== false || stripos($line, 'webhook') !== false;
    });
    
    $debug['recent_sepay_logs'] = array_values($sePayLogs);
}

echo json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
