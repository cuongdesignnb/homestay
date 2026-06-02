<?php

namespace App\Listeners;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\TourBooking;
use Illuminate\Support\Facades\Log;
use SePay\SePay\Events\SePayWebhookEvent;

class SePayWebhookListener
{
    /**
     * Handle the event.
     * 
     * SePay gửi webhook khi có giao dịch match pattern HT + số
     * $event->info chứa phần sau pattern (VD: "HT1234567890" -> "1234567890")
     */
    public function handle(SePayWebhookEvent $event): void
    {
        Log::info('SePay Webhook received', [
            'transferType' => $event->sePayWebhookData->transferType,
            'content' => $event->sePayWebhookData->content,
            'amount' => $event->sePayWebhookData->transferAmount,
            'info' => $event->info,
        ]);

        // Chỉ xử lý tiền vào tài khoản
        if ($event->sePayWebhookData->transferType !== 'in') {
            Log::info('SePay Webhook: Ignoring outgoing transfer');
            return;
        }

        // $event->info chứa payment_code (chỉ số, 3-10 ký tự)
        $paymentCode = trim($event->info);
        
        if (empty($paymentCode)) {
            Log::warning('SePay Webhook: No payment code found in content');
            return;
        }

        // Tìm booking theo payment_code
        $booking = Booking::where('payment_code', $paymentCode)->first();
        $tourBooking = null;
        
        if (!$booking) {
            // Thử tìm tour booking
            $tourBooking = TourBooking::where('payment_code', $paymentCode)->first();
        }

        if (!$booking && !$tourBooking) {
            Log::warning('SePay Webhook: Booking not found', ['payment_code' => $paymentCode]);
            return;
        }

        $targetBooking = $booking ?? $tourBooking;
        $bookingType = $booking ? 'room' : 'tour';
        
        // Kiểm tra đã thanh toán chưa
        if ($targetBooking->payment_status === 'paid') {
            Log::info('SePay Webhook: Already paid', ['payment_code' => $paymentCode]);
            return;
        }

        // Kiểm tra số tiền
        $expectedAmount = (int) $targetBooking->final_amount;
        $receivedAmount = (int) $event->sePayWebhookData->transferAmount;
        
        if ($receivedAmount < $expectedAmount) {
            Log::warning('SePay Webhook: Amount mismatch', [
                'expected' => $expectedAmount,
                'received' => $receivedAmount,
                'payment_code' => $paymentCode,
            ]);
            // Vẫn tiếp tục xử lý nhưng ghi log cảnh báo
        }

        // Cập nhật payment record
        $payment = Payment::where($bookingType === 'room' ? 'booking_id' : 'tour_booking_id', $targetBooking->id)
            ->where('status', 'pending')
            ->first();

        if ($payment) {
            $payment->update([
                'status' => 'completed',
                'paid_at' => now(),
                'gateway_response' => json_encode([
                    'sepay_id' => $event->sePayWebhookData->id,
                    'reference_code' => $event->sePayWebhookData->referenceCode,
                    'transaction_date' => $event->sePayWebhookData->transactionDate,
                    'gateway' => $event->sePayWebhookData->gateway,
                    'amount_received' => $receivedAmount,
                ]),
            ]);
        }

        // Cập nhật booking status
        $targetBooking->update([
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        Log::info('SePay Webhook: Payment confirmed', [
            'payment_code' => $paymentCode,
            'booking_number' => $targetBooking->booking_number,
            'booking_type' => $bookingType,
            'amount' => $receivedAmount,
        ]);
    }
}
