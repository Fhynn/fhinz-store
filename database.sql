-- Database: fhinz_store
CREATE DATABASE IF NOT EXISTS fhinz_store;
USE fhinz_store;

-- Tabel users untuk admin dan customer
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel categories untuk kategori produk
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel products untuk daftar aplikasi premium
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    duration_days INT DEFAULT 30,
    image_url VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Tabel orders untuk pesanan
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_proof VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel order_items untuk detail item pesanan
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Tabel testimonials untuk testimoni customer
CREATE TABLE testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    review TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Insert default admin user
INSERT INTO users (username, email, password, full_name, role) VALUES 
('admin', 'admin@fhinzstore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');

-- Insert categories
INSERT INTO categories (name, description, icon) VALUES 
('Multimedia', 'Aplikasi editing foto, video, dan konten kreatif', 'fas fa-photo-video'),
('Streaming', 'Platform streaming musik dan video premium', 'fas fa-play-circle'),
('Social Media', 'Aplikasi media sosial premium', 'fas fa-share-alt'),
('Productivity', 'Aplikasi produktivitas dan bisnis', 'fas fa-briefcase'),
('Entertainment', 'Aplikasi hiburan dan gaming', 'fas fa-gamepad'),
('Services', 'Jasa dan layanan digital', 'fas fa-cogs');

-- Insert sample products
INSERT INTO products (category_id, name, description, price, duration_days, image_url) VALUES 
(1, 'Alight Motion Pro', 'Aplikasi editing video profesional dengan fitur lengkap', 15000, 30, 'assets/images/alight-motion.jpg'),
(1, 'Canva Pro', 'Design tool profesional dengan template premium', 20000, 30, 'assets/images/canva.jpg'),
(1, 'CapCut Pro', 'Video editor dengan fitur AI dan effect premium', 12000, 30, 'assets/images/capcut.jpg'),
(1, 'PicsArt Premium', 'Photo editor dengan filter dan effect premium', 18000, 30, 'assets/images/picsart.jpg'),
(2, 'Netflix Premium', 'Streaming platform dengan kualitas 4K', 45000, 30, 'assets/images/netflix.jpg'),
(2, 'Spotify Premium', 'Music streaming tanpa iklan', 25000, 30, 'assets/images/spotify.jpg'),
(2, 'YouTube Premium', 'YouTube tanpa iklan dan background play', 30000, 30, 'assets/images/youtube.jpg'),
(2, 'Apple Music', 'Platform musik premium dari Apple', 28000, 30, 'assets/images/apple-music.jpg'),
(2, 'Amazon Prime Video', 'Streaming video premium Amazon', 35000, 30, 'assets/images/prime-video.jpg'),
(2, 'iQIYI VIP', 'Platform streaming drama Asia premium', 22000, 30, 'assets/images/iqiyi.jpg'),
(2, 'VIU Premium', 'Streaming drama Korea dan Asia', 20000, 30, 'assets/images/viu.jpg'),
(3, 'Instagram Premium Features', 'Fitur premium untuk Instagram', 15000, 30, 'assets/images/instagram.jpg'),
(3, 'TikTok Premium', 'TikTok dengan fitur premium', 18000, 30, 'assets/images/tiktok.jpg'),
(3, 'Discord Nitro', 'Discord dengan fitur premium', 25000, 30, 'assets/images/discord.jpg'),
(4, 'ChatGPT Plus', 'AI assistant premium', 60000, 30, 'assets/images/chatgpt.jpg'),
(4, 'GetContact Premium', 'Caller ID premium', 12000, 30, 'assets/images/getcontact.jpg'),
(5, 'BStation Premium', 'Platform streaming game premium', 20000, 30, 'assets/images/bstation.jpg'),
(5, 'LokLok VIP', 'Streaming movie premium', 15000, 30, 'assets/images/loklok.jpg'),
(6, 'Jasa Bikin Website', 'Pembuatan website profesional', 500000, 365, 'assets/images/web-service.jpg'),
(6, 'Jasa Pembuatan NPWP Online', 'Layanan pembuatan NPWP online', 75000, 7, 'assets/images/npwp-service.jpg');