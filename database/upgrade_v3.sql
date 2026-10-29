ALTER TABLE users ADD COLUMN IF NOT EXISTS active_plan VARCHAR(80) NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS plan_expires_at DATETIME NULL;

CREATE TABLE IF NOT EXISTS subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_name VARCHAR(80) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('active','expired','cancelled') NOT NULL DEFAULT 'active',
    starts_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_subscription_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subscription_id INT NULL,
    plan_name VARCHAR(80) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    method ENUM('Cartão','PIX') NOT NULL,
    status ENUM('Aprovado','Pendente','Recusado') NOT NULL DEFAULT 'Aprovado',
    last_four VARCHAR(4) NULL,
    transaction_code VARCHAR(40) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payment_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_payment_subscription FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE SET NULL
);
