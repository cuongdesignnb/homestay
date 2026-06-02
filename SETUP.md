# 🏠 Hướng dẫn cài đặt và sử dụng Homestay Booking System

## Yêu cầu hệ thống

- Docker Desktop
- Git
- Tối thiểu 4GB RAM
- Cổng 8765, 5174, 33065, 8091, 63905 phải available

## Cài đặt nhanh

### 1. Clone dự án

```bash
git clone <repository-url>
cd Homestay
```

### 2. Cấu hình môi trường

**Backend (.env):**

```bash
cp .env.example backend/.env
```

**Frontend (.env):**

```bash
cp frontend/.env.example frontend/.env
```

### 3. Khởi động Docker

```bash
docker-compose up -d --build
```

> Backend container sử dụng `php artisan serve --host=0.0.0.0 --port=8765` bên trong Docker, nên bạn có tốc độ
> reload nhanh mà vẫn giữ được môi trường container.

### 4. Cài đặt dependencies

**Backend:**

```bash
docker exec -it homestay_backend composer install
docker exec -it homestay_backend php artisan key:generate
docker exec -it homestay_backend php artisan migrate --seed
```

**Frontend:**

```bash
docker exec -it homestay_frontend npm install
```

### 5. Truy cập ứng dụng

- 🌐 **Frontend**: http://localhost:5174
- 🔧 **Backend API**: http://localhost:8765
- 💾 **phpMyAdmin**: http://localhost:8091
  - Server: `mysql`
  - Username: `root`
  - Password: `root_secure_pass_2024`

## Tài khoản mặc định

**Admin:**

- Email: admin@homestay.com
- Mật khẩu: admin123456

**User:**

- Email: user@homestay.com
- Mật khẩu: user123456

## Lệnh thường dùng

### Quản lý Docker

```bash
# Dừng tất cả services
docker-compose down

# Xem logs
docker-compose logs -f

# Khởi động lại một service
docker-compose restart backend
```

### Backend (Laravel)

```bash
# Vào container backend
docker exec -it homestay_backend bash

# Chạy migration
php artisan migrate

# Seed database
php artisan db:seed

# Xóa cache
php artisan cache:clear
php artisan config:clear
```

### Frontend (Vue3)

```bash
# Vào container frontend
docker exec -it homestay_frontend sh

# Chạy dev server
npm run dev

# Build production
npm run build
```

## Cấu trúc dự án

```
Homestay/
├── backend/              # Laravel Backend
│   ├── app/
│   ├── database/
│   └── routes/
├── frontend/             # Vue3 Frontend
│   └── src/
│       ├── components/
│       ├── views/
│       └── stores/
└── docker-compose.yml    # Docker config
```

## API Endpoints chính

### Authentication

- `POST /api/register` - Đăng ký
- `POST /api/login` - Đăng nhập
- `POST /api/logout` - Đăng xuất

### Rooms

- `GET /api/rooms` - Danh sách phòng
- `GET /api/rooms/{id}` - Chi tiết phòng
- `POST /api/bookings` - Đặt phòng

### Tours

- `GET /api/tours` - Danh sách tour
- `POST /api/tour-bookings` - Đặt tour

### Blog

- `GET /api/blog/posts` - Danh sách bài viết
- `GET /api/blog/posts/{slug}` - Chi tiết bài viết

## Tính năng chính

✅ Đặt phòng homestay  
✅ Đặt tour du lịch  
✅ Thanh toán online (SePay, PayPal)  
✅ Hai ngôn ngữ (Việt/Anh)  
✅ Blog với SEO đầy đủ  
✅ Quản trị admin  
✅ Đánh giá và review  
✅ Responsive design

## Cấu hình Payment Gateway

### SePay

1. Đăng ký tài khoản tại https://sepay.vn
2. Lấy API credentials
3. Cập nhật trong `backend/.env`:

```env
SEPAY_API_KEY=your_api_key
SEPAY_SECRET_KEY=your_secret_key
```

### PayPal

1. Đăng ký tại https://developer.paypal.com
2. Tạo application
3. Cập nhật trong `backend/.env`:

```env
PAYPAL_CLIENT_ID=your_client_id
PAYPAL_CLIENT_SECRET=your_client_secret
```

## Xử lý sự cố

### Port đã được sử dụng

Sửa port trong `docker-compose.yml`:

```yaml
ports:
  - "PORT_MỚI:CONTAINER_PORT"
```

### Lỗi permission

```bash
docker exec -it homestay_backend chown -R www-data:www-data /var/www
docker exec -it homestay_backend chmod -R 755 /var/www/storage
```

### Kết nối database lỗi

Kiểm tra MySQL đã start chưa:

```bash
docker-compose logs mysql
```

## Hỗ trợ

- Email: support@homestay.com
- GitHub Issues: [Link repository]

## License

MIT License
