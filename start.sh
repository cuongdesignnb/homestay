#!/bin/bash

echo "🏠 Homestay Booking System - Quick Start Script"
echo "================================================"

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
  echo "❌ Docker is not running. Please start Docker Desktop first."
  exit 1
fi

echo "✅ Docker is running"

# Create environment files if they don't exist
if [ ! -f "backend/.env" ]; then
  echo "📝 Creating backend .env file..."
  cp .env.example backend/.env
fi

if [ ! -f "frontend/.env" ]; then
  echo "📝 Creating frontend .env file..."
  cp frontend/.env.example frontend/.env
fi

# Start Docker containers
echo "🐳 Starting Docker containers..."
docker-compose up -d --build

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
sleep 10

# Install backend dependencies
echo "📦 Installing backend dependencies..."
docker exec -it homestay_backend composer install

# Generate Laravel key
echo "🔑 Generating Laravel application key..."
docker exec -it homestay_backend php artisan key:generate

# Run migrations and seeders
echo "🗄️ Running database migrations and seeders..."
docker exec -it homestay_backend php artisan migrate --seed

# Install frontend dependencies
echo "📦 Installing frontend dependencies..."
docker exec -it homestay_frontend npm install

echo ""
echo "✅ Setup complete!"
echo ""
echo "📍 Access your application:"
echo "   Frontend:    http://localhost:5174"
echo "   Backend API: http://localhost:8765"
echo "   phpMyAdmin:  http://localhost:8091"
echo ""
echo "🔐 Default accounts:"
echo "   Admin: admin@homestay.com / admin123456"
echo "   User:  user@homestay.com / user123456"
echo ""
echo "📚 For more information, see README.md and SETUP.md"
