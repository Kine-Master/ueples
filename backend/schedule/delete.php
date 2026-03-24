<?php
// backend/schedule/delete.php — Soft delete (set is_active = 0). Secretary only.
header('Content-Type: application/json');
require_once __DIR__ . '/../config/functions.php';
requireRoleApi('secretary');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }
try {
    $schedule_id = (int)($_POST['schedule_id'] ?? 0);
    if (!$schedule_id) throw new Exception("schedule_id is required.");
    $stmt = $pdo->prepare("SELECT schedule_type, subject_id, coed_subject FROM schedule WHERE schedule_id = ? AND is_active = 1");
    $stmt->execute([$schedule_id]);
    $sched = $stmt->fetch();
    if (!$sched) throw new Exception("Active schedule not found.");
    $pdo->prepare("UPDATE schedule SET is_active = 0 WHERE schedule_id = ?")->execute([$schedule_id]);
    $label = $sched['schedule_type'] === 'LES' ? "LES (Subject #" . $sched['subject_id'] . ")" : "COED ({$sched['coed_subject']})";
    logAudit($_SESSION['user_id'], 'DELETE_SCHEDULE', "Archived schedule #$schedule_id: $label");
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>