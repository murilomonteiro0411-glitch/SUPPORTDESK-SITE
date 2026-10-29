CREATE DATABASE IF NOT EXISTS support_desk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE support_desk;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    assigned_admin_id INT NULL,
    subject VARCHAR(180) NOT NULL,
    category VARCHAR(80) NOT NULL,
    priority ENUM('Baixa','Média','Alta','Urgente') NOT NULL DEFAULT 'Média',
    description TEXT NOT NULL,
    status ENUM('Aberto','Em andamento','Resolvido','Fechado') NOT NULL DEFAULT 'Aberto',
    admin_seen_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ticket_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_ticket_assigned_admin FOREIGN KEY (assigned_admin_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE ticket_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_message_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    CONSTRAINT fk_message_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Senha do admin: password
INSERT INTO users (name, email, password, role) VALUES
('Administrador', 'admin@supportdesk.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'admin');
