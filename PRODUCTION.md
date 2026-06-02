# 🚀 Production Deployment Checklist

## ✅ Pre-deployment (Already Done)

- [x] Upload project files to server
- [x] Upload database

## 🔧 Configuration Steps

### 1. Update .env file

```bash
# Copy production template
cp .env.production .env

# Edit with your actual values:
APP_URL=https://yourdomain.com
DB_HOST=localhost
DB_DATABASE=your_actual_database_name
DB_USERNAME=your_actual_username
DB_PASSWORD=your_actual_password
```

### 2. Set Document Root in aaPanel

- Go to **Website > yoursite.com > Settings > Website Directory**
- Set to: `/www/wwwroot/yourdomain.com/backend/public`
- Enable **Prevent Cross-site Access**

### 3. PHP Configuration

- **PHP Version**: 8.2 or higher
- **Required Extensions**:
  - BCMath
  - Ctype
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML

### 4. Run Deployment Script

```bash
# SSH to server or use aaPanel Terminal
cd /www/wwwroot/yourdomain.com
chmod +x deploy.sh
./deploy.sh
```

### 5. Web Server Configuration (Nginx)

Add to site config in aaPanel:

```nginx
location / {
    try_files $uri $uri/ /index.html;
}

location /api {
    try_files $uri $uri/ /index.php?$query_string;
}

# Handle Vue.js routing
location ~* ^.+\.(css|js|jpg|jpeg|gif|png|ico|svg|woff|woff2)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

## 🔐 Security & SSL

### 6. Enable SSL Certificate

- In aaPanel: **Website > yoursite.com > SSL**
- Choose **Let's Encrypt** (Free)
- Force HTTPS redirect

### 7. File Permissions

```bash
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chown -R www:www storage/ bootstrap/cache/
```

## 🧪 Testing

### 8. Test Functionality

- [ ] Homepage loads correctly
- [ ] API endpoints work (`/api/rooms`, `/api/tours`)
- [ ] Admin login works
- [ ] Room/tour booking works
- [ ] File uploads work
- [ ] Email notifications work (if configured)

### 9. Performance Optimization

```bash
# In backend directory
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🔧 Troubleshooting

### Common Issues:

**500 Error**:

- Check `storage/logs/laravel.log`
- Verify file permissions
- Check .env database credentials

**API Routes Not Working**:

- Verify Nginx configuration
- Check if `/api` routes are properly configured

**Frontend Not Loading**:

- Check if files are in `backend/public/`
- Verify build process completed successfully

**Database Connection Failed**:

- Test database credentials
- Check database server status in aaPanel

### Debug Commands:

```bash
# Check Laravel status
php artisan about

# Test database connection
php artisan migrate:status

# Clear all caches
php artisan optimize:clear

# View logs
tail -f storage/logs/laravel.log
```

## 📞 Support

If you encounter issues:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Check web server error logs in aaPanel
3. Verify database connection in aaPanel MySQL manager
4. Test API endpoints directly: `https://yourdomain.com/api/rooms`
