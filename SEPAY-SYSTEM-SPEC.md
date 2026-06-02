# Chuẩn mực tích hợp SePay trong Homestay Booking System

> Tài liệu mô tả **đúng theo code hiện tại** trong dự án này, dùng làm chuẩn tham chiếu cho dự án khác.

---

## 1) Tổng quan kiến trúc SePay trong hệ thống

Hệ thống đang có **2 cơ chế xử lý SePay** song song:

1. **Webhook + QR chuyển khoản (VietQR)**
   - Tạo QR từ cấu hình ngân hàng.
   - Người dùng chuyển khoản theo **nội dung cố định**.
   - SePay gửi webhook, backend xác nhận và cập nhật `payment` + `booking`.

2. **SePay Checkout/Redirect + IPN**
   - Frontend gọi `/payments/sepay`, nhận `checkout_url` rồi redirect.
   - Backend nhận IPN callback để cập nhật thanh toán.

> Lưu ý: phần Checkout/Redirect dùng các hàm trong `SePayService` **nhưng các hàm này hiện không tồn tại** trong file service (chi tiết ở mục 6). Vì vậy flow này **có khả năng chưa chạy hoàn chỉnh** nếu dùng đúng code hiện tại.

---

## 2) Cấu hình SePay

### 2.1 File cấu hình
- `backend/config/sepay.php`

Các key chính:
- `webhook_token`: token xác thực webhook (env: `SEPAY_WEBHOOK_TOKEN`)
- `pattern`: prefix dùng để match nội dung chuyển khoản (env: `SEPAY_MATCH_PATTERN`, mặc định `HT`)
- `bank`: cấu hình ngân hàng để tạo QR
  - `name` (env: `SEPAY_BANK_NAME`)
  - `code` (env: `SEPAY_BANK_CODE`, ví dụ `MB`)
  - `account_number` (env: `SEPAY_BANK_ACCOUNT`)
  - `account_name` (env: `SEPAY_BANK_ACCOUNT_NAME`)
- `payment_timeout`: thời gian hết hạn QR (env: `SEPAY_PAYMENT_TIMEOUT`, mặc định 30 phút)
- `qr_api_url`: nguồn ảnh VietQR (mặc định `https://img.vietqr.io/image`)

### 2.2 Token xác thực webhook
Backend chấp nhận token ở các vị trí:
- Header `Authorization` (Bearer)
- Header `X-SePay-Token`
- Body `webhook_token`

---

## 3) Model & Database liên quan

### 3.1 Booking
- File: `backend/app/Models/Booking.php`
- Field quan trọng:
  - `payment_code`: mã thanh toán dùng để ghép nội dung chuyển khoản.
- Hàm tạo mã:
  - `generatePaymentCode()` → 10 chữ số: 6 số cuối timestamp + 4 số random.

### 3.2 Payment
- File: `backend/app/Models/Payment.php`
- Bảng `payments` (migration `2024_01_01_000005_create_payments_table.php`)
- Field chính:
  - `transaction_id` (unique)
  - `payment_method` (`sepay`, `paypal`, `cash`, `bank_transfer`)
  - `gateway_response` (JSON)
  - `status` (`pending`, `completed`, `failed`, `refunded`)

### 3.3 SePayTransaction (log từ package)
- Từ package `sepayvn/laravel-sepay`
- Model: `SePay\SePay\Models\SePayTransaction`
- Lưu log giao dịch webhook trong bảng `sepay_transactions` (được package tạo/migration).

---

## 4) Service SePay (QR Transfer)

### File
- `backend/app/Services/SePayService.php`

### Chức năng chính
1. **Kiểm tra cấu hình**
   ```php
   isConfigured(): bool
   ```
   - Yêu cầu có `bank.account_number` và `webhook_token`.

2. **Tạo nội dung chuyển khoản**
   ```php
   generateTransferContent(string $paymentCode): string
   ```
   - Format: `{pattern}{payment_code}` (mặc định `HT1234567890`).

