-- Suporte a foto de perfil. Execute UMA VEZ em banco existente.
ALTER TABLE users ADD COLUMN avatar_file VARCHAR(100) NULL DEFAULT NULL;
