<?php
// Alterar somente se seu MySQL usar credenciais diferentes.
define('DB_HOST','127.0.0.1');define('DB_NAME','support_desk');define('DB_USER','root');define('DB_PASS','');

// Recuperação por e-mail em ambiente LOCAL (XAMPP).
// Não inclua a chave diretamente nos arquivos públicos. Configure no Apache:
// SetEnv RESEND_API_KEY "SUA_CHAVE_NOVA"
define('RESEND_API_KEY', getenv('RESEND_API_KEY') ?: '');
define('MAIL_FROM', 'SupportDesk <onboarding@resend.dev>');
define('APP_PUBLIC_URL', 'http://localhost/support-desk');
