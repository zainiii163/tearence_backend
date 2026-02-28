# WWA API Production Deployment Guide

## Prerequisites
- Ubuntu 20.04+ or CentOS 8+ server
- Nginx web server
- PHP 8.1+ with required extensions
- MySQL 8.0+ or MariaDB 10.5+
- Redis server
- Composer and Node.js
- SSL certificate (Let's Encrypt recommended)

## Quick Deployment Steps

### 1. Server Setup
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install nginx php8.1-fpm php8.1-mysql php8.1-redis php8.1-mbstring php8.1-xml php8.1-curl php8.1-zip php8.1-gd php8.1-intl mysql-server redis-server -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### 2. Database Setup
```bash
sudo mysql
CREATE DATABASE wwa_api_production;
CREATE USER 'wwa_api_user'@'localhost' IDENTIFIED BY 'your_strong_password';
GRANT ALL PRIVILEGES ON wwa_api_production.* TO 'wwa_api_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Application Setup
```bash
# Clone repository
git clone your-repo-url /var/www/wwa-api
cd /var/www/wwa-api

# Copy production environment
cp env-production-example .env

# Generate app key
php artisan key:generate

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci --production

# Set permissions
sudo chown -R www-data:www-data /var/www/wwa-api
sudo chmod -R 755 storage bootstrap/cache
```

### 4. Nginx Configuration
Create `/etc/nginx/sites-available/wwa-api`:
```nginx
server {
    listen 80;
    server_name api.worldwideadverts.info;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name api.worldwideadverts.info;

    root /var/www/wwa-api/public;
    index index.php index.html;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/api.worldwideadverts.info/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.worldwideadverts.info/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Laravel configuration
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

    # Block access to sensitive files
    location ~ /\. {
        deny all;
    }

    location ~ ^/(composer\.json|composer\.lock|\.env|artisan) {
        deny all;
    }
}
```

### 5. Enable Site and SSL
```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/wwa-api /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx

# Install SSL certificate
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d api.worldwideadverts.info

# Setup auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

### 6. Final Deployment
```bash
# Run deployment script
chmod +x deploy-production.sh
./deploy-production.sh

# Setup supervisor for queue workers
sudo apt install supervisor -y
sudo systemctl enable supervisor
```

### 7. Supervisor Configuration
Create `/etc/supervisor/conf.d/wwa-api-worker.conf`:
```ini
[program:wwa-api-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/wwa-api/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/wwa-api-worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start wwa-api-worker:*
```

## Environment Variables
Update your `.env` file with:
- Database credentials
- JWT secret key
- PayPal API keys
- Mail configuration
- Redis configuration

## Monitoring
- Monitor logs: `tail -f /var/log/nginx/error.log`
- Monitor Laravel logs: `tail -f storage/logs/laravel.log`
- Monitor queue workers: `sudo supervisorctl status`

## Security Checklist
- [ ] SSL certificate installed
- [ ] Firewall configured (UFW)
- [ ] Database credentials secured
- [ ] App key generated
- [ ] Debug mode disabled
- [ ] File permissions set correctly
- [ ] Regular backups configured
