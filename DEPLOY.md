# Nginx Configuration for Lucky Spin (Laravel Reverb over HTTPS)

## Architecture

```
Browser → Nginx :443 (HTTPS + WSS) → Laravel App :80 (HTTP)
                                   → Reverb :8080 (WS)
```

---

## /etc/nginx/sites-available/lucky-spin

```nginx
map $http_upgrade $connection_upgrade {
    default upgrade;
    ''      close;
}

server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com;

    # TLS — use Certbot / Let's Encrypt
    ssl_certificate     /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    ssl_protocols       TLSv1.2 TLSv1.3;
    ssl_ciphers         ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256;

    root /var/www/spin/public;
    index index.php;

    # ── Laravel App ───────────────────────────────────────────────────────────
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass   unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include        fastcgi_params;
    }

    # ── Laravel Reverb WebSocket (proxied from wss:// → ws://) ───────────────
    location /app {
        proxy_pass             http://127.0.0.1:8080;
        proxy_http_version     1.1;
        proxy_set_header       Upgrade $http_upgrade;
        proxy_set_header       Connection $connection_upgrade;
        proxy_set_header       Host $host;
        proxy_set_header       X-Real-IP $remote_addr;
        proxy_set_header       X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header       X-Forwarded-Proto $scheme;
        proxy_read_timeout     60s;
        proxy_send_timeout     60s;
    }

    # ── Static assets ─────────────────────────────────────────────────────────
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff2?)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    location ~ /\.ht { deny all; }
}
```

---

## Supervisor — Keep Reverb Running

Create `/etc/supervisor/conf.d/reverb.conf`:

```ini
[program:reverb]
process_name=%(program_name)s
command=php /var/www/spin/artisan reverb:start --host=0.0.0.0 --port=8080 --no-interaction
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/reverb.log
```

Then:

```bash
supervisorctl reread
supervisorctl update
supervisorctl start reverb
```

---

## Post-Deploy Checklist

```bash
# 1. Clone and install
git clone … /var/www/spin
cd /var/www/spin
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# 2. Environment
cp .env.example .env
php artisan key:generate
# Edit .env: DB credentials, APP_URL, REVERB keys, ADMIN_PASSWORD hash

# 3. Database
php artisan migrate --force
php artisan db:seed --class=PrizeSeeder --force

# 4. Storage
php artisan storage:link
chown -R www-data:www-data storage bootstrap/cache

# 5. Caches (production)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Start Reverb via Supervisor (see above)

# 7. Reload Nginx
nginx -t && systemctl reload nginx
```

---

## .env Key Settings for Production

| Key                    | Value                     |
| ---------------------- | ------------------------- |
| `APP_ENV`              | `production`              |
| `APP_DEBUG`            | `false`                   |
| `APP_URL`              | `https://your-domain.com` |
| `BROADCAST_CONNECTION` | `reverb`                  |
| `REVERB_HOST`          | `0.0.0.0`                 |
| `REVERB_PORT`          | `8080`                    |
| `REVERB_SCHEME`        | `https`                   |
| `VITE_REVERB_HOST`     | `your-domain.com`         |
| `VITE_REVERB_PORT`     | `443`                     |
| `VITE_REVERB_SCHEME`   | `https`                   |
