<?php
/**
 * Test SePay Webhook trên Production
 * Truy cập: https://catbacountrysidehomestay.com/test-sepay-webhook.php?code=1234567890
 * XÓA FILE NÀY SAU KHI DEBUG XONG!
 */

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// Bootstrap Laravel đầy đủ
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Lấy HTTP kernel để handle request
$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

header('Content-Type: application/json');

// Lấy payment_code từ query string
$paymentCode = $_GET['code'] ?? '1234567890';
$amount = $_GET['amount'] ?? 100000;

// Tạo payload giả lập từ SePay
// Format: HT + payment_code (chỉ số, 3-10 ký tự)
$webhookPayload = [
    'id' => rand(100000, 999999),
    'gateway' => 'MBBank',
    'transactionDate' => date('Y-m-d H:i:s'),
    'accountNumber' => config('sepay.bank.account_number', '0986789260666'),
    'code' => null,
    'content' => 'HT' . $paymentCode, // Format: HT + số
    'transferType' => 'in',
    'transferAmount' => (int) $amount,
    'accumulated' => (int) $amount,
    'subAccount' => null,
    'referenceCode' => 'FT' . date('YmdHis'),
    'description' => 'HT' . $paymentCode . ' chuyen khoan'
];

echo "=== TEST SEPAY WEBHOOK ===\n\n";
echo "Payment Code: $paymentCode\n";
echo "Amount: $amount\n";
echo "Content: " . $webhookPayload['content'] . "\n\n";

// Tạo request
$webhookToken = config('sepay.webhook_token');

$request = Illuminate\Http\Request::create(
    '/api/sepay/webhook',
    'POST',
    $webhookPayload,
    [],
    [],
    [
        'HTTP_AUTHORIZATION' => 'Apikey ' . $webhookToken,
        'HTTP_CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => 'application/json',
    ],
    json_encode($webhookPayload)
);

$request->headers->set('Content-Type', 'application/json');

try {
    $response = $httpKernel->handle($request);
    
    $result = [
        'test_payload' => $webhookPayload,
        'response_status' => $response->getStatusCode(),
        'response_body' => $response->getContent(),
        'success' => $response->getStatusCode() === 204,
    ];
    
    // Kiểm tra kết quả trong DB theo payment_code
    $booking = DB::table('bookings')->where('payment_code', $paymentCode)->first();
    if ($booking) {
        $result['booking_after'] = [
            'booking_number' => $booking->booking_number,
            'status' => $booking->status,
            'payment_status' => $booking->payment_status,
        ];
    }
    
    // Kiểm tra transaction đã lưu chưa
    $transaction = DB::table('sepay_transactions')->where('id', $webhookPayload['id'])->first();
    $result['transaction_saved'] = $transaction ? true : false;
    
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (\Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ], JSON_PRETTY_PRINT);
}
