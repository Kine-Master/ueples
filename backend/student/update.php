<?php
// backend/student/update.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/functions.php';
requireRoleApi('teacher');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }
try {
    $student_id = (int)($_POST['student_id'] ?? 0);
    $last_name  = trim($_POST['last_name']   ?? '');
    $first_name = trim($_POST['first_name']  ?? '');
    $gender     = trim($_POST['gender']      ?? '');
    if (!$student_id || !$last_name || !$first_name || !$gender) throw new Exception("student_id, last_name, first_name, gender are required.");

    // Verify teacher owns this student's section
    $stmt = $pdo->prepare("SELECT s.class_section_id FROM student s WHERE s.student_id = ?");
    $stmt->execute([$student_id]);
    $stu = $stmt->fetch();
    if (!$stu) throw new Exception("Student not found.");
    $stmt = $pdo->prepare("
        SELECT 1 FROM schedule WHERE class_section_id = ? AND teacher_id = ? AND is_active = 1
        UNION
        SELECT 1 FROM class_section WHERE class_section_id = ? AND adviser_id = ?
    ");
    $stmt->execute([$stu['class_section_id'], $_SESSION['user_id'], $stu['class_section_id'], $_SESSION['user_id']]);
    if (!$stmt->fetch()) throw new Exception("You are not assigned to this student's class section.");

    // LRN uniqueness
    $lrn = trim($_POST['lrn'] ?? '') ?: null;
    if ($lrn) {
        $stmt = $pdo->prepare("SELECT student_id FROM student WHERE lrn = ? AND student_id != ?");
        $stmt->execute([$lrn, $student_id]);
        if ($stmt->fetch()) throw new Exception("LRN '$lrn' is already used by another student.");
    }

    $pdo->prepare(
        "UPDATE student SET lrn=?, last_name=?, first_name=?, middle_name=?,
                            gender=?, date_of_birth=?, guardian_name=?, guardian_contact=?
         WHERE student_id=?"
    )->execute([
        $lrn, $last_name, $first_name, trim($_POST['middle_name'] ?? '') ?: null,
        $gender, trim($_POST['date_of_birth'] ?? '') ?: null,
        trim($_POST['guardian_name']    ?? '') ?: null,
        trim($_POST['guardian_contact'] ?? '') ?: null,
        $student_id
    ]);

    logAudit($_SESSION['user_id'], 'UPDATE_STUDENT', "Updated student #$student_id: $last_name, $first_name");
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
