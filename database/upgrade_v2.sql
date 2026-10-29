USE support_desk;

-- Execute este arquivo UMA VEZ em um banco Support Desk já existente.
ALTER TABLE tickets
    ADD COLUMN assigned_admin_id INT NULL AFTER user_id,
    ADD COLUMN admin_seen_at DATETIME NULL AFTER status,
    ADD CONSTRAINT fk_ticket_assigned_admin FOREIGN KEY (assigned_admin_id) REFERENCES users(id) ON DELETE SET NULL;

-- A prioridade agora é definida somente pela equipe. Novos chamados começam como Média.
UPDATE tickets SET priority = 'Média' WHERE priority IS NULL OR priority = '';
