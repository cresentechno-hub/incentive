# Incentive Management System — Setup Guide

## Laragon vHost Setup

1. **Open Laragon**, click the gear icon → **Apache → sites-enabled** (or use the built-in vHost wizard).

2. Add a vhost pointing the document root to the `public/` folder:

   **Via Laragon UI:**
   - Right-click the Laragon tray icon → **Quick App** → choose a name (e.g. `incentive`) — Laragon creates `http://incentive.test` automatically pointing to `C:\laragon\www\incentive\`.
   - **Or** place this entire project at `C:\laragon\www\incentive-system\` and Laragon auto-creates `http://incentive-system.test`.

   **For the document root to be `public/`, edit the Apache vhost manually:**

   Open `C:\laragon\etc\apache2\sites-enabled\auto.incentive-system.test.conf` and change:
   ```apache
   DocumentRoot "C:/laragon/www/incentive-system"
   ```
   to:
   ```apache
   DocumentRoot "C:/laragon/www/incentive-system/public"
   <Directory "C:/laragon/www/incentive-system/public">
       AllowOverride All
       Require all granted
   </Directory>
   ```
   Then restart Apache.

3. **Update `config.php`** with your database credentials:
   ```php
   define('DB_USER', 'root');
   define('DB_PASS', '');          // Laragon default is blank
   define('APP_URL', 'http://incentive-system.test');
   ```

## First-time Database Setup

Visit **http://incentive-system.test/setup.php** in your browser.

The setup script will:
- Create the `incentive_system` MySQL database
- Import the full schema (8 tables with indexes and FKs)
- Seed incentive rules (7 rules: flat/percentage/tier)
- Create demo users with hashed passwords
- Seed sample deals for the current month

### Demo Accounts (password: `password123`)

| Role    | Email                  |
|---------|------------------------|
| Admin   | admin@demo.test        |
| Manager | manager@demo.test      |
| Agent   | alice@demo.test        |
| Agent   | bob@demo.test          |
| Agent   | carol@demo.test        |

> **Delete `public/setup.php` after setup for security.**

## First Steps After Setup

1. Log in as **Admin** → Dashboard shows system-wide stats
2. Go to **Incentives → Rules** to review the 7 pre-seeded rules
3. Go to **Deals** → approve some of the sample pending deals
4. Go to **Incentives → Calculate** → select current month → Run
5. Go to **Incentives → Ledger** to review earned amounts
6. Go to **Payouts → New Batch** to create a payout batch
7. Complete the batch to mark incentives as Paid

## Architecture

```
incentive-system/
├── config.php              ← DB + app config
├── bootstrap.php           ← autoloader + session init
├── core/
│   ├── Database.php        ← PDO singleton with query helpers
│   ├── Session.php         ← Session + flash messages
│   └── Auth.php            ← Auth check, login, RBAC
├── models/                 ← BaseModel + 6 domain models
├── helpers/
│   ├── IncentiveCalculator.php  ← Core commission engine
│   ├── Security.php        ← CSRF, sanitize, validate
│   └── helpers.php         ← render(), redirect(), url(), money()
├── controllers/            ← 6 controllers (Auth/Dashboard/Deal/Incentive/Agent/Payout)
├── views/                  ← PHP templates (Bootstrap 5 + Chart.js)
├── database/
│   └── schema.sql          ← Full DDL + rule seed data
└── public/                 ← Document root
    ├── index.php           ← Router + dispatcher
    ├── setup.php           ← One-time setup (delete after use)
    └── .htaccess           ← URL rewriting
```

## Incentive Rule Types

| Type       | How it works |
|------------|-------------|
| `flat`     | Earn a fixed dollar amount when `deal_value` ≥ min_threshold |
| `percentage` | Earn `rate_value`% of `deal_value` when deal is in [min, max] range |
| `tier`     | Earn `rate_value`% of `deal_value` when agent's **period total sales** falls in [min, max] range |

Multiple rules can match a single deal — all matching rules create separate ledger entries.

## Security Features

- **CSRF tokens** on every state-changing form (`Security::csrfField()` / `Security::requireCsrf()`)
- **PDO prepared statements** for all database queries
- **`htmlspecialchars`** escaping via `e()` on all output
- **`password_hash`/`password_verify`** (bcrypt) for authentication
- **Role checks** (`Auth::require(...)`) on every controller action
- **Audit log** records every create/update/delete/approval action
- **Session regeneration** on login
- **HTTP-only session cookies**
