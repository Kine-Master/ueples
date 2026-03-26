<?php
// backend/student/toggle.php — Soft delete (is_active toggle). Teacher only.
header('Content-Type: application/json');
require_once __DIR__ . '/../config/functions.php';
requireRoleApi('teacher');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }
try {
    $student_id = (int)($_POST['student_id'] ?? 0);
    if (!$student_id) throw new Exception("student_id is required.");
    $stmt = $pdo->prepare("SELECT is_active, last_name, first_name, class_section_id FROM student WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $stu = $stmt->fetch();
    if (!$stu) throw new Exception("Student not found.");
    // Ownership check
    $stmt = $pdo->prepare("
        SELECT 1 FROM schedule WHERE class_section_id = ? AND teacher_id = ? AND is_active = 1
        UNION
        SELECT 1 FROM class_section WHERE class_section_id = ? AND adviser_id = ?
    ");
    $stmt->execute([$stu['class_section_id'], $_SESSION['user_id'], $stu['class_section_id'], $_SESSION['user_id']]);
    if (!$stmt->fetch()) throw new Exception("You are not assigned to this student's class section.");
    $new = $stu['is_active'] ? 0 : 1;
    $pdo->prepare("UPDATE student SET is_active = ? WHERE student_id = ?")->execute([$new, $student_id]);
    $action = $new ? 'RESTORED_STUDENT' : 'REMOVED_STUDENT';
    logAudit($_SESSION['user_id'], $action, "{$stu['last_name']}, {$stu['first_name']} (#$student_id)");
    echo json_encode(['status' => 'success', 'is_active' => $new]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
