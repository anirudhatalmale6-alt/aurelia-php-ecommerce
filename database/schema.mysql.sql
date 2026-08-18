-- --------------------------------------------------------------------------
-- Aurelia storefront - MySQL / MariaDB schema
-- --------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS categories (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120) NOT NULL,
    slug        VARCHAR(140) NOT NULL UNIQUE,
    description TEXT NULL,
    position    INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id       INT UNSIGNED NULL,
    name              VARCHAR(180) NOT NULL,
    slug              VARCHAR(200) NOT NULL UNIQUE,
    sku               VARCHAR(60) NULL,
    short_description VARCHAR(300) NULL,
    description       TEXT NULL,
    price             DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    sale_price        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock             INT NOT NULL DEFAULT 0,
    image             VARCHAR(255) NULL,
    is_active         TINYINT(1) NOT NULL DEFAULT 1,
    is_featured       TINYINT(1) NOT NULL DEFAULT 0,
    created_at        DATETIME NULL,
    updated_at        DATETIME NULL,
    INDEX idx_products_category (category_id),
    INDEX idx_products_active (is_active),
    FULLTEXT KEY ft_products_search (name, description),
    CONSTRAINT fk_products_category FOREIGN KEY (category_id)
        REFERENCES categories (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(120) NOT NULL,
    email         VARCHAR(190) NOT NULL UNIQUE,
    phone         VARCHAR(40) NULL,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('customer','admin') NOT NULL DEFAULT 'customer',
    created_at    DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reference         VARCHAR(40) NOT NULL UNIQUE,
    user_id           INT UNSIGNED NULL,
    customer_name     VARCHAR(120) NOT NULL,
    email             VARCHAR(190) NOT NULL,
    phone             VARCHAR(40) NULL,
    address_line1     VARCHAR(160) NOT NULL,
    address_line2     VARCHAR(160) NULL,
    city              VARCHAR(120) NOT NULL,
    postcode          VARCHAR(40) NOT NULL,
    country           VARCHAR(80) NOT NULL,
    notes             TEXT NULL,
    subtotal          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    shipping          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    tax               DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total             DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status            VARCHAR(20) NOT NULL DEFAULT 'pending',
    payment_method    VARCHAR(60) NULL,
    payment_reference VARCHAR(120) NULL,
    paid_at           DATETIME NULL,
    created_at        DATETIME NULL,
    updated_at        DATETIME NULL,
    INDEX idx_orders_user (user_id),
    INDEX idx_orders_status (status),
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id)
        REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id     INT UNSIGNED NOT NULL,
    product_id   INT UNSIGNED NULL,
    product_name VARCHAR(180) NOT NULL,
    sku          VARCHAR(60) NULL,
    unit_price   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    quantity     INT NOT NULL DEFAULT 1,
    line_total   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    INDEX idx_items_order (order_id),
    CONSTRAINT fk_items_order FOREIGN KEY (order_id)
        REFERENCES orders (id) ON DELETE CASCADE,
    CONSTRAINT fk_items_product FOREIGN KEY (product_id)
        REFERENCES products (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_resets (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(190) NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NULL,
    INDEX idx_resets_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
