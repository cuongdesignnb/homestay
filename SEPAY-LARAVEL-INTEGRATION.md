# Hướng dẫn tích hợp SePay vào Laravel (Webhook + QR Code)

> **Tài liệu chuẩn** - Đã test thành công với hệ thống Homestay Booking

## 📋 Tổng quan

SePay là cổng thanh toán cho phép nhận tiền qua chuyển khoản ngân hàng và tự động xác nhận thanh toán thông qua webhook.

**Luồng hoạt động:**

1. Khách hàng tạo đơn hàng → Hệ thống tạo mã thanh toán unique
2. Hiển thị QR Code chứa thông tin chuyển khoản (STK, số tiền, nội dung)
3. Khách chuyển khoản với đúng nội dung
4. SePay phát hiện giao dịch → Gọi webhook đến server
5. Server xác nhận đơn hàng tự động

---

## 🔧 Bước 1: Cài đặt Package

```bash
composer require sepayvn/laravel-sepay
```

**Publish config:**

```bash
php artisan vendor:publish --provider="SePayVN\SePay\SePayServiceProvider"
```

---

## 🔧 Bước 2: Cấu hình Environment (.env)

```env
# SePay Configuration
SEPAY_API_TOKEN=your_api_token_here
SEPAY_WEBHOOK_TOKEN=your_webhook_token_here
SEPAY_BANK_ACCOUNT_NUMBER=your_bank_account_number
SEPAY_BANK_NAME=MB
SEPAY_ACCOUNT_NAME=TEN_CHU_TAI_KHOAN

# Pattern để match nội dung chuyển khoản
# QUAN TRỌNG: Chỉ dùng chữ cái, suffix phải là số nguyên 3-10 chữ số
SEPAY_MATCH_PATTERN=HT
```

### ⚠️ LƯU Ý QUAN TRỌNG VỀ PATTERN

SePay Dashboard yêu cầu **suffix phải là số nguyên từ 3-10 chữ số**:

- ✅ `HT1234567890` - Đúng (HT + 10 số)
- ✅ `DH123456` - Đúng (DH + 6 số)
- ❌ `HOMESTAY BK123` - Sai (có khoảng trắng, có chữ trong suffix)
- ❌ `HT-123456` - Sai (có ký tự đặc biệt)

---

