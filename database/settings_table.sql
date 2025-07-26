-- Create settings table
CREATE TABLE IF NOT EXISTS `settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `site_name` varchar(255) NOT NULL DEFAULT 'Fhinz Store',
    `site_description` text,
    `site_logo` varchar(255) DEFAULT NULL,
    `contact_email` varchar(255) NOT NULL,
    `contact_phone` varchar(50) DEFAULT NULL,
    `address` text,
    `facebook` varchar(255) DEFAULT NULL,
    `instagram` varchar(255) DEFAULT NULL,
    `whatsapp` varchar(50) DEFAULT NULL,
    `theme_color` varchar(20) DEFAULT 'blue',
    `language` varchar(5) DEFAULT 'id',
    `maintenance_mode` tinyint(1) DEFAULT 0,
    `enable_registration` tinyint(1) DEFAULT 1,
    `admin_password_hash` varchar(255) DEFAULT NULL,
    `session_timeout` int(11) DEFAULT 30,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO `settings` (`id`, `site_name`, `site_description`, `contact_email`, `contact_phone`, `address`, `facebook`, `instagram`, `whatsapp`, `theme_color`, `language`, `maintenance_mode`, `enable_registration`, `session_timeout`) VALUES
(1, 'Fhinz Store', 'Toko aplikasi premium terpercaya dengan berbagai pilihan aplikasi berkualitas', 'admin@fhinzstore.com', '+62 812-3456-7890', 'Jakarta, Indonesia', 'https://facebook.com/fhinzstore', 'https://instagram.com/fhinzstore', '+62 812-3456-7890', 'blue', 'id', 0, 1, 30);

-- Create uploads directory structure
-- Make sure to create these directories in your file system:
-- uploads/settings/
-- uploads/payment-proofs/
-- uploads/products/
-- uploads/categories/
