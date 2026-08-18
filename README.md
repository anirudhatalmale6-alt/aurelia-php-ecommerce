# Aurelia — PHP e-commerce storefront

A complete, working store built in plain PHP 8 with a small MVC core: storefront,
customer accounts, checkout with a pluggable payment gateway, and an admin panel
for inventory, categories and orders.

This is a **demo build** put together so you can judge the work on your own brief
before committing to anything. Colours, copy and placeholder imagery are stand-ins —
they get replaced with your logo, palette and product photography.

---

## What is in here

**Storefront**
- Home page with featured products, category rail and trust panel
- Catalogue with category filter, max-price filter, in-stock filter, 4 sort orders and pagination
- Live search with a typeahead dropdown (`/api/suggest`)
- Product pages with sale pricing, stock states, quantity stepper and related products
- Session basket with quantity edit and per-line removal
- Guest or signed-in checkout, flat-rate shipping with a free-delivery threshold
- Order confirmation page + transactional email

**Customer accounts**
- Register / sign in / sign out, session regenerated on every privilege change
- Order history and per-order detail (scoped so customers can only read their own)
- Profile editing and password change
- Password reset by emailed single-use token, valid for one hour

**Admin panel** (`/admin`)
- Dashboard: revenue, order and customer counts, 14-day revenue chart, best sellers, low-stock list
- Products: create, edit, delete, image upload, live/hidden and featured toggles
- Categories: create, rename, reorder, delete (products survive as uncategorised)
- Orders: filter by status, keyword search, order detail, status changes that email the customer
- Customers: account list with order counts

**Payments**
- `Gateway` interface with two implementations: `StripeGateway` (Stripe Checkout over
  the REST API, plus a signature-verified webhook) and `DemoGateway` (an in-app
  sandbox page so the full flow works before keys exist)
- Adding PayPal or a local provider means one new class — checkout does not change

---

## Running it

Requires PHP 8.1+ with `pdo`, `gd` and `curl`. No Composer dependencies.

```bash
cp .env.example .env
php database/seed.php --fresh          # creates the schema and demo content
php -S 127.0.0.1:8000 -t public server.php
```

Open <http://127.0.0.1:8000>.

**Demo logins**

| Role     | Email                  | Password          |
|----------|------------------------|-------------------|
| Admin    | admin@aurelia.test     | `AdminPass123`    |
| Customer | customer@aurelia.test  | `CustomerPass123` |

At checkout the sandbox gateway pays with one click — no card details are collected
or transmitted.

### MySQL / MariaDB

The demo runs on SQLite so it starts with no setup. Production runs on MySQL —
the application code is identical, only the DSN changes:

```bash
mysql -u root -p -e "CREATE DATABASE aurelia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
# in .env:
DB_DRIVER=mysql
DB_DATABASE=aurelia
DB_USERNAME=…
DB_PASSWORD=…
php database/seed.php --fresh
```

`database/schema.mysql.sql` is the authoritative schema (InnoDB, foreign keys,
`utf8mb4`); `database/schema.sqlite.sql` mirrors it column for column.

### Going live with Stripe

```
PAYMENT_DRIVER=stripe
STRIPE_SECRET=sk_live_…
STRIPE_PUBLISHABLE=pk_live_…
STRIPE_WEBHOOK_SECRET=whsec_…
```

Add a webhook in the Stripe dashboard pointing at `https://yourdomain/webhooks/stripe`
for the `checkout.session.completed` event. If `PAYMENT_DRIVER=stripe` is set but no
secret is present, checkout falls back to the sandbox instead of breaking.

### Email

`MAIL_DRIVER=log` (default) writes every message to `storage/logs/mail.log`, so
nothing is sent from a development machine. `MAIL_DRIVER=mail` uses PHP's `mail()`.
An SMTP transport slots into `app/Core/Mailer.php` without touching callers.

---

## Deploying

Point the document root at `public/`. Everything else — `app/`, `config/`,
`storage/`, `.env` — sits above the web root and cannot be requested.

**Apache**: `public/.htaccess` is included; enable `mod_rewrite`.

**nginx**:

```nginx
root /var/www/aurelia/public;
index index.php;
location / { try_files $uri $uri/ /index.php?$query_string; }
location ~ \.php$ {
    include fastcgi_params;
    fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
}
```

Set `APP_DEBUG=false` in production, and make `storage/` writable by the web user.

---

## Layout

```
app/
  Core/            Router, Request, View, Database, Auth, Cart, Csrf, Flash,
                   Validator, Mailer, Uploader, Middleware
  Core/Payments/   Gateway interface, StripeGateway, DemoGateway, PaymentManager
  Controllers/     Shop, Cart, Checkout, Auth, Account, Media + Admin/*
  Models/          Product, Category, User, Order, PasswordReset
  Views/           layouts, shop, auth, account, admin, emails, errors
  Support/         template helpers
config/            config.php (env-driven), routes.php (full route table)
database/          schema.mysql.sql, schema.sqlite.sql, seed.php
public/            index.php front controller, .htaccess, assets
storage/           uploads, logs, sqlite database (not web-accessible)
```

Adding a feature means one route line, one controller method and one view. The
pieces the brief lists as "later" — discount codes, reviews, a wish-list — each fit
that shape without touching what is already here.

---

## Security notes

- Every query is a bound prepared statement; no SQL is built by concatenation
- All template output is escaped through `e()`
- CSRF token verified on every POST
- Passwords hashed with `password_hash()` and transparently re-hashed on cost changes
- Session id regenerated on login, logout and registration
- Login and password-reset forms are rate limited
- Reset tokens stored as SHA-256 hashes, single use, one-hour expiry
- Uploads validated by real MIME type, renamed randomly and re-encoded through GD
- Cart prices are re-read from the database on every request — a tampered session
  cannot change what a customer is charged
- Order totals are snapshotted per line, so later price edits never rewrite history
- `X-Content-Type-Options`, `X-Frame-Options` and `Referrer-Policy` set on every response

## Verified

The whole flow was run end to end in a real browser (Playwright, 1280×800 and 390×780):
browse → search → product → basket → register → checkout → sandbox payment →
confirmation → order history → admin sign-in → add a product → the product appearing
on the storefront → order status change. Screenshots are in `screenshots/`.
The password-reset flow was verified separately, token included.