## 🔧 Bước 3: Cấu hình config/sepay.php

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SePay API Token
    |--------------------------------------------------------------------------
    */
    'api_token' => env('SEPAY_API_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Webhook Token - Dùng để xác thực webhook từ SePay
    |--------------------------------------------------------------------------
    */
    'webhook_token' => env('SEPAY_WEBHOOK_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Match Pattern - Prefix cho nội dung chuyển khoản
    |--------------------------------------------------------------------------
    */
    'match_pattern' => env('SEPAY_MATCH_PATTERN', 'DH'),

    /*
    |--------------------------------------------------------------------------
    | Bank Account Information
    |--------------------------------------------------------------------------
    */
    'bank_account_number' => env('SEPAY_BANK_ACCOUNT_NUMBER', ''),
    'bank_name' => env('SEPAY_BANK_NAME', 'MB'),
    'account_name' => env('SEPAY_ACCOUNT_NAME', ''),
];
```

---

## 🔧 Bước 4: Tạo Migration cho Payment Code

Thêm cột `payment_code` vào bảng orders/bookings:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Payment code: 10 chữ số, dùng cho nội dung chuyển khoản
            $table->string('payment_code', 20)->nullable()->unique()->after('booking_number');
            $table->index('payment_code');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['payment_code']);
            $table->dropColumn('payment_code');
        });
    }
};
```

---

## 🔧 Bước 5: Cập nhật Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        // ... other fields
        'payment_code',
        'payment_status', // 'unpaid', 'paid'
    ];

    /**
     * Generate unique payment code (10 digits only)
     * Format: timestamp (6 digits) + random (4 digits)
     */
    public static function generatePaymentCode(): string
    {
        do {
            // Lấy 6 số cuối của timestamp + 4 số random
            $timestamp = substr(time(), -6);
            $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $code = $timestamp . $random;
        } while (self::where('payment_code', $code)->exists());

        return $code;
    }

    /**
     * Get transfer content for bank transfer
     * Format: {PATTERN}{payment_code}
     * Example: HT1234567890
     */
    public function getTransferContent(): string
    {
        $pattern = config('sepay.match_pattern', 'HT');
        return $pattern . $this->payment_code;
    }

    /**
     * Boot method to auto-generate payment code
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->payment_code)) {
                $booking->payment_code = self::generatePaymentCode();
            }
        });
    }
}
```

---

## 🔧 Bước 6: Tạo SePay Service

```php
<?php

namespace App\Services;

use App\Models\Booking;

class SePayService
{
    protected string $bankAccount;
    protected string $bankName;
    protected string $accountName;
    protected string $pattern;

    public function __construct()
    {
        $this->bankAccount = config('sepay.bank_account_number');
        $this->bankName = config('sepay.bank_name');
        $this->accountName = config('sepay.account_name');
        $this->pattern = config('sepay.match_pattern', 'HT');
    }

    /**
     * Generate QR Code URL for payment
     */
    public function generateQRCode(Booking $booking): string
    {
        $amount = (int) $booking->final_amount;
        $content = $this->pattern . $booking->payment_code;

        // SePay QR URL format
        return "https://qr.sepay.vn/img?acc={$this->bankAccount}&bank={$this->bankName}&amount={$amount}&des={$content}";
    }

    /**
     * Get payment information for display
     */
    public function getPaymentInfo(Booking $booking): array
    {
        return [
            'qr_url' => $this->generateQRCode($booking),
            'bank_name' => $this->getBankFullName(),
            'account_number' => $this->bankAccount,
            'account_name' => $this->accountName,
            'amount' => (int) $booking->final_amount,
            'amount_formatted' => number_format($booking->final_amount, 0, ',', '.') . ' VNĐ',
            'transfer_content' => $this->pattern . $booking->payment_code,
            'payment_code' => $booking->payment_code,
        ];
    }

    /**
     * Get full bank name from short code
     */
    protected function getBankFullName(): string
    {
        $banks = [
            'MB' => 'MB Bank (Ngân hàng Quân đội)',
            'VCB' => 'Vietcombank',
            'TCB' => 'Techcombank',
            'ACB' => 'ACB',
            'VPB' => 'VPBank',
            'TPB' => 'TPBank',
            'BIDV' => 'BIDV',
            'VTB' => 'Vietinbank',
            'MSB' => 'MSB',
            'SHB' => 'SHB',
        ];

        return $banks[$this->bankName] ?? $this->bankName;
    }
}
```

---

## 🔧 Bước 7: Tạo Webhook Listener

```php
<?php

namespace App\Listeners;

use App\Models\Booking;
use App\Models\SePayTransaction;
use Illuminate\Support\Facades\Log;
use SePayVN\SePay\Events\SePayWebhookEvent;

class SePayWebhookListener
{
    public function handle(SePayWebhookEvent $event): void
    {
        $data = $event->data;

        Log::info('SePay Webhook received', $data);

        // Chỉ xử lý giao dịch tiền vào
        if (($data['transferType'] ?? '') !== 'in') {
            Log::info('SePay Webhook: Skipping outgoing transfer');
            return;
        }

        // Lấy nội dung chuyển khoản
        $content = $data['content'] ?? '';
        $pattern = config('sepay.match_pattern', 'HT');

        // Tìm payment code trong nội dung
        // Pattern: HT + 10 digits
        if (preg_match('/' . preg_quote($pattern, '/') . '(\d{10})/', $content, $matches)) {
            $paymentCode = $matches[1];

            Log::info("SePay Webhook: Found payment code: {$paymentCode}");

            // Tìm booking theo payment_code
            $booking = Booking::where('payment_code', $paymentCode)
                ->where('payment_status', 'unpaid')
                ->first();

            if ($booking) {
                // Kiểm tra số tiền
                $paidAmount = (int) ($data['transferAmount'] ?? 0);
                $expectedAmount = (int) $booking->final_amount;

                if ($paidAmount >= $expectedAmount) {
                    // Cập nhật trạng thái
                    $booking->update([
                        'payment_status' => 'paid',
                        'status' => 'confirmed',
                        'paid_at' => now(),
                    ]);

                    // Lưu transaction log (optional)
                    SePayTransaction::create([
                        'booking_id' => $booking->id,
                        'transaction_id' => $data['id'] ?? null,
                        'gateway' => $data['gateway'] ?? null,
                        'amount' => $paidAmount,
                        'content' => $content,
                        'account_number' => $data['accountNumber'] ?? null,
                        'reference_number' => $data['referenceNumber'] ?? null,
                        'transaction_date' => $data['transactionDate'] ?? null,
                        'raw_data' => json_encode($data),
                    ]);

                    Log::info("SePay Webhook: Payment confirmed for booking {$booking->booking_number}");
                } else {
                    Log::warning("SePay Webhook: Amount mismatch. Paid: {$paidAmount}, Expected: {$expectedAmount}");
                }
            } else {
                Log::warning("SePay Webhook: Booking not found for payment code: {$paymentCode}");
            }
        } else {
            Log::info("SePay Webhook: No matching pattern found in content: {$content}");
        }
    }
}
```

---

## 🔧 Bước 8: Đăng ký Event Listener

**app/Providers/EventServiceProvider.php:**

```php
<?php

