<?php
require_once __DIR__ . '/session.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : '' ?>Support Desk</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $basePath ?? '' ?>assets/css/style.css">
</head>
<body>
<div class="background-grid"></div>
<nav class="navbar">
    <div class="container nav-content">
        <a class="logo" href="<?= $basePath ?? '' ?>index.php">
            <span class="logo-icon">S</span>
            <span>Support<span>Desk</span></span>
        </a>
        <button class="menu-toggle" aria-label="Abrir menu">☰</button>
        <div class="nav-links">
            <a href="<?= $basePath ?? '' ?>index.php#inicio">Início</a>
            <a href="<?= $basePath ?? '' ?>index.php#servicos">Serviços</a>
            <a href="<?= $basePath ?? '' ?>index.php#como-funciona">Como funciona</a>
            <a href="<?= $basePath ?? '' ?>index.php#contato">Contato</a>
            <?php if (isLoggedIn()): ?>
                <a class="btn btn-outline btn-small" href="<?= $basePath ?? '' ?><?= isAdmin() ? 'admin/dashboard.php' : 'user/dashboard.php' ?>">Painel</a>
                <a class="btn btn-primary btn-small" href="<?= $basePath ?? '' ?>auth/logout.php">Sair</a>
            <?php else: ?>
                <a href="<?= $basePath ?? '' ?>auth/login.php">Entrar</a>
                <a class="btn btn-primary btn-small" href="<?= $basePath ?? '' ?>auth/register.php">Criar conta</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
