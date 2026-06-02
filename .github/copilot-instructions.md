# Homestay Booking System - AI Coding Agent Instructions

## Project Overview

Full-stack homestay booking platform with Laravel 11 backend and Vue 3 frontend, supporting bilingual content (Vietnamese/English), room/tour bookings, and online payments via SePay and PayPal.

## Architecture

### Backend (Laravel 11 - Port 8765)

- **Framework**: Laravel 11 with PHP 8.2
- **Authentication**: Laravel Sanctum for API token auth
- **Database**: MySQL 8.0 (port 33065)
- **Cache/Queue**: Redis (port 63905)
- **API Structure**: RESTful API at `/api/*`

**Key Models & Relationships**:

- `User` (admin/user roles) → has many `Booking`, `TourBooking`, `Review`
- `Room` → has many `Booking`, `Review`
- `Tour` → has many `TourBooking`, `Review`
- `Booking` → belongs to `User`, `Room`; has one `Payment`
- `TourBooking` → belongs to `User`, `Tour`; has one `Payment`
- `BlogPost` → belongs to `BlogCategory`, `User` (author)
- `Payment` → polymorphic relation to bookings

### Frontend (Vue 3 - Port 5174)

- **Framework**: Vue 3 with Composition API
- **Build Tool**: Vite
- **State**: Pinia stores
- **Routing**: Vue Router with auth guards
- **i18n**: Vue I18n (vi/en locales)
- **Styling**: Custom CSS with utility classes

### Docker Services

- **backend**: Laravel on port 8765
- **frontend**: Vue dev server on port 5174
- **mysql**: Database on port 33065
- **phpmyadmin**: Web UI on port 8091 (user: root, pass: root_secure_pass_2024)
- **redis**: Cache on port 63905
- Backend container runs `php artisan serve --host=0.0.0.0 --port=8765` for faster refresh cycles.

## Development Workflow

### Starting the Project

```bash
# Start all services
docker-compose up -d

# Backend setup (first time)
docker exec -it homestay_backend composer install
docker exec -it homestay_backend php artisan key:generate
docker exec -it homestay_backend php artisan migrate --seed

# Frontend setup (first time)
docker exec -it homestay_frontend npm install
```

### Database Operations

```bash
# Run migrations
docker exec -it homestay_backend php artisan migrate

# Seed database (creates admin@homestay.com / admin123456)
docker exec -it homestay_backend php artisan db:seed

# Create new migration
docker exec -it homestay_backend php artisan make:migration create_table_name

# Rollback last migration
docker exec -it homestay_backend php artisan migrate:rollback
```

### Backend Commands

```bash
# Enter backend container
docker exec -it homestay_backend bash

# Create controller with model
php artisan make:controller Api/ControllerName -r --model=ModelName

# Clear all caches
php artisan optimize:clear

# View routes
php artisan route:list
```

### Frontend Commands

```bash
# Enter frontend container
docker exec -it homestay_frontend sh

# Install new package
npm install package-name

# Build for production
npm run build
```

## Code Conventions

### Laravel Backend

1. **API Controllers**: Place in `app/Http/Controllers/Api/`

   - Admin controllers in `Api/Admin/` namespace
   - Use resource controllers for CRUD operations
   - Always validate input with `Validator::make()` or Form Requests

2. **Models**:

   - Use `$fillable` for mass assignment
   - Cast attributes appropriately (`'amenities' => 'array'`, `'price' => 'decimal:2'`)
   - Define relationships explicitly
   - Use `SoftDeletes` for rooms, tours, blog posts

3. **Routes** (`routes/api.php`):

   - Public routes first
   - `auth:sanctum` middleware for protected routes
   - `['auth:sanctum', 'admin']` for admin-only routes
   - Use route groups for organization

4. **Middleware**:

   - `AdminMiddleware`: Checks `user->role === 'admin'`
   - Applied in route definitions, not constructors

5. **Database**:
   - Migration timestamps format: `YYYY_MM_DD_HHMMSS_table_name`
   - Use foreign keys with cascading deletes
   - Index frequently queried columns (status, dates)

### Vue 3 Frontend

1. **Composition API**: Always use `<script setup>` syntax

   ```vue
   <script setup>
   import { ref, computed, onMounted } from "vue";
   // Component logic here
   </script>
   ```

2. **API Calls**: Use centralized `api` service from `@/services/api.js`

   ```js
   import api from "@/services/api";
   const response = await api.get("/rooms");
   ```

