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
    sales_count INT DEFAULT 0,
    low_stock_threshold INT DEFAULT 5,
    supplier_id INT NULL,
    warranty_period VARCHAR(50),
    return_policy TEXT,
    FOREIGN KEY (category_id) REFERENCES product_categories(category_id)
);

-- Create indexes for products
CREATE INDEX idx_products_name ON products(name);
CREATE INDEX idx_products_price ON products(price);
CREATE INDEX idx_products_category ON products(category_id);

-- Product Images Table (multiple images per product)
CREATE TABLE product_images (
    image_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 0,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- Add the roles table
CREATE TABLE roles (
    role_id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL,
    description TEXT
);

-- Insert roles (modified to remove specified roles)
INSERT INTO roles (role_name, description) VALUES
('admin', 'Administrator with full access to the system'),
('client', 'Regular customer'),
('vendeur', 'Salesperson who assists customers and processes sales'),
('gestionnaire_stock', 'Stock Manager who maintains inventory'),
('responsable_financier', 'Finance Manager who handles transactions and finances');

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
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    role_id INT DEFAULT 2,
    FOREIGN KEY (role_id) REFERENCES roles(role_id)
);

CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role_id);

-- User Sessions Table for security
CREATE TABLE user_sessions (
    session_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
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

-- Create suppliers table
CREATE TABLE suppliers (
    supplier_id INT PRIMARY KEY AUTO_INCREMENT,
    company_name VARCHAR(100) NOT NULL,
    contact_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone_number VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add foreign key constraint for suppliers in products table
ALTER TABLE products ADD CONSTRAINT fk_product_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id);

-- Shipping Information Table
CREATE TABLE shipping_addresses (
    address_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    recipient_name VARCHAR(100) NOT NULL,
    street_address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20),
    country VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Create Coupons and Discounts Table
CREATE TABLE coupons (
    coupon_id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    discount_type ENUM('percentage', 'fixed_amount') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    minimum_purchase DECIMAL(10,2) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    valid_from DATE NOT NULL,
    valid_to DATE NOT NULL,
    usage_limit INT DEFAULT 0,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cart Table
CREATE TABLE cart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    product_id INT,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_status ENUM('pending', 'paid') DEFAULT 'pending',
    payment_date DATETIME NULL,
    payment_verified_by INT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (payment_verified_by) REFERENCES users(user_id)
);

CREATE INDEX idx_cart_user ON cart(user_id);
CREATE INDEX idx_cart_payment ON cart(payment_status);

-- Orders Table (expanded)
CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    total_amount DECIMAL(10,2) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50) DEFAULT 'Not specified',
    transaction_id VARCHAR(100) DEFAULT NULL,
    payment_status ENUM('pending', 'partial', 'completed', 'failed') DEFAULT 'pending',
    verified BOOLEAN DEFAULT FALSE,
    verified_by INT NULL,
    verification_date DATETIME NULL,
    shipping_address_id INT,
    shipping_method VARCHAR(50) DEFAULT 'Standard',
    shipping_cost DECIMAL(10,2) DEFAULT 0,
    estimated_delivery_date DATE,
    coupon_id INT,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (verified_by) REFERENCES users(user_id),
    FOREIGN KEY (shipping_address_id) REFERENCES shipping_addresses(address_id),
    FOREIGN KEY (coupon_id) REFERENCES coupons(coupon_id)
);

CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_date ON orders(order_date);
CREATE INDEX idx_orders_status ON orders(status);

-- Order Items Table
CREATE TABLE order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price_at_time DECIMAL(10,2) NOT NULL,
    payment_status ENUM('pending', 'paid') DEFAULT 'pending',
    payment_date DATETIME NULL,
    payment_verified_by INT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (payment_verified_by) REFERENCES users(user_id)
);

CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_order_items_product ON order_items(product_id);
CREATE INDEX idx_order_items_payment ON order_items(payment_status);

