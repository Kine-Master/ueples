<?php
// backend/student/create.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/functions.php';
requireRoleApi('teacher');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }

try {
    $class_section_id = (int)($_POST['class_section_id'] ?? 0);
    $last_name        = trim($_POST['last_name']  ?? '');
    $first_name       = trim($_POST['first_name'] ?? '');
    $gender           = trim($_POST['gender']     ?? '');

    if (!$class_section_id || !$last_name || !$first_name || !$gender) {
        throw new Exception("class_section_id, last_name, first_name, and gender are required.");
    }
    if (!in_array($gender, ['Male', 'Female'])) throw new Exception("Gender must be 'Male' or 'Female'.");

    // Teacher must be assigned to this section (via schedule or advisory)
    $stmt = $pdo->prepare("
        SELECT 1 FROM schedule WHERE class_section_id = ? AND teacher_id = ? AND is_active = 1
        UNION
        SELECT 1 FROM class_section WHERE class_section_id = ? AND adviser_id = ?
    ");
    $stmt->execute([$class_section_id, $_SESSION['user_id'], $class_section_id, $_SESSION['user_id']]);
    if (!$stmt->fetch()) throw new Exception("You are not assigned to this class section.");

    // LRN duplicate check (only if provided)
    $lrn = trim($_POST['lrn'] ?? '') ?: null;
    if ($lrn) {
        $stmt = $pdo->prepare("SELECT student_id FROM student WHERE lrn = ?");
        $stmt->execute([$lrn]);
        if ($stmt->fetch()) throw new Exception("LRN '$lrn' is already assigned to another student.");
    }

    // Room capacity soft warning check
    $rStmt = $pdo->prepare(
        "SELECT r.capacity
         FROM schedule sc
         JOIN room r ON sc.room_id = r.room_id
         WHERE sc.class_section_id = ? AND sc.is_active = 1
         LIMIT 1"
    );
    $rStmt->execute([$class_section_id]);
    $roomRow = $rStmt->fetch(PDO::FETCH_ASSOC);

    $eStmt = $pdo->prepare("SELECT COUNT(*) AS enrolled FROM student WHERE class_section_id = ? AND is_active = 1");
    $eStmt->execute([$class_section_id]);
    $enrolledRow = $eStmt->fetch(PDO::FETCH_ASSOC);

    $cap = $roomRow ? ['capacity' => $roomRow['capacity'], 'enrolled' => $enrolledRow['enrolled']] : null;
    $at_capacity = ($cap && $cap['capacity'] && $cap['enrolled'] >= $cap['capacity']);

    $pdo->prepare(
        "INSERT INTO student (class_section_id, lrn, last_name, first_name, middle_name,
                              gender, date_of_birth, guardian_name, guardian_contact)
         VALUES (?,?,?,?,?,?,?,?,?)"
    )->execute([
        $class_section_id, $lrn, $last_name, $first_name,
        trim($_POST['middle_name'] ?? '') ?: null,
        $gender,
        trim($_POST['date_of_birth'] ?? '') ?: null,
        trim($_POST['guardian_name']    ?? '') ?: null,
        trim($_POST['guardian_contact'] ?? '') ?: null,
    ]);

    $id = $pdo->lastInsertId();
    logAudit($_SESSION['user_id'], 'ADD_STUDENT', "Added student #$id: $last_name, $first_name to section #$class_section_id");

    echo json_encode([
        'status'      => 'success',
        'student_id'  => $id,
        'warning'     => $at_capacity ? "⚠ Room capacity reached ({$cap['capacity']} students). Consider requesting a larger room." : null
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
