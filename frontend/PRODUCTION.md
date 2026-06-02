# Frontend Production Deployment Guide

## 🎯 **Trả lời câu hỏi:**

### **Không cần xóa file nào**, chỉ cần:

1. **Tạo `.env.production`** (đã tạo sẵn)
2. **Chạy build command**
3. **Copy files đã build**

## 🚀 **Các lệnh chạy:**

### **Cách 1: Manual (Từng bước)**

```bash
# Trong thư mục frontend/
npm install
npm run build
```

### **Cách 2: Auto Script (Khuyến nghị)**

```bash
# Trong thư mục frontend/
chmod +x build.sh
./build.sh
```

## 📂 **Sau khi build:**

### **Files được tạo trong `frontend/dist/`:**

```
dist/
├── index.html          # Main HTML file
├── assets/
│   ├── index-xxx.js    # Main JavaScript bundle
│   ├── index-xxx.css   # Main CSS bundle
│   └── ...             # Other assets
└── favicon.ico
```

### **Copy vào backend:**

```bash
# Xóa files cũ
rm -rf ../backend/public/assets/
rm -f ../backend/public/index.html

# Copy files mới
cp -r dist/* ../backend/public/
```

## ⚙️ **Environment Variables:**

### **Development (.env.example):**

```dotenv
VITE_API_URL=http://localhost:8765/api
VITE_APP_NAME=Homestay Booking
```

### **Production (.env.production):**

```dotenv
VITE_API_URL=http://catbacountrysidehomestay.com/api
VITE_APP_NAME=Cat Ba Countryside Homestay
```

## 🔧 **Complete Deployment Flow:**

### **1. Build Frontend:**

```bash
cd frontend/
./build.sh
```

### **2. Deploy to Backend:**

```bash
cd ..
rm -rf backend/public/assets backend/public/index.html
cp -r frontend/dist/* backend/public/
```

### **3. Verify Files:**

```bash
ls -la backend/public/
# Should see: index.html, assets/, favicon.ico
```

## 🎯 **Files trên Production Server:**

### **Keep (Không xóa):**

```
backend/public/
├── .htaccess           # Laravel routing
├── index.php           # Laravel entry point
├── robots.txt          # SEO
└── web.config          # IIS config
```

### **Replace (Thay thế):**

```
backend/public/
├── index.html          # Vue SPA (NEW)
├── assets/             # Vue assets (NEW)
└── favicon.ico         # Vue favicon (NEW)
```

## ⚠️ **Lưu ý Production:**

1. **API URL**: Phải trỏ đúng domain production
2. **HTTPS**: Sau khi test, đổi thành HTTPS
3. **Cache**: Browser sẽ cache assets, có thể cần hard refresh
4. **Fallback**: Laravel vẫn serve API routes ở `/api/*`

## 🧪 **Test sau deployment:**

```bash
# Test API
curl http://catbacountrysidehomestay.com/api/rooms

# Test frontend
curl http://catbacountrysidehomestay.com/
```