-- Create delivery table
CREATE TABLE deliveries (
    delivery_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    delivery_person_id INT,
    status ENUM('pending', 'in_transit', 'delivered', 'failed') DEFAULT 'pending',
    scheduled_date DATETIME,
    actual_delivery_date DATETIME,
    notes TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (delivery_person_id) REFERENCES users(user_id)
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

-- Create financial transactions table
CREATE TABLE financial_transactions (
    transaction_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NULL,
    cart_id INT NULL,
    product_id INT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    transaction_type ENUM('payment', 'refund', 'adjustment') DEFAULT 'payment',
    payment_method VARCHAR(50),
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    verified_by INT NULL,
    verification_date DATETIME NULL,
    notes TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (cart_id) REFERENCES cart(cart_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (verified_by) REFERENCES users(user_id)
);

-- Create Product Inventory History table to track stock changes
CREATE TABLE product_inventory_history (
    history_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    previous_quantity INT NOT NULL,
    new_quantity INT NOT NULL,
    change_type ENUM('purchase', 'sale', 'adjustment', 'return') NOT NULL,
    reference_id INT COMMENT 'Order ID or other reference',
    changed_by INT NOT NULL,
    change_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (changed_by) REFERENCES users(user_id)
);

-- Create customer support tickets table
CREATE TABLE support_tickets (
    ticket_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    order_id INT NULL,
    subject VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    resolved_at DATETIME,
    assigned_to INT,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (assigned_to) REFERENCES users(user_id)
);

-- Create Product Reviews Table
CREATE TABLE product_reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT,
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_verified_purchase BOOLEAN DEFAULT FALSE,
    is_approved BOOLEAN DEFAULT TRUE,
    helpful_votes INT DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Create Wishlist Table
CREATE TABLE wishlists (
    wishlist_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE wishlist_items (
    wishlist_item_id INT PRIMARY KEY AUTO_INCREMENT,
    wishlist_id INT NOT NULL,
    product_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (wishlist_id) REFERENCES wishlists(wishlist_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    UNIQUE KEY unique_wishlist_product (wishlist_id, product_id)
);

-- Add notifications table
CREATE TABLE notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Create a payment verification table to track individual item payments
CREATE TABLE payment_verifications (
    verification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    cart_id INT NULL,
    order_item_id INT NULL,
    product_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    payment_reference VARCHAR(100),
    verified_by INT NOT NULL,
    verification_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (cart_id) REFERENCES cart(cart_id),
    FOREIGN KEY (order_item_id) REFERENCES order_items(order_item_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (verified_by) REFERENCES users(user_id)
);

-- Create Triggers for inventory management
DELIMITER //

-- Update product sales_count and stock when order is placed
CREATE TRIGGER after_order_item_insert
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    DECLARE old_quantity INT;
    
    -- Get the current stock quantity
    SELECT stock_quantity INTO old_quantity FROM products WHERE product_id = NEW.product_id;
    
    -- Update the product stock and sales count
    UPDATE products 
    SET stock_quantity = stock_quantity - NEW.quantity,
        sales_count = sales_count + NEW.quantity
    WHERE product_id = NEW.product_id;
    
    -- Record the inventory change
    INSERT INTO product_inventory_history (
        product_id, 
        previous_quantity, 
        new_quantity, 
        change_type, 
        reference_id, 
        changed_by, 
        notes
    ) VALUES (
        NEW.product_id,
        old_quantity,
        old_quantity - NEW.quantity,
        'sale',
        NEW.order_id,
        (SELECT user_id FROM orders WHERE order_id = NEW.order_id),
        CONCAT('Order #', NEW.order_id)
    );
    
    -- Check if stock falls below threshold and create notification
    IF ((old_quantity - NEW.quantity) <= (SELECT low_stock_threshold FROM products WHERE product_id = NEW.product_id)) THEN
        -- Notify stock managers (role_id 4)
        INSERT INTO notifications (user_id, title, message)
        SELECT user_id, 
               CONCAT('Low Stock Alert: ', (SELECT name FROM products WHERE product_id = NEW.product_id)),
               CONCAT('Product ID #', NEW.product_id, ' stock is low. Current quantity: ', (old_quantity - NEW.quantity))
        FROM users WHERE role_id = 4;
    END IF;
END //

-- Trigger for when an order is cancelled to restore stock
CREATE TRIGGER after_order_status_update
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF NEW.status = 'cancelled' AND OLD.status != 'cancelled' THEN
        -- For each order item, restore stock
        UPDATE products p
        JOIN order_items oi ON p.product_id = oi.product_id
        SET p.stock_quantity = p.stock_quantity + oi.quantity,
            p.sales_count = p.sales_count - oi.quantity
        WHERE oi.order_id = NEW.order_id;
        
        -- Log each inventory change
        INSERT INTO product_inventory_history (
            product_id,
            previous_quantity,
            new_quantity,
            change_type,
            reference_id,
            changed_by,
            notes
        )
        SELECT 
            oi.product_id,
            p.stock_quantity - oi.quantity,
            p.stock_quantity,
            'return',
            NEW.order_id,
            IFNULL(NEW.verified_by, NEW.user_id),
            CONCAT('Order #', NEW.order_id, ' cancelled')
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = NEW.order_id;
    END IF;
END //

-- Create a trigger for automatic wishlist to cart transfer
CREATE TRIGGER after_wishlist_item_update
AFTER UPDATE ON products
FOR EACH ROW
BEGIN
    -- If product was out of stock and now is in stock, notify users who wishlisted it
    IF OLD.stock_quantity <= 0 AND NEW.stock_quantity > 0 THEN
        INSERT INTO notifications (user_id, title, message)
        SELECT w.user_id, 
               CONCAT('Wishlist item back in stock: ', NEW.name),
               CONCAT('Good news! An item on your wishlist is now back in stock: ', NEW.name)
        FROM wishlist_items wi
        JOIN wishlists w ON wi.wishlist_id = w.wishlist_id
        WHERE wi.product_id = NEW.product_id;
    END IF;
END //

-- Create a trigger for auto-generating first wishlist
CREATE TRIGGER after_user_insert
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    -- Create a default wishlist for new users
    IF NEW.role_id = 2 THEN -- Only for clients
        INSERT INTO wishlists (user_id, created_at)
        VALUES (NEW.user_id, NOW());
    END IF;
END //

-- Trigger to handle payment verification for cart items
DELIMITER //
DROP TRIGGER IF EXISTS after_cart_payment_update //
CREATE TRIGGER after_cart_payment_update
AFTER UPDATE ON cart
FOR EACH ROW
BEGIN
    DECLARE old_quantity INT;
    
    -- If payment status changed from pending to paid
    IF NEW.payment_status = 'paid' AND OLD.payment_status = 'pending' THEN
        -- Get the current stock quantity
        SELECT stock_quantity INTO old_quantity FROM products WHERE product_id = NEW.product_id;
        
        -- Record the payment verification
        INSERT INTO payment_verifications (
            user_id,
            cart_id,
            product_id,
            amount,
            verified_by,
            notes
        ) VALUES (
            NEW.user_id,
            NEW.cart_id,
            NEW.product_id,
            (SELECT price * NEW.quantity FROM products WHERE product_id = NEW.product_id),
            NEW.payment_verified_by,
            CONCAT('Cart payment verification for product ID #', NEW.product_id)
        );

        -- Create a financial transaction record
        INSERT INTO financial_transactions (
            cart_id,
            product_id,
            user_id,
            amount,
            transaction_type,
            status,
            verified_by,
            verification_date,
            notes
        ) VALUES (
            NEW.cart_id,
            NEW.product_id,
            NEW.user_id,
            (SELECT price * NEW.quantity FROM products WHERE product_id = NEW.product_id),
            'payment',
            'completed',
            NEW.payment_verified_by,
            NOW(),
            CONCAT('Payment for cart item #', NEW.cart_id)
        );

        -- Update product stock quantity (reduce from inventory)
        UPDATE products 
        SET stock_quantity = stock_quantity - NEW.quantity,
            sales_count = sales_count + NEW.quantity
        WHERE product_id = NEW.product_id;
        
        -- Record inventory change
        INSERT INTO product_inventory_history (
            product_id,
            previous_quantity,
            new_quantity,
            change_type,
            reference_id,
            changed_by,
            notes
        ) VALUES (
            NEW.product_id,
            old_quantity,
            old_quantity - NEW.quantity,
            'sale',
            NEW.cart_id,
            NEW.payment_verified_by,
            CONCAT('Direct cart payment for product ID #', NEW.product_id)
        );

        -- Notify the user about the successful payment
        INSERT INTO notifications (
            user_id,
            title,
            message
        ) VALUES (
            NEW.user_id,
            'Payment Successful',
            CONCAT('Your payment for ', (SELECT name FROM products WHERE product_id = NEW.product_id), ' has been verified and processed.')
        );
        
        -- Signal that this cart item needs deletion
        -- We'll handle the actual deletion with an event or separate process
        INSERT INTO cart_items_to_delete (cart_id) VALUES (NEW.cart_id);
    END IF;
END //
DELIMITER ;

-- Trigger to handle payment verification for order items
DELIMITER //
CREATE TRIGGER after_order_item_payment_update
AFTER UPDATE ON order_items
FOR EACH ROW
BEGIN
    -- If payment status changed from pending to paid
    IF NEW.payment_status = 'paid' AND OLD.payment_status = 'pending' THEN
        -- Record the payment verification
        INSERT INTO payment_verifications (
            user_id,
            order_item_id,
            product_id,
            amount,
            verified_by,
            notes
        ) VALUES (
            (SELECT user_id FROM orders WHERE order_id = NEW.order_id),
            NEW.order_item_id,
            NEW.product_id,
            NEW.price_at_time * NEW.quantity,
            NEW.payment_verified_by,
            CONCAT('Order item payment verification for product ID #', NEW.product_id)
        );
        
        -- Create a financial transaction record
        INSERT INTO financial_transactions (
            order_id,
            product_id,
            user_id,
            amount,
            transaction_type,
            status,
            verified_by,
            verification_date,
            notes
        ) VALUES (
            NEW.order_id,
            NEW.product_id,
            (SELECT user_id FROM orders WHERE order_id = NEW.order_id),
            NEW.price_at_time * NEW.quantity,
            'payment',
            'completed',
            NEW.payment_verified_by,
            NOW(),
            CONCAT('Payment for order item #', NEW.order_item_id)
        );
        
        -- Check if all items in the order are paid to update the order status
        IF NOT EXISTS (
            SELECT 1 FROM order_items 
            WHERE order_id = NEW.order_id AND payment_status = 'pending'
        ) THEN
            UPDATE orders 
            SET payment_status = 'completed',
                verified = TRUE,
                verified_by = NEW.payment_verified_by,
                verification_date = NOW()
            WHERE order_id = NEW.order_id;
        ELSE
            UPDATE orders 
            SET payment_status = 'partial'
            WHERE order_id = NEW.order_id;
        END IF;
        
        -- Notify the user about the successful payment
        INSERT INTO notifications (
            user_id,
            title,
            message
        ) VALUES (
            (SELECT user_id FROM orders WHERE order_id = NEW.order_id),
            'Order Item Payment Successful',
            CONCAT('Your payment for order #', NEW.order_id, ', item: ', (SELECT name FROM products WHERE product_id = NEW.product_id), ' has been verified and processed.')
        );
    END IF;
END //
DELIMITER ;


-- Create an event to delete cart items older than 3 hours
DELIMITER //
CREATE EVENT delete_abandoned_carts
ON SCHEDULE EVERY 15 MINUTE
DO
BEGIN
    DELETE FROM cart WHERE added_at < DATE_SUB(NOW(), INTERVAL 3 HOUR) AND payment_status = 'pending';
END//
DELIMITER ;
-- Create table to track cart items that need deletion
CREATE TABLE IF NOT EXISTS cart_items_to_delete (
    delete_id INT PRIMARY KEY AUTO_INCREMENT,
    cart_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cart_id) REFERENCES cart(cart_id) ON DELETE CASCADE
);

-- Create an event to process the deletions
DELIMITER //
CREATE EVENT IF NOT EXISTS process_cart_deletions
ON SCHEDULE EVERY 1 MINUTE
DO
BEGIN
    -- Delete the actual cart items
    DELETE FROM cart 
    WHERE cart_id IN (SELECT cart_id FROM cart_items_to_delete);
    
    -- Clean up the tracking table
    DELETE FROM cart_items_to_delete;
END//
DELIMITER ;
-- Make sure the event scheduler is running
SET GLOBAL event_scheduler = ON;

-- Insert Admin User (Password: Graciela@1)
INSERT INTO admins (first_name, last_name, email, password_hash) VALUES
('Grace', 'Admin', 'grace@gmail.com', '$2y$12$ZZQ1Kh8eLVq3GpwLSVJhfuEMGHV1ZHPQww1XLi1FKWCq/KAZSNyni');

-- Insert Product Categories
INSERT INTO product_categories (category_name, description) VALUES
('Laptops', 'Portable computers for professionals and personal use'),
('Gaming PCs', 'High-performance computers for gaming enthusiasts'),
('Workstations', 'Powerful computers for professional work'),
('Accessories', 'Computer peripherals and additional equipment');

-- Insert sample suppliers
INSERT INTO suppliers (company_name, contact_name, email, phone_number, address) VALUES
('HP Cameroon', 'Jean Pierre', 'contact@hp-cameroon.com', '+237612345678', 'Rue 1225, Douala'),
('Dell Technologies', 'Marie Claire', 'contact@dell-tech.com', '+237623456789', 'Avenue Central, Yaounde'),
('Lenovo Group', 'Paul Biya', 'contact@lenovo-group.com', '+237634567890', 'Boulevard 34, Limbe');

-- Insert sample users for each role (modified to remove specified users)
-- Note: All passwords are set to 'Password123!' with this hash
INSERT INTO users (first_name, last_name, email, phone_number, country, state, quarter, password_hash, role_id) VALUES
-- Administrator (role_id = 1)
('Admin', 'User', 'admin@techpro.com', '+2376512345', 'Cameroon', 'Centre', 'Yaounde', '$2y$12$6IPhTFWx/cx/TEMqK584O.Oe0yuCgnzFlU0yJZdeB//1CZEbenn3a', 1),

-- Client (role_id = 2)
('Client', 'User', 'client@example.com', '+2376534567', 'Cameroon', 'Littoral', 'Douala', '$2y$12$6IPhTFWx/cx/TEMqK584O.Oe0yuCgnzFlU0yJZdeB//1CZEbenn3a', 2),

-- Vendeur (role_id = 3)
('Vendeur', 'Sales', 'vendeur@techpro.com', '+2376545678', 'Cameroon', 'Centre', 'Yaounde', '$2y$12$6IPhTFWx/cx/TEMqK584O.Oe0yuCgnzFlU0yJZdeB//1CZEbenn3a', 3),

-- Gestionnaire Stock (role_id = 4)
('Stock', 'Manager', 'stock@techpro.com', '+2376556789', 'Cameroon', 'Centre', 'Yaounde', '$2y$12$6IPhTFWx/cx/TEMqK584O.Oe0yuCgnzFlU0yJZdeB//1CZEbenn3a', 4),

-- Responsable Financier (role_id = 5)
('Finance', 'Manager', 'finance@techpro.com', '+2376578901', 'Cameroon', 'Centre', 'Yaounde', '$2y$12$6IPhTFWx/cx/TEMqK584O.Oe0yuCgnzFlU0yJZdeB//1CZEbenn3a', 5);

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
    is_featured,
    supplier_id,
    warranty_period,
    return_policy
) VALUES 
(1, 'Laptop Pro X1', 
'Powerful laptop for professionals', 
'Intel i7 12th Gen, 16GB RAM DDR5, 512GB NVMe SSD, RTX 3060 6GB', 
45999.99, 
52999.99, 
'images/laptop1.jpeg', 
'Nouveau', 
50, 
TRUE,
1,
'12 months',
'30 days money back guarantee'),

(2, 'Gaming PC Elite', 
'High-end gaming desktop', 
'AMD Ryzen 9 7950X, 32GB RAM DDR5, 1TB NVMe SSD, RTX 4080 16GB', 
57999.99, 
59999.99, 
'images/gamingpc1.jpeg', 
'Populaire', 
30, 
TRUE,
2,
'24 months',
'14 days return policy with 10% restocking fee'),

(1, 'Ultrabook Air', 
'Lightweight and portable laptop', 
'Intel i5 12e Gén, 8 Go RAM DDR4, 256 Go NVMe SSD, Intel Iris Xe', 
32499.99, 
38999.99, 
'images/Ultrabook1.jpeg', 
'Promo -15%', 
40, 
TRUE,
1,
'12 months',
'30 days money back guarantee'),

(3, 'WorkStation Pro', 
'Professional workstation for heavy tasks', 
'Intel i9 13900K, 64 Go RAM DDR5, 2 To NVMe SSD, RTX 4090 24 Go', 
59499.99, 
62999.99, 
'images/WorkStation.jpeg', 
'Professionnel', 
20, 
TRUE,
2,
'36 months',
'14 days return for defective units only'),

(1, 'MacBook Pro M2', 
'Premium Apple laptop', 
'Apple M2 Pro, 16 Go RAM, 512 Go SSD, GPU 16 cœurs', 
56999.99, 
58999.99, 
'images/MacBook1.jpeg', 
'Premium', 
25, 
TRUE,
3,
'12 months',
'14 days return policy with receipt'),

(1, 'Mini PC Creator', 
'Compact yet powerful mini computer', 
'AMD Ryzen 5 7600X, 16 Go RAM DDR5, 500 Go NVMe SSD, RTX 3050 8 Go', 
29999.99, 
34999.99, 
'images/MiniPCCreator1.jpeg', 
'Compact', 
35, 
TRUE,
1,
'12 months',
'30 days money back guarantee'),

(4, 'Gaming Mouse Pro', 
'High-precision gaming mouse', 
'20000 DPI, 11 programmable buttons, RGB lighting, wireless', 
2999.99, 
3499.99, 
'images/gaming_mouse.jpeg', 
'Gaming', 
100, 
TRUE,
3,
'6 months',
'14 days return policy'),

(4, 'Mechanical Keyboard Elite', 
'Premium mechanical gaming keyboard', 
'RGB backlight, Cherry MX Blue switches, aluminum frame', 
4999.99, 
5799.99, 
'images/mechanical_keyboard.jpeg', 
'Popular', 
75, 
TRUE,
3,
'12 months',
'30 days return policy'),

(4, 'Ultra Wide Monitor 34"', 
'Immersive curved gaming monitor', 
'34" Ultra-wide, 3440x1440, 144Hz, 1ms response time, HDR10', 
24999.99, 
28999.99, 
'images/ultrawide_monitor.jpeg', 
'Bestseller', 
40, 
TRUE,
3,
'24 months',
'7 days DOA exchange only');

-- Migrate existing product images to the new table
INSERT INTO product_images (product_id, image_path, is_primary)
SELECT product_id, image_path, TRUE FROM products WHERE image_path IS NOT NULL;

-- Insert some sample coupons
INSERT INTO coupons (code, description, discount_type, discount_value, minimum_purchase, is_active, valid_from, valid_to, usage_limit) VALUES
('WELCOME10', 'Welcome discount for new customers', 'percentage', 10.00, 10000.00, TRUE, '2025-01-01', '2025-12-31', 1000),
('TECH25', 'Special 25% off on selected items', 'percentage', 25.00, 20000.00, TRUE, '2025-03-01', '2025-04-30', 500),
('FREESHIP', 'Free shipping on all orders', 'fixed_amount', 1500.00, 25000.00, TRUE, '2025-03-01', '2025-03-31', 200);

-- Insert sample shipping addresses
INSERT INTO shipping_addresses (user_id, recipient_name, street_address, city, state, postal_code, country, phone_number, is_default) VALUES
(3, 'Client User', 'Avenue de la Paix 123', 'Douala', 'Littoral', '12345', 'Cameroon', '+2376534567', TRUE);

-- Create sample wishlist (trigger should create initial wishlist, but this is a backup)
INSERT INTO wishlists (user_id, created_at)
SELECT user_id, NOW() FROM users WHERE role_id = 3 AND user_id NOT IN (SELECT user_id FROM wishlists);

-- Add some products to the sample wishlist
-- Add some products to the sample wishlist 
INSERT INTO wishlist_items (wishlist_id, product_id) 
SELECT 
    (SELECT wishlist_id FROM wishlists WHERE user_id = 3 LIMIT 1),
    product_id
FROM products
WHERE is_featured = TRUE;

-- Create sample reviews
INSERT INTO product_reviews (product_id, user_id, rating, review_text, is_verified_purchase) VALUES
(1, 3, 5, 'Excellent laptop for professional work. The performance is outstanding, and the battery life is impressive.', TRUE),
(2, 3, 4, 'Great gaming PC, runs all my games at max settings. Only issue is it runs a bit hot under heavy load.', TRUE),
(7, 3, 5, 'This gaming mouse is perfect! Responsive, comfortable, and the customizable buttons are very useful.', TRUE);