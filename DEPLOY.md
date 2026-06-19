# Deploying to Hostinger (shared hosting + SSH)

Domain: **saloon.malayznbeat.com** · App path used below: `~/domains/saloon.malayznbeat.com`

> Compiled frontend assets (`public/build`) are committed to the repo because Hostinger
> shared hosting has no Node.js. To update the site UI later, run `npm run build` locally,
> commit, push, then `git pull` on the server.

## 0. Prerequisites (in hPanel, one-time)

1. **PHP version** → set to **8.2** or newer (Advanced → PHP Configuration).
2. **MySQL database** → create one (Databases → MySQL). Note the **database name**,
   **username**, **password** (host is `localhost`). They look like `u450711979_saloon`.

## 1. Clone the app (NOT inside public_html)

```bash
cd ~/domains/saloon.malayznbeat.com

# back up the default public_html, then clone the app beside it
mv public_html public_html_backup
git clone https://github.com/aktharmd22/angel-krown-beauty.git app
```

## 2. Install PHP dependencies

```bash
cd ~/domains/saloon.malayznbeat.com/app
composer install --no-dev --optimize-autoloader
```

## 3. Configure the environment

```bash
cp .env.example .env
nano .env
```

Set these values (see the template in the deploy chat / below), then save (Ctrl-O, Enter, Ctrl-X):

```env
APP_NAME="Angel Krown"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://saloon.malayznbeat.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=uXXXXXXXXX_saloon
DB_USERNAME=uXXXXXXXXX_saloon
DB_PASSWORD=your-db-password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync
```

Then:

```bash
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan filament:assets
php artisan optimize        # caches config, routes, views
```

> If you use `SESSION_DRIVER=database` / `CACHE_STORE=database`, the `sessions` and `cache`
> tables are created by the migrations. To avoid DB setup you can instead use
> `SESSION_DRIVER=file` and `CACHE_STORE=file`.

## 4. Point the web root at Laravel's `public/`

```bash
cd ~/domains/saloon.malayznbeat.com
ln -s app/public public_html
```

Visit **https://saloon.malayznbeat.com** — the site should load.
Admin panel: **https://saloon.malayznbeat.com/admin**

### If the symlink doesn't serve (some plans lock public_html)

Keep `public_html` as a real folder and bootstrap Laravel from it:

```bash
cd ~/domains/saloon.malayznbeat.com
rm public_html                       # remove the symlink
mkdir public_html
cp -r app/public/. public_html/      # copy public assets in
```

Edit `public_html/index.php` and change the two `__DIR__.'/../...'` paths to point to `app`:

```php
require __DIR__.'/../app/vendor/autoload.php';
$app = require_once __DIR__.'/../app/bootstrap/app.php';
```

(With this method, re-copy `app/public/build` into `public_html/build` whenever you update assets.)

## 5. Create the admin user

```bash
cd ~/domains/saloon.malayznbeat.com/app
php artisan make:filament-user
```

## 6. (Optional) Cron for broadcasts & reminders

In hPanel → Advanced → Cron Jobs, add (every minute):

```
php ~/domains/saloon.malayznbeat.com/app/artisan schedule:run
```

## Updating later

```bash
cd ~/domains/saloon.malayznbeat.com/app
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan filament:assets
php artisan optimize:clear && php artisan optimize
```
