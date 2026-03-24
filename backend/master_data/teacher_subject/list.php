<?php
// backend/master_data/teacher_subject/list.php
// Gets subjects assigned to a teacher, or all teacher-subject pairs.
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/functions.php';
requireRoleApi(['admin', 'secretary']);
try {
    $teacher_id = (int)($_GET['teacher_id'] ?? 0);
    if ($teacher_id) {
        $stmt = $pdo->prepare(
            "SELECT s.subject_id, s.name AS subject_name, s.units,
                    c.name AS curriculum_name, gl.name AS grade_name
             FROM teacher_subject ts
             JOIN subject s ON ts.subject_id = s.subject_id
             JOIN curriculum c ON s.curriculum_id = c.curriculum_id
             JOIN grade_level gl ON c.grade_level_id = gl.grade_level_id
             WHERE ts.teacher_id = ? ORDER BY gl.level_order, s.name"
        );
        $stmt->execute([$teacher_id]);
    } else {
        // All mappings
        $stmt = $pdo->query(
            "SELECT ts.teacher_id, ts.subject_id,
                    CONCAT(u.last_name, ', ', u.first_name) AS teacher_name,
                    s.name AS subject_name
             FROM teacher_subject ts
             JOIN user u ON ts.teacher_id = u.user_id
             JOIN subject s ON ts.subject_id = s.subject_id
             ORDER BY u.last_name, s.name"
        );
    }
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
