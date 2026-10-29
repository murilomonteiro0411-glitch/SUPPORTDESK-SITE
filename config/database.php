
<?php

// CONEXÃO COM O BANCO DE DADOS
$host = 'localhost';
$db = 'support_desk';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    exit('Erro ao conectar com o banco de dados.');
}

// CONFIGURAÇÃO DO E-MAIL - RESEND
define(
    'RESEND_API_KEY',
    getenv('RESEND_API_KEY') ?: ''
);

// E-MAIL REMETENTE
define(
    'MAIL_FROM',
    'SupportDesk <onboarding@resend.dev>'
);

// ENDEREÇO DO SITE
define(
    'APP_PUBLIC_URL',
    'http://localhost/support-desk'
);
