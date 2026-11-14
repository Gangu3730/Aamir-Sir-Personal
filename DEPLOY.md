# Deploying to Hostinger (shared hosting)

Laravel needs PHP >= 8.3 and the extensions `pdo_mysql, mbstring, openssl, fileinfo, gd, zip`. Pick PHP 8.3+ in hPanel > Advanced > PHP Configuration.

## One-time setup
1. hPanel > Databases: create a MySQL database + user. Note host `127.0.0.1`, name, user, password.
2. Upload the project to `~/laravel` (one level **above** `public_html`), e.g. via Git (SSH) or a zip built locally:
   `composer install --no-dev --optimize-autoloader` (run locally if the server has no Composer), then upload including `vendor/`.
3. Point the domain to the `public` folder: either set the domain's document root to `~/laravel/public`, or copy `public/*` into `public_html`
   and edit `public_html/index.php` paths to `../laravel/...`.
4. Create `~/laravel/.env` from `.env.example` and set:
   `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://aamirsheeraz.com`, DB_* values,
   `ADMIN_EMAIL`, `ADMIN_PASSWORD` (strong, temporary), and SMTP: `MAIL_MAILER=smtp`, `MAIL_HOST=smtp.hostinger.com`, `MAIL_PORT=465`,
   `MAIL_ENCRYPTION=ssl`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS=no-reply@aamirsheeraz.com`.
5. `php artisan key:generate && php artisan migrate --seed --force && php artisan storage:link && php artisan config:cache && php artisan route:cache && php artisan view:cache`
   (no SSH? run the same through a temporary web route or hPanel's cron/"Run PHP script" - ask Claude to prepare a packaged release.)
6. Log in at `/login` with the admin account, **change the password immediately** (Profile page).
7. Email deliverability: add SPF + DKIM records for the sending domain in hPanel > Emails.

## Releases / rollback
- Tag each release in Git (`git tag v1.0.0`). Rollback = redeploy the previous tag + restore the DB backup taken before `migrate`.
- Always back up before migrating: `mysqldump -u USER -p DB > backup-$(date +%F).sql`.

## Cut-over from WordPress
1. Back up the old WordPress site (files + DB) via hPanel.
2. Deploy the Laravel app to a staging subdomain first and test the flow: apply -> approve -> email -> set password -> login -> comment.
3. Switch the main domain to the Laravel app. Old URLs are preserved (`/about-me`, `/professional`, `/login`, `/registration`, `/lost-password`, old article URLs redirect into the community).
