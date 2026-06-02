<?php
/**
 * Script test SePay Webhook - Giả lập SePay gọi webhook
 * Chạy: php test-webhook.php BK202512022BB956
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

// Bootstrap Laravel đầy đủ
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;

echo "=== TEST SEPAY WEBHOOK ===\n\n";

$bookingNumber = $argv[1] ?? 'BK202512022BB956';
$amount = $argv[2] ?? 1350000;

// Lấy thông tin booking
$booking = Booking::where('booking_number', $bookingNumber)->first();
if ($booking) {
    $amount = $booking->final_amount;
    echo "✓ Tìm thấy booking: $bookingNumber\n";
    echo "  - Status hiện tại: {$booking->status}\n";
    echo "  - Payment status: {$booking->payment_status}\n";
    echo "  - Số tiền: " . number_format($amount) . " VND\n\n";
} else {
    echo "⚠ Không tìm thấy booking $bookingNumber, dùng amount mặc định\n\n";
}

// Tạo payload giả lập từ SePay
// LƯU Ý: KHÔNG CÓ DẤU CÁCH giữa pattern và booking number
$webhookPayload = [
    'id' => rand(100000, 999999),
    'gateway' => 'MBBank',
    'transactionDate' => date('Y-m-d H:i:s'),
    'accountNumber' => config('sepay.bank.account_number', '0986789260666'),
    'code' => null,
    'content' => 'HOMESTAY' . $bookingNumber,  // Không có dấu cách!
    'transferType' => 'in',
    'transferAmount' => (int) $amount,
    'accumulated' => (int) $amount,
    'subAccount' => null,
    'referenceCode' => 'FT' . date('YmdHis'),
    'description' => 'HOMESTAY' . $bookingNumber . ' chuyen khoan'
];

echo "1. Webhook Payload:\n";
echo json_encode($webhookPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Tạo request giả lập
$webhookToken = config('sepay.webhook_token');
echo "2. Gọi webhook với token: " . substr($webhookToken, 0, 15) . "...\n\n";

$request = Request::create(
    '/api/sepay/webhook',
    'POST',
    $webhookPayload
);
$request->headers->set('Content-Type', 'application/json');
$request->headers->set('Authorization', 'Apikey ' . $webhookToken);
$request->headers->set('Accept', 'application/json');

// Gọi kernel để xử lý request
echo "3. Đang gọi webhook endpoint...\n";
try {
    // Dùng HTTP kernel để xử lý request
    $httpKernel = $app->make('Illuminate\Contracts\Http\Kernel');
    $response = $httpKernel->handle($request);
    $statusCode = $response->getStatusCode();
    $content = $response->getContent();
    
    echo "\n4. Response:\n";
    echo "   - Status Code: $statusCode\n";
    echo "   - Body: $content\n\n";
    
    if ($statusCode === 200) {
        echo "✅ Webhook xử lý thành công!\n\n";
    } else {
        echo "❌ Webhook trả về lỗi!\n\n";
    }
} catch (\Exception $e) {
    echo "\n❌ Lỗi: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
}

// Kiểm tra kết quả
echo "5. Kiểm tra kết quả trong database:\n";
$booking = Booking::where('booking_number', $bookingNumber)->first();
if ($booking) {
    $booking->refresh();
    echo "   - Booking Status: {$booking->status}\n";
    echo "   - Payment Status: {$booking->payment_status}\n";
    
    $payment = Payment::where('booking_id', $booking->id)->first();
    if ($payment) {
        $payment->refresh();
        echo "   - Payment Record Status: {$payment->status}\n";
        echo "   - Paid At: " . ($payment->paid_at ?? 'NULL') . "\n";
    }
    
    if ($booking->status === 'confirmed' && $booking->payment_status === 'paid') {
        echo "\n🎉 THÀNH CÔNG! Booking đã được xác nhận thanh toán!\n";
    } else {
        echo "\n⚠ Booking chưa được cập nhật. Kiểm tra log:\n";
        echo "   tail -f storage/logs/laravel.log\n";
    }
} else {
    echo "   ❌ Không tìm thấy booking\n";
}

echo "\n=== KẾT THÚC ===\n";
