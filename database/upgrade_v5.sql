-- Atualização visual e permissões V5 (projeto reformulado)
-- Execute DEPOIS de database/instalar.sql (instalação nova) ou database/atualizar.sql (legado).
-- Faça backup antes. Não apaga usuários, chamados ou pagamentos.
USE support_desk;
ALTER TABLE users ADD COLUMN IF NOT EXISTS is_blocked TINYINT(1) NOT NULL DEFAULT 0;
CREATE TABLE IF NOT EXISTS site_settings (setting_key VARCHAR(60) PRIMARY KEY, setting_value TEXT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT IGNORE INTO site_settings(setting_key,setting_value) VALUES ('site_name','SupportDesk'),('hero_title','Suporte eficiente. Tudo sob controle.'),('hero_text','Central profissional de chamados com atendimento organizado e acompanhamento transparente.'),('accent','#1677ff'),('announcement','');
