# 🏠 Homestay Booking System

A full-stack web application for homestay booking and tour management with bilingual support (Vietnamese & English).

## 🚀 Tech Stack

### Backend

- **Laravel 11** - PHP Framework
- **MySQL 8.0** - Database
- **Redis** - Caching & Queue
- **Laravel Sanctum** - API Authentication

### Frontend

- **Vue 3** - Progressive JavaScript Framework
- **Vite** - Build Tool
- **Vue Router** - Routing
- **Pinia** - State Management
- **Vue I18n** - Internationalization

### Payment Gateways

- **SePay** - Vietnamese Payment Gateway
- **PayPal** - International Payment

## 📋 Features

- ✅ Room listing and availability checking
- ✅ Room booking system with calendar
- ✅ Tour management and booking
- ✅ Online payment via SePay & PayPal
- ✅ Bilingual support (Vietnamese/English)
- ✅ Blog system with SEO optimization
- ✅ Admin dashboard
- ✅ User authentication & authorization
- ✅ Email notifications
- ✅ Responsive design

## 🐳 Docker Setup

### Unique Ports Configuration

- **Backend (Laravel)**: `http://localhost:8765`
- **Frontend (Vue3)**: `http://localhost:5174`
- **MySQL**: `localhost:33065`
- **phpMyAdmin**: `http://localhost:8091`
- **Redis**: `localhost:63905`

> ℹ️ Backend container now boots with `php artisan serve --host=0.0.0.0 --port=8765` inside Docker for faster
> reloads while keeping the same external port mapping.

### Prerequisites

- Docker Desktop installed
- Git installed
- Minimum 4GB RAM available

### Installation Steps

1. **Clone the repository**

```bash
git clone <repository-url>
cd Homestay
```

2. **Setup environment files**

```bash
# Backend environment
cp .env.example backend/.env

# Frontend environment
cp frontend/.env.example frontend/.env
```

3. **Update backend/.env with database credentials**

```env
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=homestay_db
DB_USERNAME=homestay_user
DB_PASSWORD=homestay_secure_pass_2024
```

4. **Build and start Docker containers**

```bash
docker-compose up -d --build
```

5. **Install Laravel dependencies**

```bash
docker exec -it homestay_backend composer install
```

6. **Generate application key**

```bash
docker exec -it homestay_backend php artisan key:generate
```

7. **Run database migrations**

```bash
docker exec -it homestay_backend php artisan migrate --seed
```

8. **Install frontend dependencies**

```bash
docker exec -it homestay_frontend npm install
```

9. **Access the applications**

- Frontend: http://localhost:5174
- Backend API: http://localhost:8765
- phpMyAdmin: http://localhost:8091
  - Server: `mysql`
  - Username: `root`
  - Password: `root_secure_pass_2024`

## 🔧 Development Commands

### Backend (Laravel)

```bash
# Enter backend container
docker exec -it homestay_backend bash

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Create new migration
php artisan make:migration create_table_name

# Create new model
php artisan make:model ModelName -mcr

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Frontend (Vue3)

```bash
# Enter frontend container
docker exec -it homestay_frontend sh

# Run development server
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview
```

### Docker Management

```bash
# Stop all containers
docker-compose down

# Stop and remove volumes
docker-compose down -v

# View logs
docker-compose logs -f

# Restart specific service
docker-compose restart backend
docker-compose restart frontend
```

## 📁 Project Structure

```
Homestay/
├── backend/                    # Laravel Backend
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/    # API Controllers
│   │   │   └── Middleware/     # Custom Middleware
│   │   ├── Models/             # Eloquent Models
│   │   └── Services/           # Business Logic
│   ├── database/
│   │   ├── migrations/         # Database Migrations
│   │   └── seeders/            # Database Seeders
│   ├── routes/
│   │   ├── api.php             # API Routes
│   │   └── web.php             # Web Routes
│   └── Dockerfile
├── frontend/                   # Vue3 Frontend
│   ├── src/
│   │   ├── components/         # Vue Components
│   │   ├── views/              # Page Views
│   │   ├── router/             # Vue Router
│   │   ├── stores/             # Pinia Stores
│   │   ├── locales/            # i18n Translations
│   │   └── services/           # API Services
│   └── Dockerfile
├── docker-compose.yml          # Docker Configuration
└── README.md
```

## 🌐 API Endpoints

### Authentication

- `POST /api/register` - Register new user
- `POST /api/login` - User login
- `POST /api/logout` - User logout

### Rooms

- `GET /api/rooms` - List all rooms
- `GET /api/rooms/{id}` - Get room details
- `POST /api/rooms` - Create room (Admin)
- `PUT /api/rooms/{id}` - Update room (Admin)
- `DELETE /api/rooms/{id}` - Delete room (Admin)

### Bookings

- `GET /api/bookings` - List user bookings
- `POST /api/bookings` - Create booking
- `GET /api/bookings/{id}` - Get booking details
- `PUT /api/bookings/{id}` - Update booking status

### Tours

- `GET /api/tours` - List all tours
- `GET /api/tours/{id}` - Get tour details
- `POST /api/tours` - Create tour (Admin)

### Payments

- `POST /api/payments/sepay` - Process SePay payment
- `POST /api/payments/paypal` - Process PayPal payment

### Blog

- `GET /api/blog/posts` - List blog posts
- `GET /api/blog/posts/{slug}` - Get post by slug
- `POST /api/blog/posts` - Create post (Admin)

## 🔐 Default Admin Account

After seeding, you can login with:

- **Email**: admin@homestay.com
- **Password**: admin123456

## 🌍 Internationalization

The application supports Vietnamese (vi) and English (en) languages. Language switching is available in the navigation bar.

## 💳 Payment Gateway Setup

### SePay Configuration

1. Register at https://sepay.vn
2. Get API credentials
3. Update `.env` with `SEPAY_API_KEY` and `SEPAY_SECRET_KEY`

### PayPal Configuration

1. Register at https://developer.paypal.com
2. Create an application
3. Update `.env` with `PAYPAL_CLIENT_ID` and `PAYPAL_CLIENT_SECRET`

## 📧 Email Configuration

Update the following in `backend/.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
```

## 🐛 Troubleshooting

### Port Already in Use

If ports are already in use, modify the ports in `docker-compose.yml`:

```yaml
ports:
  - "NEW_PORT:CONTAINER_PORT"
```

### Permission Issues

```bash
docker exec -it homestay_backend chown -R www-data:www-data /var/www
docker exec -it homestay_backend chmod -R 755 /var/www/storage
```

### Database Connection Issues

Ensure MySQL is fully started before running migrations:

```bash
docker-compose logs mysql
```

## 📝 License

This project is open-sourced software licensed under the MIT license.

## 👥 Support

For support, email support@homestay.com or create an issue in the repository.
