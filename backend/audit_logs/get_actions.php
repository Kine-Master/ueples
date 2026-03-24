<?php
// backend/audit_logs/get_actions.php — Admin only. Returns distinct action types for filter dropdown.
header('Content-Type: application/json');
require_once __DIR__ . '/../config/functions.php';
requireRoleApi('admin');
try {
    $stmt = $pdo->query("SELECT DISTINCT user_action FROM audit_log ORDER BY user_action ASC");
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_COLUMN)]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
