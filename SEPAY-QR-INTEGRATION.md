# SePay QR Code Payment Integration

## Tổng quan

Homestay Booking System sử dụng SePay với phương thức **Webhook + QR Code** để xử lý thanh toán.

### Cách hoạt động:

1. Khách đặt phòng và chọn thanh toán online
2. Hệ thống tạo mã QR với VietQR
3. Khách quét mã QR bằng app ngân hàng để chuyển khoản
4. SePay nhận thông báo từ ngân hàng và gửi webhook đến server
5. Server xác nhận thanh toán và cập nhật trạng thái booking

## Cấu hình

### 1. Đăng ký SePay

1. Truy cập: https://my.sepay.vn/register
2. Xác thực tài khoản và liên kết ngân hàng
3. Vào **CỔNG THANH TOÁN** → **Webhook** để lấy token

### 2. Cấu hình Webhook trên SePay

Trong dashboard SePay:

- URL Webhook: `https://your-domain.com/api/payment/sepay/webhook`
- Hoặc: `https://your-domain.com/payment/sepay/webhook` (web route)
- Method: POST
- Lấy **Webhook Token** để xác thực

### 3. Cấu hình .env

```env
# SePay Webhook Token (từ dashboard SePay)
SEPAY_WEBHOOK_TOKEN=your_webhook_token

# Thông tin tài khoản ngân hàng nhận tiền
SEPAY_BANK_NAME="MB Bank"
SEPAY_BANK_CODE=MB
SEPAY_BANK_ACCOUNT=0123456789
SEPAY_BANK_ACCOUNT_NAME="NGUYEN VAN A"

# Pattern để match booking (nội dung chuyển khoản sẽ là HOMESTAY + mã booking)
SEPAY_PATTERN=HOMESTAY

# VietQR settings (không cần thay đổi)
SEPAY_VIETQR_URL=https://img.vietqr.io/image
SEPAY_QR_TEMPLATE=compact2
```

### 4. Danh sách mã ngân hàng (SEPAY_BANK_CODE)

| Bank        | Code |
| ----------- | ---- |
| MB Bank     | MB   |
| Vietcombank | VCB  |
| Techcombank | TCB  |
| BIDV        | BIDV |
| VPBank      | VPB  |
| ACB         | ACB  |
| Agribank    | AGRI |
| TPBank      | TPB  |
| VIB         | VIB  |

## Luồng thanh toán

### Frontend (QRPayment.vue)

```
1. Nhận thông tin payment từ BookingForm
   - qr_code_url: URL ảnh QR Code
   - bank_name, account_number, account_name
   - amount, transfer_content
   - expires_at

2. Hiển thị:
   - QR Code để khách quét
   - Thông tin chuyển khoản (có nút copy)
   - Countdown timer
   - Nút "Kiểm tra thanh toán"

3. Auto-check status mỗi 10 giây
4. Khi payment.status = 'completed' → redirect sang BookingConfirmation
```

### Backend Flow

```
1. BookingController::store()
   - Tạo booking
   - Gọi SePayService::createPayment()
   - Trả về payment info với QR code URL

2. Khách chuyển khoản với nội dung: HOMESTAY{booking_number}
   VD: HOMESTAYBK240001

3. SePay gửi webhook đến /api/payment/sepay/webhook
   - PaymentController::sePayWebhook() xử lý
   - Verify token
   - Parse transfer content để lấy booking_number
   - Tìm booking và payment
   - Update status = 'completed'
   - Mark booking as paid/confirmed

4. Frontend check status → thấy completed → redirect
```

## API Endpoints

### Public

| Method | URL                          | Mô tả                          |
| ------ | ---------------------------- | ------------------------------ |
| GET    | `/api/payments/{id}/status`  | Kiểm tra trạng thái thanh toán |
| POST   | `/api/payment/sepay/webhook` | Webhook từ SePay               |

### Authenticated

| Method | URL             | Mô tả                                |
| ------ | --------------- | ------------------------------------ |
| POST   | `/api/bookings` | Tạo booking (trả về payment QR info) |

## Cấu trúc dữ liệu

### Payment Response (từ BookingController)

```json
{
  "booking": {
    "id": 1,
    "booking_number": "BK240001",
    ...
  },
  "payment": {
    "id": 1,
    "booking_number": "BK240001",
    "qr_code_url": "https://img.vietqr.io/image/MB-0123456789-compact2.png?amount=1500000&addInfo=HOMESTAYBK240001",
    "bank_name": "MB Bank",
    "bank_code": "MB",
    "account_number": "0123456789",
    "account_name": "NGUYEN VAN A",
    "amount": 1500000,
    "amount_formatted": "1,500,000 VND",
    "transfer_content": "HOMESTAYBK240001",
    "expires_at": "2024-01-15T15:30:00.000000Z"
  }
}
```

### SePay Webhook Payload

```json
{
  "id": "12345",
  "transferAmount": 1500000,
  "content": "HOMESTAYBK240001",
  "referenceNumber": "FT24xxxxx",
  "gateway": "MB",
  "transactionDate": "2024-01-15 14:30:00"
}
```

## Troubleshooting

### 1. Không nhận được webhook

- Kiểm tra URL webhook đã đúng chưa
- Kiểm tra CSRF middleware đã disable cho route này chưa
- Kiểm tra firewall/security không block request từ SePay
- Xem logs: `storage/logs/laravel.log`

### 2. Webhook nhận được nhưng không match booking

- Kiểm tra nội dung chuyển khoản có đúng pattern không
- Kiểm tra booking_number có tồn tại không
- Xem log để debug: `SePay Webhook received`

### 3. QR Code không hiển thị

- Kiểm tra cấu hình SEPAY_BANK_CODE có đúng không
- Kiểm tra URL: `https://img.vietqr.io/image/{bank_code}-{account}-compact2.png`

### 4. Token verification failed

- Kiểm tra SEPAY_WEBHOOK_TOKEN trong .env
- Token phải giống với token trong dashboard SePay
- Kiểm tra header Authorization hoặc X-SePay-Token

## Files liên quan

```
backend/
├── config/sepay.php                      # Cấu hình SePay
├── app/Services/SePayService.php         # Service xử lý QR Code
├── app/Http/Controllers/Api/
│   ├── PaymentController.php             # Webhook handler
│   └── BookingController.php             # Tạo booking + payment
├── routes/
│   ├── api.php                           # API routes
│   └── web.php                           # Web routes (webhook)
└── .env                                  # Cấu hình

frontend/
├── src/views/
│   ├── BookingForm.vue                   # Form đặt phòng
│   └── payment/QRPayment.vue             # Trang QR thanh toán
├── src/router/index.js                   # Routes
└── src/locales/
    ├── vi.json                           # Tiếng Việt
    └── en.json                           # English
```

## Testing

### Test với SePay Sandbox

1. Đăng nhập SePay dashboard
2. Vào **CỔNG THANH TOÁN** → **Webhook** → **Test Webhook**
3. Gửi test request với payload giả lập

### Test thủ công

1. Tạo booking với payment_method = 'sepay'
2. Lấy payment ID từ response
3. Gọi API check status:
   ```bash
   curl http://localhost:8765/api/payments/{id}/status
   ```
4. Mô phỏng webhook:
   ```bash
   curl -X POST http://localhost:8765/api/payment/sepay/webhook \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer your_webhook_token" \
     -d '{"transferAmount": 1500000, "content": "HOMESTAYBK240001"}'
   ```
