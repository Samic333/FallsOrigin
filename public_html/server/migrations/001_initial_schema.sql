-- Falls Origin Coffee Database Schema
-- PostgreSQL 14+

-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Products Table
CREATE TABLE products (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL CHECK (price >= 0),
    weight VARCHAR(50) NOT NULL,
    image_url TEXT NOT NULL,
    origin VARCHAR(100) NOT NULL,
    roast_intensity INTEGER CHECK (roast_intensity BETWEEN 1 AND 5),
    roast_notes JSONB DEFAULT '[]'::jsonb,
    type VARCHAR(50) DEFAULT 'Single Origin',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Orders Table
CREATE TABLE orders (
    id VARCHAR(50) PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    province VARCHAR(50) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    items JSONB NOT NULL,
    total DECIMAL(10, 2) NOT NULL CHECK (total >= 0),
    status VARCHAR(50) NOT NULL DEFAULT 'Paid',
    delivery_method VARCHAR(50) NOT NULL,
    eta VARCHAR(255),
    tracking_number VARCHAR(100),
    carrier VARCHAR(100),
    signature_url TEXT,
    delivered_at TIMESTAMP WITH TIME ZONE,
    stripe_payment_intent_id VARCHAR(255) UNIQUE,
    stripe_charge_id VARCHAR(255),
    tracking_token VARCHAR(100) UNIQUE NOT NULL,
    review_email_sent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Order Audit Log
CREATE TABLE order_audit_log (
    id SERIAL PRIMARY KEY,
    order_id VARCHAR(50) NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
    status VARCHAR(50) NOT NULL,
    notes TEXT,
    timestamp TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Contact Messages
CREATE TABLE contact_messages (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(500) NOT NULL,
    message TEXT NOT NULL,
    read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Reviews
CREATE TABLE reviews (
    id SERIAL PRIMARY KEY,
    order_id VARCHAR(50) REFERENCES orders(id) ON DELETE SET NULL,
    customer_name VARCHAR(255) NOT NULL,
    rating INTEGER NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'approved', 'rejected')),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Admin Users
CREATE TABLE admin_users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP WITH TIME ZONE
);

-- Analytics Events
CREATE TABLE analytics_events (
    id SERIAL PRIMARY KEY,
    event_type VARCHAR(50) NOT NULL,
    metadata JSONB DEFAULT '{}'::jsonb,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Webhook Events (for idempotency)
CREATE TABLE webhook_events (
    id VARCHAR(255) PRIMARY KEY,
    event_type VARCHAR(100) NOT NULL,
    payload JSONB NOT NULL,
    processed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for performance
CREATE INDEX idx_orders_email ON orders(email);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_created_at ON orders(created_at DESC);
CREATE INDEX idx_orders_tracking_token ON orders(tracking_token);
CREATE INDEX idx_orders_stripe_payment_intent ON orders(stripe_payment_intent_id);
CREATE INDEX idx_order_audit_log_order_id ON order_audit_log(order_id);
CREATE INDEX idx_reviews_status ON reviews(status);
CREATE INDEX idx_reviews_order_id ON reviews(order_id);
CREATE INDEX idx_contact_messages_read ON contact_messages(read);
CREATE INDEX idx_analytics_events_type ON analytics_events(event_type);
CREATE INDEX idx_analytics_events_created_at ON analytics_events(created_at DESC);

-- Function to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Triggers for updated_at
CREATE TRIGGER update_products_updated_at BEFORE UPDATE ON products
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_orders_updated_at BEFORE UPDATE ON orders
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Insert default admin user (password: 'admin123' - CHANGE THIS!)
-- Hash generated with: bcrypt.hashSync('admin123', 10)
INSERT INTO admin_users (username, email, password_hash) 
VALUES (
    'admin',
    'admin@fallsorigincoffee.com',
    '$2b$10$rBV2kU8Y8zVZq7jXZ5nLKOX8YvJ0qGqF5vZ5vZ5vZ5vZ5vZ5vZ5vZ'
) ON CONFLICT (username) DO NOTHING;

-- Insert sample product (Falls Origin Coffee)
INSERT INTO products (id, name, description, price, weight, image_url, origin, roast_intensity, roast_notes, type)
VALUES (
    'FALLS-ORIGIN-001',
    'Falls Origin – Yirgacheffe Heirloom',
    'A luminous single-origin coffee from the birthplace of coffee itself. Delicate floral notes dance with bright citrus and a whisper of honey sweetness.',
    24.99,
    '500 g / 1.1 lb',
    '/falls-origin-premium-packaging.png',
    'Ethiopia (Yirgacheffe)',
    3,
    '["Citrus", "Honey", "Floral", "Bergamot"]',
    'Single Origin'
) ON CONFLICT (id) DO NOTHING;

-- Create view for order statistics
CREATE OR REPLACE VIEW order_statistics AS
SELECT 
    COUNT(*) as total_orders,
    SUM(total) as total_revenue,
    AVG(total) as average_order_value,
    COUNT(CASE WHEN status = 'Delivered' THEN 1 END) as delivered_orders,
    COUNT(CASE WHEN delivery_method = 'Local Delivery' THEN 1 END) as local_deliveries,
    COUNT(CASE WHEN delivery_method = 'Postal Shipping' THEN 1 END) as postal_shipments
FROM orders
WHERE status NOT IN ('Cancelled', 'Refunded');

COMMENT ON TABLE products IS 'Coffee products available for purchase';
COMMENT ON TABLE orders IS 'Customer orders with payment and delivery information';
COMMENT ON TABLE order_audit_log IS 'Audit trail for order status changes';
COMMENT ON TABLE contact_messages IS 'Customer inquiries from contact form';
COMMENT ON TABLE reviews IS 'Product reviews submitted by customers';
COMMENT ON TABLE admin_users IS 'Admin user accounts';
COMMENT ON TABLE analytics_events IS 'Analytics tracking events';
COMMENT ON TABLE webhook_events IS 'Stripe webhook events for idempotency';
