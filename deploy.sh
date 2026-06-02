#!/bin/bash
# Production deployment script for aaPanel
# Run this script after uploading code to server

echo "🚀 Starting Homestay production deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Get the directory where script is located
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
PROJECT_ROOT="$SCRIPT_DIR"

echo -e "${YELLOW}📁 Project root: $PROJECT_ROOT${NC}"

# Step 1: Backend setup
echo -e "${YELLOW}🔧 Setting up Laravel backend...${NC}"
cd "$PROJECT_ROOT/backend"

# Install composer dependencies
if ! command -v composer &> /dev/null; then
    echo -e "${RED}❌ Composer not found. Please install composer first.${NC}"
    exit 1
fi

composer install --optimize-autoloader --no-dev --no-interaction
if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Composer install failed${NC}"
    exit 1
fi

# Check if .env exists, if not copy from .env.production
if [ ! -f .env ]; then
    if [ -f .env.production ]; then
        cp .env.production .env
        echo -e "${YELLOW}📄 Created .env from .env.production${NC}"
        echo -e "${YELLOW}⚠️  Please update database credentials in .env file${NC}"
    else
        echo -e "${RED}❌ No .env file found. Please create .env file with production settings.${NC}"
        exit 1
    fi
fi

# Generate app key if needed
php artisan key:generate --force

# Run database migrations
echo -e "${YELLOW}📊 Running database migrations...${NC}"
php artisan migrate --force
if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Database migration failed${NC}"
    exit 1
fi

# Seed database if tables are empty
echo -e "${YELLOW}🌱 Seeding database...${NC}"
php artisan db:seed --force

# Clear and cache config
echo -e "${YELLOW}🗑️  Clearing caches...${NC}"
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo -e "${YELLOW}⚡ Optimizing for production...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
echo -e "${YELLOW}🔐 Setting file permissions...${NC}"
chmod -R 755 storage bootstrap/cache
chown -R www:www storage bootstrap/cache 2>/dev/null || chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# Step 2: Frontend build
echo -e "${YELLOW}🎨 Building Vue.js frontend...${NC}"
cd "$PROJECT_ROOT/frontend"

# Check if node is available
if ! command -v npm &> /dev/null; then
    echo -e "${RED}❌ npm not found. Please install Node.js first.${NC}"
    exit 1
fi

# Install dependencies
npm install
if [ $? -ne 0 ]; then
    echo -e "${RED}❌ npm install failed${NC}"
    exit 1
fi

# Build for production
npm run build
if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Frontend build failed${NC}"
    exit 1
fi

# Step 3: Deploy frontend to Laravel public directory
echo -e "${YELLOW}📂 Deploying frontend...${NC}"
cd "$PROJECT_ROOT"

# Backup old frontend files
if [ -d "backend/public/assets" ]; then
    rm -rf backend/public/assets
fi
if [ -f "backend/public/index.html" ]; then
    rm -f backend/public/index.html
fi

# Copy new build files
cp -r frontend/dist/* backend/public/
if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Failed to copy frontend files${NC}"
    exit 1
fi

# Step 4: Final checks
echo -e "${YELLOW}✅ Running final checks...${NC}"
cd "$PROJECT_ROOT/backend"

# Check if Laravel is working
php artisan --version > /dev/null
if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Laravel is not working properly${NC}"
    exit 1
fi

# Create storage link if needed
php artisan storage:link

echo -e "${GREEN}🎉 Deployment completed successfully!${NC}"
echo -e "${GREEN}📝 Next steps:${NC}"
echo -e "   1. Update .env with your actual database credentials"
echo -e "   2. Update .env with your domain URL"
echo -e "   3. Configure your web server to point to backend/public/"
echo -e "   4. Enable SSL certificate"
echo -e "   5. Test the website"
echo -e ""
echo -e "${YELLOW}🔗 Your website should be accessible at: $(grep APP_URL backend/.env | cut -d'=' -f2)${NC}"