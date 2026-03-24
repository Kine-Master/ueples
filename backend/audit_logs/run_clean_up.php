<?php
// backend/audit_logs/run_clean_up.php — Admin only. Deletes logs older than threshold.
header('Content-Type: application/json');
require_once __DIR__ . '/../config/functions.php';
requireRoleApi('admin');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }
try {
    $stmt = $pdo->query("SELECT deletion_threshold FROM audit_log_deletion_threshold WHERE threshold_id = 1");
    $months = (int)($stmt->fetchColumn() ?: 12);
    $cutoff = date('Y-m-d H:i:s', strtotime("-$months months"));
    $del = $pdo->prepare("DELETE FROM audit_log WHERE timestamp < ?");
    $del->execute([$cutoff]);
    $deleted = $del->rowCount();
    logAudit($_SESSION['user_id'], 'AUDIT_CLEANUP', "Deleted $deleted audit log entries older than $months months (before $cutoff)");
    echo json_encode(['status' => 'success', 'deleted' => $deleted, 'cutoff' => $cutoff]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>