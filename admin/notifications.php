<?php
require_once '../config/database.php';
require_once '../includes/session.php';
requireAdmin();
header('Content-Type: application/json; charset=utf-8');
$new = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE admin_seen_at IS NULL AND status='Aberto'")->fetchColumn();
$high = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE priority IN ('Alta','Urgente') AND status IN ('Aberto','Em andamento')")->fetchColumn();
$items = $pdo->query("SELECT id,subject,priority,status,created_at FROM tickets WHERE status IN ('Aberto','Em andamento') ORDER BY (priority='Urgente') DESC,(priority='Alta') DESC,created_at DESC LIMIT 5")->fetchAll();
echo json_encode(['new'=>$new,'high'=>$high,'items'=>$items], JSON_UNESCAPED_UNICODE);
