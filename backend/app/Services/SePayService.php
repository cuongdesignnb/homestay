<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\TourBooking;

class SePayService
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('sepay', []);
    }

    /**
     * Kiểm tra đã cấu hình đầy đủ chưa
     */
    public function isConfigured(): bool
    {
        return !empty($this->config['bank']['account_number']) 
            && !empty($this->config['webhook_token']);
    }

    /**
     * Tạo nội dung chuyển khoản cho SePay
     * Format: HT + payment_code (chỉ số, 3-10 ký tự)
     * Ví dụ: HT1234567890
     */
    public function generateTransferContent(string $paymentCode): string
    {
        $pattern = $this->config['pattern'] ?? 'HT';
        return $pattern . $paymentCode;
    }

    /**
     * Tạo URL QR Code VietQR
     */
    public function generateQRCodeUrl(Payment $payment, $booking): string
    {
        $bank = $this->config['bank'] ?? [];
        $bankCode = $bank['code'] ?? 'MB';
        $accountNumber = $bank['account_number'] ?? '';
        $accountName = $bank['account_name'] ?? '';
        
        $amount = (int) $payment->amount;
        
        // Đảm bảo booking có payment_code
        $paymentCode = $booking->payment_code ?? $booking->generatePaymentCode();
        $content = $this->generateTransferContent($paymentCode);
        
        // VietQR URL format
        $qrUrl = sprintf(
            '%s/%s-%s-compact2.png?amount=%d&addInfo=%s&accountName=%s',
            $this->config['qr_api_url'] ?? 'https://img.vietqr.io/image',
            $bankCode,
            $accountNumber,
            $amount,
            urlencode($content),
            urlencode($accountName)
        );
        
        return $qrUrl;
    }

    /**
     * Lấy thông tin thanh toán để hiển thị
     */
    public function getPaymentInfo(Payment $payment, $booking): array
    {
        $bank = $this->config['bank'] ?? [];
        
        // Đảm bảo booking có payment_code
        $paymentCode = $booking->payment_code ?? $booking->generatePaymentCode();
        
        return [
            'qr_code_url' => $this->generateQRCodeUrl($payment, $booking),
            'bank_name' => $bank['name'] ?? '',
            'bank_code' => $bank['code'] ?? '',
            'account_number' => $bank['account_number'] ?? '',
            'account_name' => $bank['account_name'] ?? '',
            'amount' => (int) $payment->amount,
            'amount_formatted' => number_format($payment->amount, 0, ',', '.') . ' VND',
            'transfer_content' => $this->generateTransferContent($paymentCode),
            'payment_code' => $paymentCode,
            'booking_number' => $booking->booking_number,
            'timeout_minutes' => $this->config['payment_timeout'] ?? 30,
            'expires_at' => now()->addMinutes($this->config['payment_timeout'] ?? 30)->toISOString(),
        ];
    }

    /**
     * Tạo payment record cho booking
     */
    public function createPayment(Booking|TourBooking $booking): Payment
    {
        $isRoomBooking = $booking instanceof Booking;
        
        // Tạo payment_code nếu chưa có
        if (empty($booking->payment_code)) {
            $booking->payment_code = $booking->generatePaymentCode();
            $booking->save();
        }
        
        $payment = new Payment();
        $payment->booking_id = $isRoomBooking ? $booking->id : null;
        $payment->tour_booking_id = !$isRoomBooking ? $booking->id : null;
        $payment->transaction_id = 'TXN_' . $booking->payment_code . '_' . time();
        $payment->payment_method = 'bank_transfer';
        $payment->gateway = 'sepay';
        $payment->amount = $booking->final_amount;
        $payment->currency = 'VND';
        $payment->status = 'pending';
        $payment->save();
        
        return $payment;
    }
}
