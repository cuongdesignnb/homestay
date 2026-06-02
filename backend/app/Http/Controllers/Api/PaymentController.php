<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\TourBooking;
use App\Services\SePayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function __construct(private readonly SePayService $sePayService)
    {
    }

    public function processSePay(Request $request)
    {
        $request->validate([
            'booking_id' => 'required_without:tour_booking_id',
            'tour_booking_id' => 'required_without:booking_id',
            'amount' => 'required|numeric',
        ]);

        if (!$this->sePayService->isConfigured()) {
            return response()->json([
                'message' => 'SePay has not been configured. Please contact the administrator.',
            ], 422);
        }

        // Get booking
        $booking = null;
        $tourBooking = null;

        if ($request->has('booking_id')) {
            $booking = Booking::findOrFail($request->booking_id);
        } else {
            $tourBooking = TourBooking::findOrFail($request->tour_booking_id);
        }

        // Create payment record
        $payment = Payment::create([
            'booking_id' => $booking?->id,
            'tour_booking_id' => $tourBooking?->id,
            'transaction_id' => 'SEPAY_' . Str::upper(Str::random(10)),
            'payment_method' => 'sepay',
            'amount' => $request->amount,
            'currency' => 'VND',
            'status' => 'pending',
        ]);

        // Create SePay checkout URL
        $checkoutUrl = $this->sePayService->createCheckoutUrl($payment, [
            'booking' => $booking,
            'tour_booking' => $tourBooking,
        ]);

        $payment->update([
            'gateway_response' => [
                'endpoint' => $this->sePayService->getEndpoint(),
                'checkout_url' => $checkoutUrl,
            ],
        ]);

        return response()->json([
            'payment' => $payment,
            'checkout_url' => $checkoutUrl,
        ]);
    }

    public function showSePayCheckout($transactionId)
    {
        $payment = Payment::where('transaction_id', $transactionId)->firstOrFail();
        
        $formData = $this->sePayService->getPaymentFormData($payment, [
            'booking' => $payment->booking,
            'tour_booking' => $payment->tourBooking,
        ]);
        
        return view('payments.sepay', [
            'action' => $formData['action'],
            'method' => $formData['method'],
            'fields' => $formData['fields']
        ]);
    }

    public function getPaymentForm($paymentId)
    {
        $payment = Payment::with(['booking', 'tourBooking'])->findOrFail($paymentId);
        
        if ($payment->gateway === 'sepay') {
            $formData = $this->sePayService->getPaymentFormData($payment, [
                'booking' => $payment->booking,
                'tour_booking' => $payment->tourBooking,
            ]);
            
            return response()->json([
                'payment' => $payment,
                'form' => $formData
            ]);
        }
        
        return response()->json(['message' => 'Payment method not supported'], 422);
    }

    public function processPayPal(Request $request)
    {
        $request->validate([
            'booking_id' => 'required_without:tour_booking_id',
            'tour_booking_id' => 'required_without:booking_id',
            'amount' => 'required|numeric',
        ]);

        // Get booking
        $booking = null;
        $tourBooking = null;

        if ($request->has('booking_id')) {
            $booking = Booking::findOrFail($request->booking_id);
        } else {
            $tourBooking = TourBooking::findOrFail($request->tour_booking_id);
        }

        // Create payment record
        $payment = Payment::create([
            'booking_id' => $booking?->id,
            'tour_booking_id' => $tourBooking?->id,
            'transaction_id' => 'PAYPAL_' . time() . rand(1000, 9999),
            'payment_method' => 'paypal',
            'amount' => $request->amount,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        // Call PayPal API (implement actual PayPal integration)
        $paypalResponse = $this->callPayPal([
            'amount' => $request->amount,
            'transaction_id' => $payment->transaction_id,
            'return_url' => config('app.frontend_url') . '/payment/callback',
        ]);

        $payment->update([
            'gateway_response' => $paypalResponse,
        ]);

        return response()->json([
            'payment' => $payment,
            'approval_url' => $paypalResponse['approval_url'] ?? null,
        ]);
    }

    public function callback(Request $request)
    {
        // Handle generic payment gateway callback
        $transactionId = $request->transaction_id;
        $status = $request->status;

        $payment = Payment::where('transaction_id', $transactionId)->firstOrFail();

        if ($status === 'success') {
            $payment->update([
                'status' => 'completed',
                'paid_at' => now(),
            ]);

            // Update booking payment status
            if ($payment->booking_id) {
                $payment->booking->update(['payment_status' => 'paid', 'status' => 'confirmed']);
            } elseif ($payment->tour_booking_id) {
                $payment->tourBooking->update(['payment_status' => 'paid', 'status' => 'confirmed']);
            }
        } else {
            $payment->update(['status' => 'failed']);
        }

        return response()->json(['message' => 'Payment processed', 'payment' => $payment]);
    }

    public function checkStatus($id)
    {
        $payment = Payment::findOrFail($id);
        return response()->json($payment);
    }

    private function callPayPal($data)
    {
        // Implement actual PayPal API call
        // This is a placeholder
        return [
            'approval_url' => 'https://www.paypal.com/checkoutnow?token=' . $data['transaction_id'],
            'status' => 'pending',
        ];
    }

    public function sepaySuccess(Request $request)
    {
        return $this->redirectFromGateway('success', $request->input('order_invoice_number'));
    }

    public function sepayError(Request $request)
    {
        return $this->redirectFromGateway('error', $request->input('order_invoice_number'));
    }

    public function sepayCancel(Request $request)
    {
        return $this->redirectFromGateway('cancel', $request->input('order_invoice_number'));
    }

    public function sepayIpn(Request $request)
    {
        $payload = $request->all();

        if (!$this->sePayService->verifySignature($payload)) {
            Log::warning('SePay IPN signature mismatch', ['payload' => $payload]);
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $orderInfo = $payload['order'] ?? [];
        $transactionId = $orderInfo['order_invoice_number'] ?? $payload['order_invoice_number'] ?? null;

        if (!$transactionId) {
            return response()->json(['message' => 'Missing invoice number'], 422);
        }

        $payment = Payment::where('transaction_id', $transactionId)->first();

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        $status = $payload['notification_type'] ?? null;
        $update = [
            'gateway_response' => array_merge($payment->gateway_response ?? [], ['ipn' => $payload]),
        ];

        if ($status === 'ORDER_PAID') {
            $update['status'] = 'completed';
            $update['paid_at'] = now();
            $this->markBookingAsPaid($payment);
        } elseif (in_array($status, ['ORDER_FAILED', 'ORDER_CANCELLED'])) {
            $update['status'] = 'failed';
            $this->markBookingAsFailed($payment);
        }

        $payment->update($update);

        return response()->json(['message' => 'IPN processed']);
    }

    protected function redirectFromGateway(string $status, ?string $transactionId = null)
    {
        $url = rtrim(config('app.frontend_url'), '/') . '/payment/result?status=' . $status;
        if ($transactionId) {
            $url .= '&transaction_id=' . $transactionId;
        }

        return redirect()->away($url);
    }

    protected function markBookingAsPaid(Payment $payment): void
    {
        if ($payment->booking) {
            $payment->booking->update([
                'payment_status' => 'paid',
                'status' => 'confirmed',
            ]);
        }

        if ($payment->tourBooking) {
            $payment->tourBooking->update([
                'payment_status' => 'paid',
                'status' => 'confirmed',
            ]);
        }
    }

    protected function markBookingAsFailed(Payment $payment): void
    {
        if ($payment->booking) {
            $payment->booking->update([
                'payment_status' => 'failed',
                'status' => 'pending',
            ]);
        }

        if ($payment->tourBooking) {
            $payment->tourBooking->update([
                'payment_status' => 'failed',
                'status' => 'pending',
            ]);
        }
    }

    /**
     * Handle SePay IPN callback
     */
    public function sePayCallback(Request $request)
    {
        Log::info('SePay IPN received', ['payload' => $request->all()]);

        $payload = $request->all();

        // Skip signature verification in sandbox/test mode for now
        // TODO: Fix signature verification when SePay provides proper documentation
        if ($this->sePayService->isConfigured()) {
            $isTestEnv = config('sepay.env') === 'sandbox' || app()->environment(['local', 'development']);
            
            if (!$isTestEnv && !$this->sePayService->verifySignature($payload)) {
                Log::warning('SePay IPN signature verification failed', ['payload' => $payload]);
                return response()->json(['message' => 'Invalid signature'], 400);
            } else {
                Log::info('SePay IPN signature verification skipped in test mode');
            }
        }

        // Extract order information - handle multiple payload formats
        $orderInfo = $payload['order'] ?? [];
        
        // Try different transaction ID fields
        $transactionId = $orderInfo['order_id'] 
            ?? $payload['order_id'] 
            ?? $payload['transaction_id']
            ?? $payload['order_invoice_number']
            ?? null;
            
        // Handle notification type variations
        $notificationType = $payload['notification_type'] ?? $payload['transaction_status'] ?? '';
        $orderStatus = $orderInfo['order_status'] ?? $payload['transaction_status'] ?? '';
        
        // Handle amount variations
        $amount = $orderInfo['order_amount'] 
            ?? $payload['order_amount']
            ?? $payload['transaction_amount'] 
            ?? 0;

        Log::info('Processing SePay IPN', [
            'transaction_id' => $transactionId,
            'notification_type' => $notificationType,
            'order_status' => $orderStatus,
            'amount' => $amount
        ]);

        // Find payment by transaction_id or create test payment
        $payment = null;
        if ($transactionId) {
            $payment = Payment::where('transaction_id', $transactionId)->first();
        }

        // For test payments that don't have existing records
        if (!$payment && $transactionId && str_contains($transactionId, 'TEST_ORDER')) {
            Log::info('Creating test payment record for SePay IPN', ['transaction_id' => $transactionId]);
            
            // Create a test payment record
            $payment = Payment::create([
                'transaction_id' => $transactionId,
                'payment_method' => 'sepay',
                'amount' => $amount,
                'currency' => 'VND',
                'status' => 'pending',
                'gateway_response' => ['test' => true, 'ipn_payload' => $payload],
            ]);
        }

        if (!$payment) {
            Log::warning('Payment not found for SePay IPN', ['transaction_id' => $transactionId]);
            return response()->json(['message' => 'Payment not found'], 404);
        }

        // Update payment based on notification type and order status
        $update = [
            'gateway_response' => array_merge($payment->gateway_response ?? [], ['ipn' => $payload]),
        ];

        // Handle multiple success status formats
        $successStatuses = ['PAYMENT_SUCCESS', 'CAPTURED', 'APPROVED', 'COMPLETED', 'SUCCESS'];
        $failedStatuses = ['FAILED', 'CANCELLED', 'PAYMENT_FAILED', 'DENIED', 'REJECTED'];
        
        if (in_array($notificationType, $successStatuses) || in_array($orderStatus, $successStatuses)) {
            $update['status'] = 'completed';
            $update['paid_at'] = now();
            $this->markBookingAsPaid($payment);
            Log::info('Payment marked as completed', ['payment_id' => $payment->id]);
        } elseif (in_array($orderStatus, $failedStatuses) || in_array($notificationType, $failedStatuses)) {
            $update['status'] = 'failed';
            $this->markBookingAsFailed($payment);
            Log::info('Payment marked as failed', ['payment_id' => $payment->id]);
        }

        $payment->update($update);

        return response()->json(['message' => 'SePay IPN processed successfully']);
    }

    /**
     * Handle PayPal IPN callback
     */
    public function paypalCallback(Request $request)
    {
        Log::info('PayPal IPN received', ['payload' => $request->all()]);

        $payload = $request->all();
        $transactionId = $payload['txn_id'] ?? $payload['transaction_id'] ?? null;
        $paymentStatus = $payload['payment_status'] ?? '';

        if (!$transactionId) {
            return response()->json(['message' => 'Missing transaction ID'], 422);
        }

        $payment = Payment::where('transaction_id', $transactionId)
            ->orWhere('transaction_id', 'PAYPAL_' . $transactionId)
            ->first();

        if (!$payment) {
            Log::warning('Payment not found for PayPal IPN', ['transaction_id' => $transactionId]);
            return response()->json(['message' => 'Payment not found'], 404);
        }

        $update = [
            'gateway_response' => array_merge($payment->gateway_response ?? [], ['ipn' => $payload]),
        ];

        if ($paymentStatus === 'Completed') {
            $update['status'] = 'completed';
            $update['paid_at'] = now();
            $this->markBookingAsPaid($payment);
        } elseif (in_array($paymentStatus, ['Failed', 'Denied', 'Cancelled_Reversal'])) {
            $update['status'] = 'failed';
            $this->markBookingAsFailed($payment);
        }

        $payment->update($update);

        return response()->json(['message' => 'PayPal IPN processed successfully']);
    }

    /**
     * Handle SePay Webhook for QR Code payments
     * 
     * SePay sends webhook when bank transfer is received
    * Matches payment by transfer content (HAPPYISLANDTOUR{booking_number})
     */
    public function sePayWebhook(Request $request)
    {
        Log::info('SePay Webhook received', ['payload' => $request->all()]);

        $payload = $request->all();
        
        // Verify webhook token
        $webhookToken = config('sepay.webhook_token');
        $receivedToken = $request->header('Authorization') 
            ?? $request->header('X-SePay-Token')
            ?? $payload['webhook_token'] 
            ?? null;
            
        // Bearer token format check
        if (str_starts_with($receivedToken ?? '', 'Bearer ')) {
            $receivedToken = substr($receivedToken, 7);
        }
        
        if ($webhookToken && $receivedToken !== $webhookToken) {
            Log::warning('SePay Webhook token mismatch', [
                'expected' => substr($webhookToken, 0, 8) . '...',
                'received' => substr($receivedToken ?? '', 0, 8) . '...'
            ]);
            return response()->json(['success' => false, 'message' => 'Invalid token'], 401);
        }

        // Extract transaction details from SePay webhook
        $transferContent = $payload['content'] ?? $payload['transferContent'] ?? $payload['description'] ?? '';
        $amount = (float) ($payload['transferAmount'] ?? $payload['amount'] ?? 0);
        $bankRef = $payload['referenceNumber'] ?? $payload['id'] ?? null;
        $gateway = $payload['gateway'] ?? $payload['bankName'] ?? null;
        
        Log::info('Processing SePay Webhook', [
            'content' => $transferContent,
            'amount' => $amount,
            'bankRef' => $bankRef
        ]);

        // Extract booking number from transfer content
        // Pattern: HAPPYISLANDTOUR{booking_number} e.g., HAPPYISLANDTOURBK240001
        $pattern = config('sepay.pattern', 'HAPPYISLANDTOUR');
        if (preg_match("/{$pattern}([A-Z0-9]+)/i", $transferContent, $matches)) {
            $bookingNumber = $matches[1];
            
            // Find booking by booking_number
            $booking = \App\Models\Booking::where('booking_number', $bookingNumber)->first();
            
            if (!$booking) {
                // Try with full match
                $booking = \App\Models\Booking::where('booking_number', $matches[0])->first();
            }
            
            if (!$booking) {
                Log::warning('Booking not found for SePay webhook', ['booking_number' => $bookingNumber]);
                return response()->json(['success' => false, 'message' => 'Booking not found'], 404);
            }
            
            // Find payment
            $payment = Payment::where('booking_id', $booking->id)
                ->where('status', 'pending')
                ->first();
                
            if (!$payment) {
                Log::warning('Payment not found for booking', ['booking_id' => $booking->id]);
                return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
            }
            
            // Verify amount matches (with small tolerance for rounding)
            $expectedAmount = (float) $payment->amount;
            if (abs($amount - $expectedAmount) > 100) { // 100 VND tolerance
                Log::warning('Payment amount mismatch', [
                    'expected' => $expectedAmount,
                    'received' => $amount
                ]);
                return response()->json([
                    'success' => false, 
                    'message' => 'Amount mismatch'
                ], 422);
            }
            
            // Update payment
            $payment->update([
                'status' => 'completed',
                'paid_at' => now(),
                'gateway_response' => array_merge($payment->gateway_response ?? [], [
                    'webhook' => $payload,
                    'bank_ref' => $bankRef,
                    'gateway' => $gateway,
                ]),
            ]);
            
            // Mark booking as paid
            $this->markBookingAsPaid($payment);
            
            Log::info('SePay payment confirmed via webhook', [
                'payment_id' => $payment->id,
                'booking_number' => $bookingNumber
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Payment confirmed'
            ]);
        }
        
        Log::info('No matching booking pattern in transfer content', ['content' => $transferContent]);
        
        // Return success anyway to acknowledge receipt
        return response()->json([
            'success' => true,
            'message' => 'Webhook received, no matching booking'
        ]);
    }
}