3. **Tạo URL QR VietQR**
   ```php
   generateQRCodeUrl(Payment $payment, $booking): string
   ```
   - URL dạng:
     ```
     https://img.vietqr.io/image/{BANK_CODE}-{ACCOUNT}-compact2.png?amount={AMOUNT}&addInfo={CONTENT}&accountName={ACCOUNT_NAME}
     ```

4. **Trả về payment info cho frontend**
   ```php
   getPaymentInfo(Payment $payment, $booking): array
   ```
   - Trả về:
     - `qr_code_url`
     - `bank_name`, `bank_code`, `account_number`, `account_name`
     - `amount`, `amount_formatted`
     - `transfer_content`
     - `payment_code`, `booking_number`
     - `timeout_minutes`, `expires_at`

5. **Tạo bản ghi Payment**
   ```php
   createPayment(Booking|TourBooking $booking): Payment
   ```
   - Tạo `payment_code` nếu chưa có.
   - `transaction_id` dạng `TXN_{payment_code}_{timestamp}`.
   - `payment_method` = `bank_transfer`, `gateway` = `sepay`.

---

## 5) Luồng QR chuyển khoản (Webhook + QR)

### 5.1 Tạo Booking và Payment
- File: `backend/app/Http/Controllers/Api/BookingController.php`
- Khi `payment_method != pay_at_checkin`:
  - Gọi `SePayService::createPayment($booking)`.
  - Gọi `SePayService::getPaymentInfo($payment, $booking)` để trả về QR info.

Kết quả API trả về:
```json
{
  "message": "Booking created successfully",
  "booking": { ... },
  "payment": {
    "id": 1,
    "transaction_id": "TXN_1234567890_1700000000",
    "qr_code_url": "...",
    "bank_name": "...",
    "account_number": "...",
    "account_name": "...",
    "amount": 1500000,
    "amount_formatted": "1,500,000 VND",
    "transfer_content": "HT1234567890",
    "booking_number": "BK202401XXXX",
    "expires_at": "..."
  }
}
```

### 5.2 Frontend hiển thị QR và kiểm tra trạng thái
- File: `frontend/src/views/payment/QRPayment.vue`
- Nhận `payment` và `booking` qua `history.state` hoặc query.
- Hiển thị:
  - QR Code (`qr_code_url`)
  - Thông tin ngân hàng
  - `amount`, `transfer_content`
  - Countdown đến `expires_at`
- Auto-check status mỗi 10 giây:
  - Gọi `GET /payments/{id}/status`
  - Nếu `status = completed` → chuyển sang trang xác nhận.

### 5.3 Endpoint check trạng thái
- File: `backend/routes/api.php`
- Route: `GET /api/payments/{id}/status`
- Controller: `PaymentController::checkStatus()`
- Trả về toàn bộ payment record (bao gồm `status`).

---

## 6) Webhook xác nhận thanh toán

### 6.1 Webhook theo QR (PaymentController)
- File: `backend/app/Http/Controllers/Api/PaymentController.php`
- Route: `POST /api/payment/sepay/webhook`

**Logic chính**
1. Verify token (Authorization / X-SePay-Token / webhook_token).
2. Lấy dữ liệu:
   - `content` / `transferContent` / `description`
   - `transferAmount`
   - `referenceNumber` / `id`
   - `gateway` / `bankName`
3. Parse `booking_number` từ `content` với regex dựa trên `config('sepay.pattern', 'HOMESTAY')`.
4. Tìm `booking` theo `booking_number`.
5. Tìm `payment` pending của booking.
6. So khớp số tiền (sai lệch > 100 VND sẽ trả lỗi).
7. Cập nhật `payment.status = completed`, `paid_at = now()`.
8. Gọi `markBookingAsPaid()` để set:
   - `booking.status = confirmed`
   - `booking.payment_status = paid`

**Lưu ý quan trọng**
- Ở đây regex mặc định dùng pattern `HOMESTAY` trong khi config mặc định là `HT`. Nếu không chỉnh `SEPAY_MATCH_PATTERN`, webhook có thể không match.

