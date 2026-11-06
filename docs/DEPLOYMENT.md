# Deploying V2

## Hosting recommendation

The audience is one school in Iran, staff using it daily from Iranian networks. That
points at an Iranian host, not a foreign VPS:

- **Recommended: an Iranian VPS** (پارسپک / ایرانسرور / فاخ — any provider with KVM,
  Ubuntu 24.04 and a real SSH account). Size: 2 vCPU, 4 GB RAM, 60 GB SSD. This app is
  tiny; the database is a few MB and 207 students. Anything bigger is waste.
  - Why not Hetzner/Cloudflare: Iranian ISPs throttle or block international routes
    unevenly, and paying a European provider from Iran is a recurring annoyance. Staff
    would blame the app for the network.
  - Why a VPS over shared hosting: you need cron (nightly backup), `composer`, and the
    ability to run `php artisan`. Many Iranian shared plans give none of that.
- **Domain**: `.ir` through [nic.ir](https://www.nic.ir) — needs an Iranian national ID
  (or company registration), an Iranian bank card for the annual fee (about
  ۶۰۰٬۰۰۰ ریال from مهر ۱۴۰۴) and at least one nameserver. Budget one working day for
  approval. A school-owned name like `soltani5.ir` is worth having; the certificate is
  free either way.

If the school already has a host, the only hard requirements are: **PHP ≥ 8.2 with
pdo_mysql, mbstring, gd**, **MariaDB ≥ 10.4**, **composer**, **cron**, and **SSH**.

## Server setup (Ubuntu 24.04)

```bash
apt update && apt install -y nginx mariadb-server php8.3-fpm php8.3-mysql \
  php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath unzip git
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

adduser --system --group --home /var/www/soltani soltani
su -s /bin/bash soltani -c 'git clone https://github.com/Retro-Zero/soltani5.git app && cd app && git checkout v2'
cd /var/www/soltani/app
su -s /bin/bash soltani -c 'composer install --no-dev --optimize-autoloader'
cp .env.example .env
```

`.env` for production:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://soltani5.ir
DB_CONNECTION=mysql
DB_DATABASE=soltani
DB_USERNAME=soltani
DB_PASSWORD=<generated>
```

Then:

```bash
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force            # reference data: grades, fields, subjects
php artisan admin:create admin         # prints the password once — store it safely
php artisan config:cache && php artisan route:cache && php artisan view:cache
chown -R soltani:www-data storage bootstrap/cache && chmod -R ug+rwX storage bootstrap/cache
```

Moving the old data: on the OLD server (or from a dump), keep the V1 database reachable
read-only, set `V1_DB_*` in `.env`, then `php artisan v1:import` and require
`php artisan v1:import --verify-only` to print `Parity: exact match.` before going live.

nginx (`/etc/nginx/sites-available/soltani`):

```nginx
server {
    listen 443 ssl http2;
    server_name soltani5.ir;
    root /var/www/soltani/app/public;
    index index.php;
    client_max_body_size 12m;

    add_header X-Frame-Options SAMEORIGIN;
    add_header X-Content-Type-Options nosniff;

    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }
    location ~ /\.(?!well-known).* { deny all; }
}
```

TLS: `certbot --nginx -d soltani5.ir` (Let's Encrypt works for `.ir`).

Cron:

```
* * * * * cd /var/www/soltani/app && php artisan schedule:run >> /dev/null 2>&1
```

That runs `db:backup` nightly at 02:30 (`storage/app/backups`, 14 dumps kept, verified by
restoring one into a scratch database).

## Backups — the part that matters

The database is the school's financial history. Two copies, one off the machine:

```bash
# nightly, after the app's own dump
rsync -az --delete /var/www/soltani/app/storage/app/backups/ backup@other-host:/srv/soltani/
```

Test a restore once a month: `gunzip -c file.sql.gz | mariadb scratch_db`.

## Before handing it over

- [ ] `APP_DEBUG=false`, `APP_ENV=production`, caches built.
- [ ] Create the staff login with `admin:create` and store the printed password somewhere the school can find it.
- [ ] Add the CI workflow (Pint + tests on push); it needs a token with the `workflow` scope
      to be pushed — `gh auth refresh -s workflow`.
- [ ] Confirm `/` redirects to `/login` when logged out (this is the whole point of V2).
- [ ] Run `php artisan v1:import --verify-only` on the production database.
- [ ] One full restore rehearsal from the off-site copy.
- [ ] Decide whether the demo/dev database keeps real student records — scrub them
      (`national_id`, `phone`) before showing anyone outside the school.
