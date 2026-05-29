-- 1. Użytkownicy i Uprawnienia
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password TEXT NOT NULL,
    full_name VARCHAR(100),
    avatar_url VARCHAR(255) DEFAULT 'https://upload.wikimedia.org/wikipedia/commons/9/99/Sample_User_Icon.png',
    vibe_points INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE user_roles (
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    role_id INT REFERENCES roles(id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, role_id)
);

-- 2. Pokoje i Rezerwacje
CREATE TABLE rooms (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    image_url VARCHAR(255),
    rating DECIMAL(2,1),
    hourly_rate DECIMAL(10, 2) NOT NULL,
    capacity INT NOT NULL,
    status VARCHAR(50) DEFAULT 'Available'
);

CREATE TABLE bookings (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    room_id INT REFERENCES rooms(id) ON DELETE CASCADE,
    start_time TIMESTAMP WITH TIME ZONE NOT NULL,
    end_time TIMESTAMP WITH TIME ZONE NOT NULL,
    total_price DECIMAL(10, 2),
    status VARCHAR(50) DEFAULT 'Active'
);

-- 3. Produkty i Zamówienia (Menu)
CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category VARCHAR(50) DEFAULT 'General',
    price DECIMAL(10, 2) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    image_url VARCHAR(255) DEFAULT 'https://i0.wp.com/www.drdavidludwig.com/wp-content/uploads/2017/01/1-RIS_6IbCLYv1X3bzYW1lmA.jpeg?fit=800%2C552&ssl=1'
);

CREATE TABLE orders (
    id SERIAL PRIMARY KEY,
    booking_id INT REFERENCES bookings(id) ON DELETE CASCADE,
    status VARCHAR(50) DEFAULT 'In Preparation',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE order_items (
    id SERIAL PRIMARY KEY,
    order_id INT REFERENCES orders(id) ON DELETE CASCADE,
    product_id INT REFERENCES products(id) ON DELETE CASCADE,
    quantity INT NOT NULL CHECK (quantity > 0),
    unit_price DECIMAL(10, 2) NOT NULL
);

-- 4. Funkcje i Triggery
CREATE OR REPLACE FUNCTION calculate_room_price(
    p_room_id INT,
    p_hours DECIMAL,
    p_discount_percent DECIMAL DEFAULT 0
)
RETURNS DECIMAL AS $$
DECLARE
v_hourly_rate DECIMAL;
    v_base_price DECIMAL;
    v_final_price DECIMAL;
BEGIN
SELECT hourly_rate INTO v_hourly_rate FROM rooms WHERE id = p_room_id;
v_base_price := v_hourly_rate * p_hours;
    v_final_price := v_base_price - (v_base_price * (p_discount_percent / 100));
RETURN v_final_price;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION set_room_needs_cleaning()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.status = 'Completed' AND OLD.status != 'Completed' THEN
UPDATE rooms SET status = 'Cleaning' WHERE id = NEW.room_id;
END IF;
RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_room_cleaning
    AFTER UPDATE ON bookings
    FOR EACH ROW
    EXECUTE FUNCTION set_room_needs_cleaning();

-- 5. Widoki (Views)
CREATE OR REPLACE VIEW top_ordered_products AS
SELECT p.id, p.name, SUM(oi.quantity) as total_ordered
FROM products p
         JOIN order_items oi ON p.id = oi.product_id
GROUP BY p.id, p.name
ORDER BY total_ordered DESC;

CREATE OR REPLACE VIEW occupied_rooms_status AS
SELECT r.name as room_name, u.username as client_name, b.end_time as session_ends_at, b.total_price
FROM bookings b
         JOIN rooms r ON b.room_id = r.id
         JOIN users u ON b.user_id = u.id
WHERE b.status = 'Active';

-- 6. Dane testowe
INSERT INTO roles (name) VALUES ('Client'), ('Manager');

INSERT INTO rooms (name, description, image_url, rating, hourly_rate, capacity, status) VALUES
    ('The Neon Vault', 'LED lights and luxury vibe', 'https://images.squarespace-cdn.com/content/v1/5c7d228a348cd9351adf113d/43281df9-3316-42c1-bf80-a9c312303150/IMG_7853.jpg', 4.9, 120.00, 8, 'Available'),
    ('Cyberpunk Den', 'Futuristic neon aesthetics', 'https://static01.nyt.com/images/2025/11/30/multimedia/11re-karaoke-rooms-01-fqhg/11re-karaoke-rooms-01-fqhg-articleLarge.jpg?quality=75&auto=webp&disable=upscale', 4.8, 85.00, 10, 'Available'),
    ('The Velvet Lounge', 'Cozy atmosphere for chilling', 'https://uk.bam-karaokebox.com/wp-content/uploads/2024/04/The-Peacock-Room-LR-1552x980.jpg', 4.7, 95.00, 8, 'Available'),
    ('Jungle Vibe', 'Tropical plants and relaxing sounds', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcROhRwKQ1HPCV6eph6uaRS3nAGIgMjY9kTQEw&s', 4.9, 60.00, 6, 'Available');

INSERT INTO products (name, description, category, price, stock_quantity) VALUES
    ('Vocal Vibe Cocktail', 'Our signature fruit blend', 'Drinks', 15.00, 100),
    ('Premium Craft Beer', 'Local IPA from the best brewery', 'Drinks', 9.00, 50),
    ('Nachos Supreme', 'With spicy cheese sauce', 'Snacks', 12.00, 30),
    ('Popcorn', 'Freshly popped, salted', 'Snacks', 5.00, 200);