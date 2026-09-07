CREATE DATABASE IF NOT EXISTS cafelia;

USE cafelia;


-- =========================================
-- USERS TABLE
-- =========================================

CREATE TABLE IF NOT EXISTS users (

    id INT AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(100) NOT NULL,

    email VARCHAR(100) NOT NULL UNIQUE,

    password VARCHAR(255) NOT NULL,

    role ENUM('customer', 'admin')
        NOT NULL DEFAULT 'customer',

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP

);


-- =========================================
-- PRODUCTS TABLE
-- =========================================

CREATE TABLE IF NOT EXISTS products (

    id INT AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(100) NOT NULL,

    category VARCHAR(100) NOT NULL,

    description TEXT,

    price DECIMAL(10,2) NOT NULL,

    image VARCHAR(255),

    status ENUM('available', 'unavailable')
        NOT NULL DEFAULT 'available',

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP

);


-- =========================================
-- ORDERS TABLE
-- =========================================

CREATE TABLE IF NOT EXISTS orders (

    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    total_amount DECIMAL(10,2) NOT NULL,

    status ENUM(
        'Pending',
        'Preparing',
        'Completed',
        'Cancelled'
    ) NOT NULL DEFAULT 'Pending',

    order_date TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE

);


-- =========================================
-- ORDER ITEMS TABLE
-- =========================================

CREATE TABLE IF NOT EXISTS order_items (

    id INT AUTO_INCREMENT PRIMARY KEY,

    order_id INT NOT NULL,

    product_id INT NOT NULL,

    quantity INT NOT NULL,

    price DECIMAL(10,2) NOT NULL,

    subtotal DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (order_id)
        REFERENCES orders(id)
        ON DELETE CASCADE,

    FOREIGN KEY (product_id)
        REFERENCES products(id)
        ON DELETE CASCADE

);


-- =========================================
-- DEFAULT ADMIN ACCOUNT
-- =========================================

INSERT IGNORE INTO users
(
    name,
    email,
    password,
    role
)

VALUES
(
    'Cafelia Admin',
    'admin@cafelia.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCzqg5M6M8m9Fh8Z0w',
    'admin'
);


-- =========================================
-- SAMPLE PRODUCTS
-- =========================================

INSERT INTO products
(
    name,
    category,
    description,
    price,
    image,
    status
)

VALUES

(
    'Espresso',
    'Coffee',
    'Rich and bold espresso made from freshly roasted coffee beans.',
    120.00,
    'images/espresso.png',
    'available'
),

(
    'Latte',
    'Coffee',
    'Smooth espresso blended with steamed milk.',
    140.00,
    'images/latte.png',
    'available'
),

(
    'White Mocha',
    'Coffee',
    'Creamy white chocolate mocha with espresso and milk.',
    155.00,
    'images/white-mocha.png',
    'available'
),

(
    'Milkshake',
    'Beverage',
    'Creamy and refreshing Cafelia milkshake.',
    150.00,
    'images/milkshake.png',
    'available'
);