# Migrating to cPanel

Package (rebuild any time with `deploy/build-package.ps1`, see bottom):

- `deploy/dist/incentive-system.zip` - app files (no demo setup script, no dev router)
- `deploy/dist/incentive_system.sql` - clean database: tables, 3 roles, 7 default incentive rules, one Admin user. No demo users or deals.

Requirements on the host: PHP 8.1 or newer (8.3 recommended) with `pdo_mysql`; MySQL 5.7+ or MariaDB 10.4+; mod_rewrite (standard on cPanel).

## 1. Database
1. cPanel > **MySQL Databases**: create a database and a user, then add the user to the database with **ALL PRIVILEGES**. Note the full names - cPanel prefixes them with your account name (e.g. `cpuser_incentive`).
2. cPanel > **phpMyAdmin**: select that database > **Import** > choose `incentive_system.sql` > Go.

## 2. Files (choose one layout)

**A. Subdomain pointing at `public/` (recommended - config and code stay outside the web root)**
1. cPanel > **Domains** (or Subdomains): create e.g. `incentive.yourdomain.com` with document root `/incentive-system/public` (a folder next to `public_html`).
2. **File Manager**: upload `incentive-system.zip` into `/home/<cpuser>/incentive-system/` and **Extract**. Delete the zip afterwards.

**B. Inside an existing site, e.g. `https://yourdomain.com/incentive`**
1. Upload and extract the zip into `public_html/incentive/`.
2. The root `.htaccess` routes everything into `public/`, so `config.php`, `core/`, etc. are not reachable.

## 3. PHP version
cPanel > **MultiPHP Manager**: set the domain to PHP 8.1+ (8.3 recommended). In **Select PHP Version > Extensions** make sure `pdo_mysql` is ticked.

## 4. Config
In File Manager, copy `config.local.sample.php` to `config.local.php` and edit:

```php
define('APP_ENV', 'production');
define('APP_URL', 'https://incentive.yourdomain.com');   // layout B: https://yourdomain.com/incentive
define('DB_NAME', 'cpuser_incentive');
define('DB_USER', 'cpuser_incentive');
define('DB_PASS', '...');
```
`APP_ENV=production` turns off error display (errors go to `storage/error.log`), hides the demo-accounts hint, and marks the session cookie Secure when APP_URL is https.

## 5. SSL
cPanel > **SSL/TLS Status** > Run AutoSSL. Once the certificate is active, open `public/.htaccess` and uncomment the three "Force HTTPS" lines.

## 6. First login
Open the site and sign in with username `andyyoon` (or its placeholder email `andyyoon@incentive.local`). Then:
- **Agents** page: edit the admin to set a real email and change the password.
- Add your managers and agents there.

## 7. Checklist
- [ ] `https://.../setup.php` returns 404 (it is not in the package)
- [ ] `https://.../config.php` and `https://.../storage/error.log` are not downloadable
- [ ] Log in, add a deal, approve it, run a calculation, create and complete a payout batch
- [ ] Back up the database regularly (cPanel > Backup, or phpMyAdmin > Export)

## Rebuilding the package
```powershell
powershell -ExecutionPolicy Bypass -File deploy\build-package.ps1 -AdminUsername andyyoon -AdminPassword '<password>' -AdminName 'Andy Yoon'
```
This builds the SQL from a temporary clean database; the local dev/demo database is never touched.