### 6.2 Webhook theo package (SePayWebhookController + Listener)
- Controller: `backend/app/Http/Controllers/Api/SePayWebhookController.php`
- Listener: `backend/app/Listeners/SePayWebhookListener.php`
- Event đăng ký tại `backend/bootstrap/app.php`:
  ```php
  Event::listen(
      \SePay\SePay\Events\SePayWebhookEvent::class,
      \App\Listeners\SePayWebhookListener::class
  );
  ```

**Luồng**
1. `SePayWebhookController::webhook()` parse payload → tạo `SePayWebhookData`.
2. Lưu transaction vào bảng `sepay_transactions`.
3. Match `pattern` trong `content` để lấy `payment_code`.
4. `event(new SePayWebhookEvent($info, $sePayWebhookData))`.
5. `SePayWebhookListener` xử lý:
   - Chỉ nhận `transferType = in`.
   - Tìm booking/tour booking theo `payment_code`.
   - Cập nhật `payment.status = completed`.
   - Set booking `status = confirmed`, `payment_status = paid`.

**Quan trọng**
- Hiện tại **không có route trực tiếp trỏ tới `SePayWebhookController` trong `routes/api.php`**.
- File debug `test-sepay-webhook.php` gọi endpoint `/api/sepay/webhook`, nên endpoint này **phụ thuộc route do package tự đăng ký** (nếu package đã register route) hoặc cần tự thêm route trong `api.php`.

### 6.3 Route webhook ở `web.php`
- `POST /payment/sepay/webhook` cũng trỏ về `PaymentController::sePayWebhook()`.
- Được disable CSRF (`withoutMiddleware(ValidateCsrfToken)`).

---

## 7) Checkout/Redirect + IPN (SePay Gateway)

### 7.1 Frontend: chọn phương thức SePay
- File: `frontend/src/views/payment/Checkout.vue`
- Khi chọn `sepay`:
  - Gọi `POST /api/payments/sepay` với `booking_id` và `amount`.
  - Nhận `checkout_url` → `window.location.href`.

### 7.2 Backend: tạo checkout
- File: `backend/app/Http/Controllers/Api/PaymentController.php`
- `processSePay()`:
  - Tạo `Payment` với `payment_method = sepay`.
  - Gọi `SePayService::createCheckoutUrl()` để lấy `checkout_url`.
  - Lưu `gateway_response` chứa `endpoint` và `checkout_url`.

### 7.3 IPN Callback
- Endpoint public:
  - `POST /api/payment/sepay/callback` → `sePayCallback()`
  - `POST /api/payment/sepay/ipn` (trong code là `sepayIpn()` nhưng route **không được khai báo**)

**Logic**
- `sePayCallback()`:
  - Verify signature (chỉ kiểm tra nếu không phải test/sandbox).
  - Parse `transaction_id` nhiều định dạng.
  - Cập nhật `payment.status` theo `notification_type` hoặc `order_status`.
- `sepayIpn()`:
  - Xử lý payload dạng khác (`notification_type = ORDER_PAID`).
  - Update `payment` + `booking` tương tự.

**Lưu ý quan trọng**
- `SePayService` **không có** các hàm:
  - `createCheckoutUrl()`
  - `getEndpoint()`
  - `getPaymentFormData()`
  - `verifySignature()`
- Vì vậy, luồng Checkout/Redirect + IPN có thể **không chạy được** nếu không bổ sung các hàm này.

---

## 8) Debug & Kiểm thử

### 8.1 Debug endpoint
- `GET /api/debug-sepay`
  - Trả về trạng thái config, webhook URL, và check bảng `sepay_transactions`.

### 8.2 Test webhook giả lập
- File: `backend/public/test-sepay-webhook.php`
- Gửi request đến `/api/sepay/webhook` với token `Apikey {SEPAY_WEBHOOK_TOKEN}`.
- Dùng để kiểm tra cập nhật `booking` và log `sepay_transactions`.

---

## 9) Tổng hợp Endpoint liên quan SePay

