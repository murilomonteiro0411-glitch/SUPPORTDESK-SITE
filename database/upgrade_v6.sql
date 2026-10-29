-- Execute após upgrade_v5.sql. Não apaga dados existentes.
USE support_desk;
CREATE TABLE IF NOT EXISTS pix_orders (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NOT NULL,
 provider_id VARCHAR(40) NOT NULL UNIQUE,
 reference VARCHAR(60) NOT NULL UNIQUE,
 plan_name VARCHAR(80) NOT NULL,
 amount DECIMAL(10,2) NOT NULL,
 status VARCHAR(30) NOT NULL DEFAULT 'pending',
 pix_code TEXT NOT NULL,
 qr_base64 MEDIUMTEXT NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
 INDEX idx_user_status (user_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
