<?php
require_once 'config/database.php';
require_once 'includes/session.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'Unauthorized']);
    exit;
}

$code = $_GET['code'] ?? '';

$stmt = $pdo->prepare("SELECT status FROM payments WHERE transaction_code = ? AND user_id = ? LIMIT 1");
$stmt->execute([$code, $_SESSION['user_id']]);
$payment = $stmt->fetch();

if ($payment) {
    echo json_encode(['status' => $payment['status']]);
} else {
    echo json_encode(['status' => 'NotFound']);
}
