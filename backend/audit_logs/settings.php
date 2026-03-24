<?php
// backend/audit_logs/settings.php — Admin only. Get/update both thresholds.
header('Content-Type: application/json');
require_once __DIR__ . '/../config/functions.php';
requireRoleApi('admin');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $archive  = (int)($_POST['archive_threshold']  ?? 0);
        $deletion = (int)($_POST['deletion_threshold'] ?? 0);
        if ($archive  < 1) throw new Exception("Archive threshold must be at least 1 month.");
        if ($deletion < 1) throw new Exception("Deletion threshold must be at least 1 month.");
        $pdo->prepare("UPDATE archive_schedule_threshold SET archive_threshold = ? WHERE threshold_id = 1")->execute([$archive]);
        $pdo->prepare("UPDATE audit_log_deletion_threshold SET deletion_threshold = ? WHERE threshold_id = 1")->execute([$deletion]);
        logAudit($_SESSION['user_id'], 'UPDATE_THRESHOLDS', "Archive: {$archive}mo, Log deletion: {$deletion}mo");
        echo json_encode(['status' => 'success']);
    } else {
        $a = $pdo->query("SELECT archive_threshold FROM archive_schedule_threshold WHERE threshold_id = 1")->fetchColumn();
        $d = $pdo->query("SELECT deletion_threshold FROM audit_log_deletion_threshold WHERE threshold_id = 1")->fetchColumn();
        echo json_encode(['status' => 'success', 'data' => ['archive_threshold' => (int)$a, 'deletion_threshold' => (int)$d]]);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>