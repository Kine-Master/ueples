<?php
// backend/master_data/teacher_subject/remove.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/functions.php';
requireRoleApi(['admin', 'secretary']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }
try {
    $teacher_id = (int)($_POST['teacher_id'] ?? 0);
    $subject_id = (int)($_POST['subject_id'] ?? 0);
    if (!$teacher_id || !$subject_id) throw new Exception("teacher_id and subject_id are required.");
    $pdo->prepare("DELETE FROM teacher_subject WHERE teacher_id = ? AND subject_id = ?")
        ->execute([$teacher_id, $subject_id]);
    logAudit($_SESSION['user_id'], 'REMOVE_SUBJECT', "Removed subject #$subject_id from teacher #$teacher_id");
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
