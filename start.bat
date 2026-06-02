@echo off
echo 🏠 Homestay Booking System - Quick Start Script
echo ================================================

REM Check if Docker is running
docker info >nul 2>&1
if %errorlevel% neq 0 (
  echo ❌ Docker is not running. Please start Docker Desktop first.
  exit /b 1
)

echo ✅ Docker is running

REM Create environment files if they don't exist
if not exist "backend\.env" (
  echo 📝 Creating backend .env file...
  copy .env.example backend\.env
)

if not exist "frontend\.env" (
  echo 📝 Creating frontend .env file...
  copy frontend\.env.example frontend\.env
)

REM Start Docker containers
echo 🐳 Starting Docker containers...
docker-compose up -d --build

REM Wait for MySQL to be ready
echo ⏳ Waiting for MySQL to be ready...
timeout /t 10 /nobreak >nul

REM Install backend dependencies
echo 📦 Installing backend dependencies...
docker exec -it homestay_backend composer install

REM Generate Laravel key
echo 🔑 Generating Laravel application key...
docker exec -it homestay_backend php artisan key:generate

REM Run migrations and seeders
echo 🗄️ Running database migrations and seeders...
docker exec -it homestay_backend php artisan migrate --seed

REM Install frontend dependencies
echo 📦 Installing frontend dependencies...
docker exec -it homestay_frontend npm install

echo.
echo ✅ Setup complete!
echo.
echo 📍 Access your application:
echo    Frontend:    http://localhost:5174
echo    Backend API: http://localhost:8765
echo    phpMyAdmin:  http://localhost:8091
echo.
echo 🔐 Default accounts:
echo    Admin: admin@homestay.com / admin123456
echo    User:  user@homestay.com / user123456
echo.
echo 📚 For more information, see README.md and SETUP.md

pause
