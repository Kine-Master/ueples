<?php
// backend/master_data/teacher_subject/assign.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/functions.php';
requireRoleApi(['admin', 'secretary']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }
try {
    $teacher_id = (int)($_POST['teacher_id'] ?? 0);
    $subject_id = (int)($_POST['subject_id'] ?? 0);
    if (!$teacher_id || !$subject_id) throw new Exception("teacher_id and subject_id are required.");
    // Verify teacher role
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM user WHERE user_id = ? AND role_id = ?");
    $stmt->execute([$teacher_id, ROLE_TEACHER]);
    $teacher = $stmt->fetch();
    if (!$teacher) throw new Exception("User is not a valid teacher.");
    // Verify subject
    $stmt = $pdo->prepare("SELECT name FROM subject WHERE subject_id = ?");
    $stmt->execute([$subject_id]);
    $subject = $stmt->fetch();
    if (!$subject) throw new Exception("Subject not found.");
    // INSERT IGNORE handles duplicate gracefully
    $pdo->prepare("INSERT IGNORE INTO teacher_subject (teacher_id, subject_id) VALUES (?,?)")
        ->execute([$teacher_id, $subject_id]);
    logAudit($_SESSION['user_id'], 'ASSIGN_SUBJECT', "Assigned '{$subject['name']}' to {$teacher['last_name']}, {$teacher['first_name']}");
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