| Method | URL | Mục đích | Controller |
|---|---|---|---|
| POST | /api/bookings | Tạo booking + trả về QR payment | BookingController::store |
| POST | /api/payments/sepay | Tạo checkout SePay (redirect) | PaymentController::processSePay |
| GET | /api/payments/{id}/status | Check trạng thái payment | PaymentController::checkStatus |
| POST | /api/payment/sepay/webhook | Webhook QR (nội dung chuyển khoản) | PaymentController::sePayWebhook |
| POST | /payment/sepay/webhook | Webhook QR (web.php) | PaymentController::sePayWebhook |
| POST | /api/payment/sepay/callback | IPN SePay | PaymentController::sePayCallback |

> Endpoint `/api/sepay/webhook` hiện **không được định nghĩa trong routes** của dự án, nhưng được dùng bởi `test-sepay-webhook.php` và `SePayWebhookController`. Nếu package không tự add route, cần thêm route thủ công.

---

## 10) Điểm cần lưu ý khi dùng làm chuẩn

1. **Pattern phải thống nhất** giữa:
   - `config('sepay.pattern')` trong webhook
   - `SePayService::generateTransferContent()`
   - Regex parse `booking_number` trong `PaymentController::sePayWebhook()`

2. **Hai luồng webhook khác nhau**:
   - `PaymentController::sePayWebhook()` dùng `booking_number` (HOMESTAY + booking_number).
   - `SePayWebhookListener` dùng `payment_code` (HT + payment_code).

3. **Chọn một luồng chuẩn duy nhất** để tránh xung đột.

4. **Kiểm tra thiếu hàm** trong `SePayService` nếu muốn dùng checkout/redirect + IPN.

5. **Đảm bảo env**:
   - `SEPAY_WEBHOOK_TOKEN`
   - `SEPAY_MATCH_PATTERN`
   - `SEPAY_BANK_NAME`
   - `SEPAY_BANK_CODE`
   - `SEPAY_BANK_ACCOUNT`
   - `SEPAY_BANK_ACCOUNT_NAME`
   - `SEPAY_PAYMENT_TIMEOUT`

---

## 11) Sơ đồ luồng QR (đúng theo code hiện tại)

1. FE tạo booking → `POST /api/bookings`
2. BE tạo `payment` và trả về `payment_info` (QR + nội dung chuyển khoản)
3. FE hiển thị `QRPayment.vue` + polling `/api/payments/{id}/status`
4. SePay gửi webhook → `POST /api/payment/sepay/webhook`
5. BE xác nhận → update `payment.status = completed` và `booking.payment_status = paid`
6. FE thấy status completed → chuyển trang xác nhận

---

## 12) Sơ đồ luồng Webhook theo package (nếu route /api/sepay/webhook tồn tại)

1. SePay gửi webhook JSON → `/api/sepay/webhook`
2. `SePayWebhookController` parse + save `sepay_transactions`
3. Dispatch `SePayWebhookEvent`
4. `SePayWebhookListener` cập nhật `payment` + `booking`

---

## 13) Kết luận

- **Luồng QR chuyển khoản** đang đầy đủ và sử dụng `SePayService` + `PaymentController::sePayWebhook()`.
- **Luồng Checkout/Redirect + IPN** tồn tại trong code nhưng **chưa hoàn chỉnh** vì thiếu hàm trong `SePayService`.
- Nếu muốn dùng làm chuẩn cho dự án khác, nên **chọn 1 luồng duy nhất** và chuẩn hoá pattern + endpoint.

---

## 14) Tham chiếu code

Các file chính:
- `backend/config/sepay.php`
- `backend/app/Services/SePayService.php`
- `backend/app/Http/Controllers/Api/BookingController.php`
- `backend/app/Http/Controllers/Api/PaymentController.php`
- `backend/app/Http/Controllers/Api/SePayWebhookController.php`
- `backend/app/Listeners/SePayWebhookListener.php`
- `backend/bootstrap/app.php`
- `backend/routes/api.php`
- `backend/routes/web.php`
- `frontend/src/views/payment/Checkout.vue`
- `frontend/src/views/payment/QRPayment.vue`
