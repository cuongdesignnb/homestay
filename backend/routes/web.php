<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaymentController;

Route::get('/', function () {
    return response()->json([
        'message' => 'Happy Island Tour API',
        'version' => '1.0.0',
        'status' => 'active'
    ]);
});

// SePay Webhook route - for QR Code payment notifications
// This is called by SePay server when a bank transfer is received
Route::post('/payment/sepay/webhook', [PaymentController::class, 'sePayWebhook'])
    ->name('payments.sepay.webhook')
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
