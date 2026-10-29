-- Instalação LIMPA: cria tabelas se ainda não existirem. Não apaga dados.
CREATE DATABASE IF NOT EXISTS support_desk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE support_desk;
CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(120) NOT NULL,email VARCHAR(160) NOT NULL UNIQUE,password VARCHAR(255) NOT NULL,role ENUM('user','admin') NOT NULL DEFAULT 'user',is_blocked TINYINT(1) NOT NULL DEFAULT 0,active_plan VARCHAR(80) NULL,plan_expires_at DATETIME NULL,avatar_file VARCHAR(100) NULL DEFAULT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS tickets (id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,assigned_admin_id INT NULL,subject VARCHAR(180) NOT NULL,category VARCHAR(80) NOT NULL,priority ENUM('Baixa','Média','Alta','Urgente') NOT NULL DEFAULT 'Média',description TEXT NOT NULL,status ENUM('Aberto','Em andamento','Resolvido','Fechado') NOT NULL DEFAULT 'Aberto',admin_seen_at DATETIME NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,FOREIGN KEY (assigned_admin_id) REFERENCES users(id) ON DELETE SET NULL) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS ticket_messages (id INT AUTO_INCREMENT PRIMARY KEY,ticket_id INT NOT NULL,user_id INT NOT NULL,message TEXT NOT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS subscriptions (id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,plan_name VARCHAR(80) NOT NULL,amount DECIMAL(10,2) NOT NULL,status ENUM('active','expired','cancelled') DEFAULT 'active',starts_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,expires_at DATETIME NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS payments (id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,subscription_id INT NULL,plan_name VARCHAR(80) NOT NULL,amount DECIMAL(10,2) NOT NULL,method ENUM('Cartão','PIX') NOT NULL,status ENUM('Aprovado','Pendente','Recusado') NOT NULL DEFAULT 'Aprovado',last_four VARCHAR(4) NULL,transaction_code VARCHAR(40) NOT NULL UNIQUE,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE SET NULL) ENGINE=InnoDB;
INSERT INTO users(name,email,password,role) VALUES ('Administrador','admin@supportdesk.com','$2y$12$G1IBqKkWpA1S5vb5jBlASevSubU4XyKBZrwqa5qtB.3CxHnBuph86','admin') ON DUPLICATE KEY UPDATE role='admin',password=VALUES(password);

-- Atualização visual e permissões V5 (projeto reformulado)
-- Execute DEPOIS de database/instalar.sql (instalação nova) ou database/atualizar.sql (legado).
-- Faça backup antes. Não apaga usuários, chamados ou pagamentos.
CREATE TABLE IF NOT EXISTS site_settings (setting_key VARCHAR(60) PRIMARY KEY, setting_value TEXT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT IGNORE INTO site_settings(setting_key,setting_value) VALUES ('site_name','SupportDesk'),('hero_title','Suporte eficiente. Tudo sob controle.'),('hero_text','Central profissional de chamados com atendimento organizado e acompanhamento transparente.'),('accent','#1677ff'),('announcement','');

-- V9: central de notificações
-- Execute uma vez no banco support_desk, depois de fazer backup.
CREATE TABLE IF NOT EXISTS notifications (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NOT NULL,
 title VARCHAR(160) NOT NULL,
 body TEXT NOT NULL,
 link_url VARCHAR(255) NOT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 read_at DATETIME NULL,
 INDEX ix_user_unread(user_id,read_at),
 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- V16: tokens de recuperação de senha (não apaga dados existentes)
CREATE TABLE IF NOT EXISTS password_reset_tokens (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT NOT NULL,
 token_hash CHAR(64) NOT NULL UNIQUE,
 expires_at DATETIME NOT NULL,
 used_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_reset_user (user_id, created_at),
 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