namespace App\Providers;

use App\Listeners\SePayWebhookListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use SePayVN\SePay\Events\SePayWebhookEvent;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        SePayWebhookEvent::class => [
            SePayWebhookListener::class,
        ],
    ];
}
```

---

## 🔧 Bước 9: Tạo Migration cho Transaction Log (Optional)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sepay_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('set null');
            $table->string('transaction_id')->nullable();
            $table->string('gateway')->nullable();
            $table->decimal('amount', 15, 2);
            $table->text('content')->nullable();
            $table->string('account_number')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('transaction_date')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->index('transaction_id');
            $table->index('booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sepay_transactions');
    }
};
```

---

## 🔧 Bước 10: API Controller cho Payment

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\SePayService;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    protected SePayService $sePayService;

    public function __construct(SePayService $sePayService)
    {
        $this->sePayService = $sePayService;
    }

    /**
     * Get payment info for a booking
     */
    public function getPaymentInfo(string $bookingNumber): JsonResponse
    {
        $booking = Booking::where('booking_number', $bookingNumber)->firstOrFail();

        if ($booking->payment_status === 'paid') {
            return response()->json([
                'status' => 'paid',
                'message' => 'Booking đã được thanh toán',
            ]);
        }

        $paymentInfo = $this->sePayService->getPaymentInfo($booking);

        return response()->json([
            'status' => 'pending',
            'payment_info' => $paymentInfo,
        ]);
    }

    /**
     * Check payment status
     */
    public function checkStatus(string $bookingNumber): JsonResponse
    {
        $booking = Booking::where('booking_number', $bookingNumber)->firstOrFail();

        return response()->json([
            'payment_status' => $booking->payment_status,
            'booking_status' => $booking->status,
            'is_paid' => $booking->payment_status === 'paid',
        ]);
    }
}
```

---

## 🔧 Bước 11: Routes

```php
// routes/api.php

// Payment routes
Route::get('/payment/{bookingNumber}', [PaymentController::class, 'getPaymentInfo']);
Route::get('/payment/{bookingNumber}/status', [PaymentController::class, 'checkStatus']);

// SePay webhook route (đã được package tự động thêm tại /api/sepay/webhook)
```

---

## 🌐 Bước 12: Cấu hình SePay Dashboard

1. Đăng nhập vào https://my.sepay.vn
2. Vào **Cài đặt Webhook**
3. Cấu hình:
   - **Webhook URL:** `https://your-domain.com/api/sepay/webhook`
   - **Webhook Token:** Copy token và paste vào `.env` (SEPAY_WEBHOOK_TOKEN)
   - **Match Pattern:** `HT` (hoặc pattern bạn chọn)
   - **Suffix:** Chọn "Số nguyên" và set "3-10 ký tự"

---

## 🎨 Frontend: Component QR Payment (Vue 3)

