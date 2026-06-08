# Lakkad Loha — Staff & Sales Management
## Deployment & Setup Guide

---

## 🗂 Project Structure

```
lakkadloha/
├── app/
│   ├── Http/Controllers/
│   │   ├── Auth/AuthController.php
│   │   ├── Admin/AuditLogController.php
│   │   ├── Staff/StaffController.php
│   │   ├── Api/  (AuthApiController, SalesApiController,
│   │   │         InventoryApiController, AttendanceApiController,
│   │   │         SalaryApiController)
│   │   ├── DashboardController.php
│   │   ├── InventoryController.php
│   │   ├── SalesController.php
│   │   ├── AttendanceController.php
│   │   ├── SalaryController.php
│   │   └── ReportsController.php
│   ├── Models/
│   │   ├── User.php           (roles: super_admin, manager, staff)
│   │   ├── Product.php
│   │   ├── StockMovement.php
│   │   ├── Sale.php
│   │   ├── SaleRefund.php
│   │   ├── Attendance.php
│   │   ├── SalaryRecord.php
│   │   ├── LoginHistory.php
│   │   └── AuditLog.php
│   ├── Notifications/LowStockNotification.php
│   └── Policies/  (ProductPolicy, SalePolicy, UserPolicy,
│                   AttendancePolicy, SalaryRecordPolicy)
├── database/
│   ├── migrations/  (7 migration files)
│   └── seeders/DatabaseSeeder.php
├── public/
│   ├── css/app.css      (full design system + dark/light mode)
│   ├── js/app.js        (theme, sidebar, PWA, helpers)
│   ├── sw.js            (service worker)
│   └── manifest.json    (PWA manifest)
├── resources/views/
│   ├── layouts/app.blade.php    (main layout with sidebar)
│   ├── layouts/auth.blade.php   (login layout)
│   ├── auth/                    (login, forgot-password, reset-password)
│   ├── dashboard/index.blade.php
│   ├── inventory/               (index, create, edit, show)
│   ├── sales/                   (index, create, edit, show, receipt)
│   ├── attendance/              (index, report)
│   ├── salary/                  (index, create, show, employee-summary)
│   ├── staff/                   (index, create, edit, show, profile)
│   ├── reports/                 (index, sales, attendance, salary, inventory)
│   └── admin/audit-logs.blade.php
└── routes/
    ├── web.php    (all web routes)
    └── api.php    (REST API v1 for Flutter)
```

---

## 🚀 Server Requirements

| Requirement | Version |
|-------------|---------|
| PHP | 8.3+ |
| MySQL | 8.0+ |
| Composer | 2.x |
| Nginx/Apache | Latest |
| Storage | 2GB+ |

**PHP Extensions:** mbstring, pdo_mysql, gd, zip, bcmath, exif, json, openssl

---

## 📦 Fresh Installation

### 1. Clone & Install

```bash
git clone https://github.com/yourusername/lakkadloha-staff.git
cd lakkadloha-staff

composer install --prefer-dist --no-interaction
```

### 2. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:
```env
APP_NAME="Lakkad Loha Staff"
APP_ENV=production
APP_URL=https://staff.lakkadloha.com

DB_HOST=127.0.0.1
DB_DATABASE=lakkadloha_staff
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_FROM_ADDRESS=noreply@lakkadloha.com

LOW_STOCK_THRESHOLD=10
```

### 3. Database

```bash
# Create DB first
mysql -u root -p -e "CREATE DATABASE lakkadloha_staff CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate

# Seed with demo data
php artisan db:seed
```

### 4. Storage & Permissions

```bash
php artisan storage:link
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 5. Optimize for Production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## 🌐 Nginx Configuration

```nginx
server {
    listen 80;
    server_name staff.lakkadloha.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name staff.lakkadloha.com;

    root /var/www/staff.lakkadloha.com/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/staff.lakkadloha.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/staff.lakkadloha.com/privkey.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, no-transform";
    }
}
```

---

## 🔐 Demo Credentials

After seeding:

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@lakkadloha.com | password123 |
| Manager | manager@lakkadloha.com | password123 |
| Staff | staff1@lakkadloha.com | password123 |

> **Change all passwords immediately in production!**

---

## 📱 Flutter API Integration

Base URL: `https://staff.lakkadloha.com/api/v1`

### Authentication
```
POST /auth/login
Body: { "email": "", "password": "", "device_name": "Flutter App" }
Returns: { "token": "...", "user": {...} }
```

### All endpoints require:
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

### Available Endpoints
```
GET     /auth/me
PUT     /auth/profile
POST    /auth/logout

GET     /dashboard
GET     /inventory
POST    /inventory
GET     /inventory/{id}
PUT     /inventory/{id}
POST    /inventory/{id}/add-stock

GET     /sales
POST    /sales
GET     /sales/{id}

GET     /attendance
GET     /attendance/today
POST    /attendance/check-in
POST    /attendance/check-out

GET     /salary
POST    /salary
GET     /salary/summary

GET     /notifications
POST    /notifications/{id}/read
```

---

## ⚙️ Queue Worker (for background notifications)

```bash
# Start worker (use Supervisor in production)
php artisan queue:work --sleep=3 --tries=3

# Supervisor config: /etc/supervisor/conf.d/lakkadloha-worker.conf
[program:lakkadloha-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/staff.lakkadloha.com/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/lakkadloha-worker.log
stopwaitsecs=3600
```

---

## 🔄 GitHub Actions Setup

Add these secrets to your GitHub repo (`Settings > Secrets`):

| Secret | Value |
|--------|-------|
| `SSH_HOST` | Your server IP |
| `SSH_USER` | SSH username |
| `SSH_PRIVATE_KEY` | Private key content |
| `SSH_PORT` | `22` |

---

## 📋 Post-Deploy Checklist

- [ ] Change all demo passwords
- [ ] Configure real SMTP credentials
- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Enable SSL certificate (Let's Encrypt)
- [ ] Set up Supervisor for queue worker
- [ ] Configure cron for scheduled tasks
- [ ] Create PWA icons (72, 96, 128, 144, 152, 192, 384, 512px) in `/public/icons/`
- [ ] Test low stock notifications
- [ ] Test PDF export (DomPDF requires GD extension)
- [ ] Verify storage symlink exists

---

## 🕐 Cron Job (Laravel Scheduler)

Add to server crontab:
```bash
* * * * * cd /var/www/staff.lakkadloha.com && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🛠 Common Issues

**PDF not generating:**
```bash
# Ensure GD is enabled
php -m | grep gd
# Install if missing
apt install php8.3-gd
```

**Storage not accessible:**
```bash
php artisan storage:link
# If symlink already exists:
rm public/storage && php artisan storage:link
```

**Permission issues:**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data .
```

**Session driver errors:**
```bash
php artisan migrate  # ensure sessions table exists
```

---

## 🎨 PWA Icons

Generate icons at https://realfavicongenerator.net/ or https://pwa-asset-generator.netlify.app/ and place them in `/public/icons/`:
- icon-72.png, icon-96.png, icon-128.png, icon-144.png
- icon-152.png, icon-192.png, icon-384.png, icon-512.png

---

*Built for Lakkad Loha — Saharanpur, UP, India*
