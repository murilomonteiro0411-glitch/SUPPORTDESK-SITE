<?php
function requireActivePlan(PDO $pdo): void {
    requireLogin();
    if (isAdmin()) { header('Location: ../admin/dashboard.php'); exit; }
    $stmt = $pdo->prepare('SELECT active_plan, plan_expires_at FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]); $account = $stmt->fetch();
    $active = $account && !empty($account['active_plan']) && !empty($account['plan_expires_at']) && strtotime($account['plan_expires_at']) >= time();
    if (!$active) { header('Location: ../plans.php?required=1'); exit; }
}