```vue
<template>
  <div class="qr-payment">
    <h2>Thanh toán chuyển khoản</h2>

    <div v-if="loading" class="loading">Đang tải...</div>

    <div v-else-if="isPaid" class="paid-notice">
      <span class="icon">✅</span>
      <p>Đã thanh toán thành công!</p>
    </div>

    <div v-else class="payment-info">
      <!-- QR Code -->
      <div class="qr-section">
        <img :src="paymentInfo.qr_url" alt="QR Code" class="qr-image" />
        <p class="scan-hint">Quét mã QR bằng app ngân hàng</p>
      </div>

      <!-- Bank Info -->
      <div class="bank-info">
        <div class="info-row">
          <span class="label">Ngân hàng:</span>
          <span class="value">{{ paymentInfo.bank_name }}</span>
        </div>
        <div class="info-row">
          <span class="label">Số tài khoản:</span>
          <span
            class="value copyable"
            @click="copy(paymentInfo.account_number)"
          >
            {{ paymentInfo.account_number }}
            <span class="copy-icon">📋</span>
          </span>
        </div>
        <div class="info-row">
          <span class="label">Chủ tài khoản:</span>
          <span class="value">{{ paymentInfo.account_name }}</span>
        </div>
        <div class="info-row">
          <span class="label">Số tiền:</span>
          <span class="value amount">{{ paymentInfo.amount_formatted }}</span>
        </div>
        <div class="info-row">
          <span class="label">Nội dung CK:</span>
          <span
            class="value copyable"
            @click="copy(paymentInfo.transfer_content)"
          >
            {{ paymentInfo.transfer_content }}
            <span class="copy-icon">📋</span>
          </span>
        </div>
      </div>

      <!-- Warning -->
      <div class="warning">
        ⚠️ Vui lòng nhập <strong>chính xác</strong> nội dung chuyển khoản để hệ
        thống tự động xác nhận.
      </div>

      <!-- Auto-check status -->
      <p class="checking-status">
        🔄 Đang chờ thanh toán... (tự động kiểm tra mỗi 5 giây)
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import api from "@/services/api";

const props = defineProps({
  bookingNumber: { type: String, required: true },
});

const emit = defineEmits(["paid"]);

const loading = ref(true);
const isPaid = ref(false);
const paymentInfo = ref({});
let checkInterval = null;

const fetchPaymentInfo = async () => {
  try {
    const response = await api.get(`/payment/${props.bookingNumber}`);
    if (response.data.status === "paid") {
      isPaid.value = true;
      emit("paid");
    } else {
      paymentInfo.value = response.data.payment_info;
    }
  } catch (error) {
    console.error("Error fetching payment info:", error);
  } finally {
    loading.value = false;
  }
};

const checkPaymentStatus = async () => {
  try {
    const response = await api.get(`/payment/${props.bookingNumber}/status`);
    if (response.data.is_paid) {
      isPaid.value = true;
      emit("paid");
      clearInterval(checkInterval);
    }
  } catch (error) {
    console.error("Error checking status:", error);
  }
};

const copy = (text) => {
  navigator.clipboard.writeText(text);
  alert("Đã copy: " + text);
};

onMounted(() => {
  fetchPaymentInfo();
  // Check payment status every 5 seconds
  checkInterval = setInterval(checkPaymentStatus, 5000);
});

onUnmounted(() => {
  if (checkInterval) {
    clearInterval(checkInterval);
  }
});
</script>
```

---

## ✅ Checklist Triển khai

- [ ] Cài đặt package `sepayvn/laravel-sepay`
- [ ] Cấu hình `.env` với token và thông tin ngân hàng
- [ ] Tạo migration cho `payment_code`
- [ ] Cập nhật Model với `generatePaymentCode()`
- [ ] Tạo `SePayService`
- [ ] Tạo `SePayWebhookListener`
- [ ] Đăng ký Event Listener
- [ ] Tạo API endpoints
- [ ] Cấu hình webhook trên SePay Dashboard
- [ ] Test với giao dịch thật

---

## 🐛 Troubleshooting

### Webhook không được gọi

1. Kiểm tra URL webhook đúng: `https://domain.com/api/sepay/webhook`
2. Kiểm tra HTTPS (bắt buộc)
3. Kiểm tra pattern trên SePay Dashboard

### Webhook gọi nhưng không match

1. Kiểm tra pattern trong `.env` khớp với SePay Dashboard
2. Kiểm tra suffix là số nguyên 3-10 chữ số
3. Xem log: `storage/logs/laravel.log`

### Debug Webhook

```php
// Thêm vào đầu listener để debug
Log::info('SePay Webhook RAW', ['data' => $event->data]);
```

### Test thủ công

```bash
curl -X POST https://your-domain.com/api/sepay/webhook \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_WEBHOOK_TOKEN" \
  -d '{
    "id": 123456,
    "gateway": "MBBank",
    "transactionDate": "2025-12-04 10:00:00",
    "accountNumber": "1234567890",
    "transferType": "in",
    "transferAmount": 500000,
    "content": "HT1234567890",
    "referenceNumber": "FT123456"
  }'
```

---

## 📚 Tham khảo

- [SePay Documentation](https://docs.sepay.vn)
- [Laravel SePay Package](https://github.com/sepayvn/laravel-sepay)
- [SePay Dashboard](https://my.sepay.vn)

---

**Tác giả:** Homestay Booking Team  
**Cập nhật:** December 2025  
**Version:** 1.0