3. **State Management**: Use Pinia stores

   - Auth state in `stores/auth.js`
   - Access with `const authStore = useAuthStore()`

4. **Internationalization**: Use `$t()` in templates, `t()` in script

   ```vue
   <template>{{ $t("nav.home") }}</template>
   <script setup>
   import { useI18n } from "vue-i18n";
   const { t } = useI18n();
   </script>
   ```

5. **Routing**:

   - Protected routes use `meta: { requiresAuth: true }`
   - Admin routes use `meta: { requiresAdmin: true }`
   - Guards in `router/index.js` check auth store

6. **Component Structure**:
   - Layout components in `components/layout/`
   - Page views in `views/`
   - Reusable components in `components/`

## Key Features Implementation

### Authentication Flow

1. User registers/logins → receives Sanctum token
2. Token stored in localStorage
3. `api.js` interceptor adds token to all requests
4. 401 responses trigger logout and redirect to login

### Booking Process

1. User selects room/tour with dates
2. Availability checked via `isAvailable()` method
3. Booking created with `pending` status
4. Payment processed → status changes to `confirmed`
5. Email notification sent (configure MAIL\_ in .env)

### Payment Integration

- **SePay**: Vietnamese payment gateway (implement in `PaymentController`)
- **PayPal**: International payments (implement in `PaymentController`)
- Both return to `/payment/callback` route
- Payment status updates booking status automatically

### SEO Implementation

- Blog posts have `meta_title`, `meta_description`, `meta_keywords`
- Frontend should render meta tags dynamically
- Slugs generated using `Str::slug()`
- Published posts filtered with `published()` scope

### Bilingual Support

- Backend: Separate translation files in `resources/lang/`
- Frontend: JSON files in `src/locales/`
- Locale stored in localStorage
- API can accept `Accept-Language` header

## Common Tasks

### Adding a New API Endpoint

1. Define route in `routes/api.php`
2. Create/update controller method
3. Add validation rules
4. Return JSON response
5. Update frontend API service/component

### Adding a New Model

```bash
php artisan make:model ModelName -mcr
# Creates: Model, Migration, Controller (resource), Factory
```

### Adding a New Vue Component

1. Create component file in appropriate directory
2. Use `<script setup>` syntax
3. Import and register in parent component
4. Add i18n keys to locale files

### Database Schema Changes

1. Create migration: `php artisan make:migration description`
2. Define up/down methods
3. Run: `php artisan migrate`
4. Update model `$fillable` and `$casts`

## Debugging

### Backend Issues

- Check logs: `docker-compose logs -f backend`
- Laravel logs: `backend/storage/logs/laravel.log`
- Query debugging: Use `DB::enableQueryLog()` and `DB::getQueryLog()`

### Frontend Issues

- Browser console for JS errors
- Network tab for API call failures
- Vue DevTools for component inspection

### Database Issues

- Access phpMyAdmin at http://localhost:8091
- Direct MySQL: `docker exec -it homestay_mysql mysql -u root -p`

## Security Notes

- Never commit `.env` files
- Sanctum CSRF protection enabled for SPA
- Admin middleware protects sensitive routes
- Input validation on all POST/PUT requests
- SQL injection protected by Eloquent ORM

## Testing Access

- **Admin**: admin@homestay.com / admin123456
- **User**: user@homestay.com / user123456
- **phpMyAdmin**: root / root_secure_pass_2024
- **Frontend**: http://localhost:5174
- **Backend API**: http://localhost:8765/api

## File Structure Quick Reference

```
backend/
├── app/
│   ├── Http/Controllers/Api/  # API controllers
│   ├── Models/                # Eloquent models
│   └── Http/Middleware/       # Custom middleware
├── database/
│   ├── migrations/            # Database migrations
│   └── seeders/               # Database seeders
└── routes/api.php             # API routes

frontend/
├── src/
│   ├── components/            # Vue components
│   ├── views/                 # Page components
│   ├── stores/                # Pinia stores
│   ├── router/                # Vue Router config
│   ├── services/              # API services
│   └── locales/               # i18n translations
```

## Important Notes for AI Agents

- Port 8765 is for backend API, not 8000
- Always use Docker commands, not direct artisan/npm
- Database credentials are in `docker-compose.yml`, not `.env`
- Frontend connects to backend via `VITE_API_URL`
- Sanctum requires CORS configuration for SPA authentication
- Room/tour availability logic is in model methods, not controllers
- Payment integration placeholders need actual API implementation
