# SecuroFi.Tech Staging Environment Setup Guide (PRD-ADDNEW 6.2)

This guide documents the architecture, configuration, and security controls for the Staging environment at `staging.securofi.tech`.

---

## 1. Staging Architecture Overview

- **Host Domain**: `staging.securofi.tech`
- **Application Environment**: `APP_ENV=staging`
- **Debug Mode**: `APP_DEBUG=false` (to mirror production behavior while logging errors to Sentry)
- **Database**: Dedicated staging database (`securofi_staging`), completely isolated from production.
- **Offsite Storage / S3**: Dedicated staging bucket prefix (`staging/backups`, `staging/uploads`).

---

## 2. Security & Anti-Indexing Directives (PRD Requirement)

### A. HTTP Headers Anti-Indexing
The application's `SecurityHeadersMiddleware` automatically detects any request where `APP_ENV=staging` or the hostname contains `staging` and injects:
```http
X-Robots-Tag: noindex, nofollow, noarchive
```

### B. Robots.txt on Staging
In your staging web server or Cloudflare root:
```txt
User-agent: *
Disallow: /
```

### C. Web Server Basic Authentication (Nginx / Apache)
To prevent unauthorized public access, enable HTTP Basic Authentication in Nginx:
```nginx
server {
    server_name staging.securofi.tech;
    root /var/www/staging.securofi.tech/public;

    # Basic Auth Protection
    auth_basic "SecuroFi Staging Environment - Restricted Access";
    auth_basic_user_file /etc/nginx/.htpasswd_staging;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    }
}
```

Or for Apache in `.htaccess`:
```apache
<IfModule mod_auth_basic.c>
    AuthType Basic
    AuthName "SecuroFi Staging"
    AuthUserFile /var/www/staging.securofi.tech/.htpasswd
    Require valid-user
</IfModule>
```

---

## 3. Staging `.env` Template
```env
APP_NAME="SecuroFi (Staging)"
APP_ENV=staging
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://staging.securofi.tech

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=securofi_staging
DB_USERNAME=securofi_staging_user
DB_PASSWORD="<STRONG_STAGING_PASSWORD>"

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Error Monitoring (Sentry)
SENTRY_LARAVEL_DSN="<STAGING_SENTRY_DSN>"
```

---

## 4. Automated Deployment Flow (GitHub Actions)
1. Developers push code to the `staging` git branch:
   ```bash
   git checkout staging
   git merge feature/new-module
   git push origin staging
   ```
2. The GitHub Actions CI/CD pipeline (`.github/workflows/ci-cd.yml`):
   - Automatically provisions PHP 8.2 and dependencies.
   - Executes all PHPUnit and Pest feature tests.
   - Upon green tests, SSHes into `staging.securofi.tech`.
   - Runs `git pull`, `composer install --no-dev`, `php artisan migrate --force`, and `php artisan optimize`.
3. Quality Assurance is performed on the staging URL before merging into `main` for production release.
