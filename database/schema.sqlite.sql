-- --------------------------------------------------------------------------
-- Aurelia storefront - SQLite schema (used for the zero-config demo build).
-- Mirrors database/schema.mysql.sql column for column.
-- --------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS categories (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT NOT NULL,
    slug        TEXT NOT NULL UNIQUE,
    description TEXT,
    position    INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS products (
    id                INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id       INTEGER REFERENCES categories(id) ON DELETE SET NULL,
    name              TEXT NOT NULL,
    slug              TEXT NOT NULL UNIQUE,
    sku               TEXT,
    short_description TEXT,
    description       TEXT,
    price             REAL NOT NULL DEFAULT 0,
    sale_price        REAL NOT NULL DEFAULT 0,
    stock             INTEGER NOT NULL DEFAULT 0,
    image             TEXT,
    is_active         INTEGER NOT NULL DEFAULT 1,
    is_featured       INTEGER NOT NULL DEFAULT 0,
    created_at        TEXT,
    updated_at        TEXT
);
CREATE INDEX IF NOT EXISTS idx_products_category ON products (category_id);
CREATE INDEX IF NOT EXISTS idx_products_active   ON products (is_active);

CREATE TABLE IF NOT EXISTS users (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    name          TEXT NOT NULL,
    email         TEXT NOT NULL UNIQUE,
    phone         TEXT,
    password_hash TEXT NOT NULL,
    role          TEXT NOT NULL DEFAULT 'customer',
    created_at    TEXT
);

CREATE TABLE IF NOT EXISTS orders (
    id                INTEGER PRIMARY KEY AUTOINCREMENT,
    reference         TEXT NOT NULL UNIQUE,
    user_id           INTEGER REFERENCES users(id) ON DELETE SET NULL,
    customer_name     TEXT NOT NULL,
    email             TEXT NOT NULL,
    phone             TEXT,
    address_line1     TEXT NOT NULL,
    address_line2     TEXT,
    city              TEXT NOT NULL,
    postcode          TEXT NOT NULL,
    country           TEXT NOT NULL,
    notes             TEXT,
    subtotal          REAL NOT NULL DEFAULT 0,
    shipping          REAL NOT NULL DEFAULT 0,
    tax               REAL NOT NULL DEFAULT 0,
    total             REAL NOT NULL DEFAULT 0,
    status            TEXT NOT NULL DEFAULT 'pending',
    payment_method    TEXT,
    payment_reference TEXT,
    paid_at           TEXT,
    created_at        TEXT,
    updated_at        TEXT
);
CREATE INDEX IF NOT EXISTS idx_orders_user   ON orders (user_id);
CREATE INDEX IF NOT EXISTS idx_orders_status ON orders (status);

CREATE TABLE IF NOT EXISTS order_items (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id     INTEGER NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
    product_id   INTEGER REFERENCES products(id) ON DELETE SET NULL,
    product_name TEXT NOT NULL,
    sku          TEXT,
    unit_price   REAL NOT NULL DEFAULT 0,
    quantity     INTEGER NOT NULL DEFAULT 1,
    line_total   REAL NOT NULL DEFAULT 0
);
CREATE INDEX IF NOT EXISTS idx_items_order ON order_items (order_id);

CREATE TABLE IF NOT EXISTS password_resets (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    email      TEXT NOT NULL,
    token_hash TEXT NOT NULL,
    expires_at TEXT NOT NULL,
    created_at TEXT
);
CREATE INDEX IF NOT EXISTS idx_resets_email ON password_resets (email);
