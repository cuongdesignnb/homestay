#!/bin/bash
# Frontend build script for production

echo "🎨 Building Vue.js frontend for production..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Get script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
cd "$SCRIPT_DIR"

echo -e "${YELLOW}📁 Working directory: $SCRIPT_DIR${NC}"

# Check if node/npm is available
if ! command -v npm &> /dev/null; then
    echo -e "${RED}❌ npm not found. Please install Node.js first.${NC}"
    exit 1
fi

# Check if .env.production exists
if [ ! -f .env.production ]; then
    echo -e "${RED}❌ .env.production file not found${NC}"
    exit 1
fi

# Install dependencies
echo -e "${YELLOW}📦 Installing dependencies...${NC}"
npm install
if [ $? -ne 0 ]; then
    echo -e "${RED}❌ npm install failed${NC}"
    exit 1
fi

# Clean previous build
echo -e "${YELLOW}🧹 Cleaning previous build...${NC}"
rm -rf dist/

# Build for production
echo -e "${YELLOW}🔨 Building for production...${NC}"
npm run build
if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Build failed${NC}"
    exit 1
fi

# Verify build output
if [ ! -d "dist" ]; then
    echo -e "${RED}❌ Build output directory not found${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Frontend build completed successfully!${NC}"
echo -e "${GREEN}📂 Build files are in: $SCRIPT_DIR/dist/${NC}"
echo -e "${YELLOW}📝 Next: Copy dist/* to backend/public/ directory${NC}"

# Optional: Show build size
echo -e "${YELLOW}📊 Build size:${NC}"
du -sh dist/ 2>/dev/null || echo "Unable to calculate size"

# List main files
echo -e "${YELLOW}📋 Main files:${NC}"
find dist/ -type f -name "*.html" -o -name "*.js" -o -name "*.css" | head -10