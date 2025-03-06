-- Create Database
CREATE DATABASE techpro_ecommerce;
USE techpro_ecommerce;

-- Product Categories Table
CREATE TABLE product_categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL,
    description TEXT
);

-- Products Table
CREATE TABLE products (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    specifications TEXT,
    price DECIMAL(10,2) NOT NULL,
    original_price DECIMAL(10,2),
    image_path VARCHAR(255),
    badge VARCHAR(50),
    stock_quantity INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES product_categories(category_id)
);

-- Users Table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone_number VARCHAR(20),
    country VARCHAR(100),
    state VARCHAR(100),
    quarter VARCHAR(100),
    password_hash VARCHAR(255) NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Admins Table
CREATE TABLE admins (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cart Table
CREATE TABLE cart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    product_id INT,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- Orders Table
CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    total_amount DECIMAL(10,2) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Order Items Table
CREATE TABLE order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price_at_time DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- Add the roles table
CREATE TABLE roles (
    role_id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL,
    description TEXT
);

-- Create a sales table to track total sales
CREATE TABLE sales (
    sale_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    total_amount DECIMAL(10,2) NOT NULL,
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_method VARCHAR(50),
    transaction_id VARCHAR(100),
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
);

-- Insert default roles
INSERT INTO roles (role_name, description) VALUES
('admin', 'Administrator with full access to the system'),
('super_admin', 'Super Administrator with extended privileges'),
('client', 'Regular customer');

-- Add role_id to users table
ALTER TABLE users ADD COLUMN role_id INT DEFAULT 3;
ALTER TABLE users ADD CONSTRAINT fk_user_role FOREIGN KEY (role_id) REFERENCES roles(role_id);

-- Add a sales_count column to the products table to track how many times a product has been sold
ALTER TABLE products ADD COLUMN sales_count INT DEFAULT 0;

-- Add a payment_method column to the orders table
ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) DEFAULT 'Not specified';

-- Add payment transaction fields to the orders table
ALTER TABLE orders ADD COLUMN transaction_id VARCHAR(100) DEFAULT NULL;
ALTER TABLE orders ADD COLUMN payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending';

-- Ensure the added_at column exists and is properly set up
ALTER TABLE cart MODIFY COLUMN added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Insert Admin User (Password: Graciela@1)
INSERT INTO admins (first_name, last_name, email, password_hash) VALUES
('Grace', 'Admin', 'grace@gmail.com', '$2y$12$ZZQ1Kh8eLVq3GpwLSVJhfuEMGHV1ZHPQww1XLi1FKWCq/KAZSNyni');

-- Update existing admin to have admin role
UPDATE users SET role_id = 1 WHERE email = 'grace@gmail.com';

-- Insert Product Categories
INSERT INTO product_categories (category_name, description) VALUES
('Laptops', 'Portable computers for professionals and personal use'),
('Gaming PCs', 'High-performance computers for gaming enthusiasts'),
('Workstations', 'Powerful computers for professional work'),
('Accessories', 'Computer peripherals and additional equipment');

-- Insert Sample Products with realistic pricing below 60,000
INSERT INTO products (
    category_id, 
    name, 
    description, 
    specifications, 
    price, 
    original_price, 
    image_path, 
    badge, 
    stock_quantity, 
    is_featured
) VALUES 
(1, 'Laptop Pro X1', 
'Powerful laptop for professionals', 
'Intel i7 12th Gen, 16GB RAM DDR5, 512GB NVMe SSD, RTX 3060 6GB', 
45999.99, 
52999.99, 
'images/laptop1.jpeg', 
'Nouveau', 
50, 
TRUE),

(2, 'Gaming PC Elite', 
'High-end gaming desktop', 
'AMD Ryzen 9 7950X, 32GB RAM DDR5, 1TB NVMe SSD, RTX 4080 16GB', 
57999.99, 
59999.99, 
'images/gamingpc1.jpeg', 
'Populaire', 
30, 
TRUE),

(1, 'Ultrabook Air', 
'Lightweight and portable laptop', 
'Intel i5 12e Gén, 8 Go RAM DDR4, 256 Go NVMe SSD, Intel Iris Xe', 
32499.99, 
38999.99, 
'images/Ultrabook1.jpeg', 
'Promo -15%', 
40, 
TRUE),

(3, 'WorkStation Pro', 
'Professional workstation for heavy tasks', 
'Intel i9 13900K, 64 Go RAM DDR5, 2 To NVMe SSD, RTX 4090 24 Go', 
59499.99, 
62999.99, 
'images/WorkStation.jpeg', 
'Professionnel', 
20, 
TRUE),

(1, 'MacBook Pro M2', 
'Premium Apple laptop', 
'Apple M2 Pro, 16 Go RAM, 512 Go SSD, GPU 16 cœurs', 
56999.99, 
58999.99, 
'images/MacBook1.jpeg', 
'Premium', 
25, 
TRUE),

(1, 'Mini PC Creator', 
'Compact yet powerful mini computer', 
'AMD Ryzen 5 7600X, 16 Go RAM DDR5, 500 Go NVMe SSD, RTX 3050 8 Go', 
29999.99, 
34999.99, 
'images/MiniPCCreator1.jpeg', 
'Compact', 
35, 
TRUE),

(4, 'Gaming Mouse Pro', 
'High-precision gaming mouse', 
'20000 DPI, 11 programmable buttons, RGB lighting, wireless', 
2999.99, 
3499.99, 
'images/gaming_mouse.jpeg', 
'Gaming', 
100, 
TRUE),

(4, 'Mechanical Keyboard Elite', 
'Premium mechanical gaming keyboard', 
'RGB backlight, Cherry MX Blue switches, aluminum frame', 
4999.99, 
5799.99, 
'images/mechanical_keyboard.jpeg', 
'Popular', 
75, 
TRUE),

(4, 'Ultra Wide Monitor 34"', 
'Immersive curved gaming monitor', 
'34" Ultra-wide, 3440x1440, 144Hz, 1ms response time, HDR10', 
24999.99, 
28999.99, 
'images/ultrawide_monitor.jpeg', 
'Bestseller', 
40, 
TRUE);

-- Create an event to delete cart items older than 3 hours
DELIMITER //
CREATE EVENT delete_abandoned_carts
ON SCHEDULE EVERY 15 MINUTE
DO
BEGIN
    DELETE FROM cart WHERE added_at < DATE_SUB(NOW(), INTERVAL 3 HOUR);
END//
DELIMITER ;

-- Make sure the event scheduler is running
SET GLOBAL event_scheduler = ON;