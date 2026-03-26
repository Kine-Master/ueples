<?php
// backend/student/list.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/functions.php';
requireRoleApi(['admin', 'principal', 'secretary', 'teacher']);

try {
    $class_section_id = (int)($_GET['class_section_id'] ?? 0);
    if (!$class_section_id) throw new Exception("class_section_id is required.");

    // Teachers can only view students in sections they are assigned to (schedule OR advisory)
    if ($_SESSION['role'] === 'teacher') {
        $stmt = $pdo->prepare("
            SELECT 1 FROM schedule WHERE class_section_id = ? AND teacher_id = ? AND is_active = 1
            UNION
            SELECT 1 FROM class_section WHERE class_section_id = ? AND adviser_id = ?
        ");
        $stmt->execute([$class_section_id, $_SESSION['user_id'], $class_section_id, $_SESSION['user_id']]);
        if (!$stmt->fetch()) throw new Exception("Unauthorized: you are not assigned to this class section.");
    }

    $search = $_GET['search'] ?? '';
    $sql = "SELECT st.*, cs.section_name, gl.name AS grade_name, sy.label AS school_year
            FROM student st
            JOIN class_section cs ON st.class_section_id = cs.class_section_id
            JOIN grade_level gl ON cs.grade_level_id = gl.grade_level_id
            JOIN school_year sy ON cs.school_year_id = sy.school_year_id
            WHERE st.class_section_id = ?";
    $params = [$class_section_id];

    if (!empty($search)) {
        $sql .= " AND (st.last_name LIKE ? OR st.first_name LIKE ? OR st.lrn LIKE ?)";
        $t = "%$search%"; $params = array_merge($params, [$t, $t, $t]);
    }
    $sql .= " ORDER BY st.last_name, st.first_name";
    $stmt = $pdo->prepare($sql); $stmt->execute($params);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Room capacity info (get room from the first LES schedule for this section)
    $rStmt = $pdo->prepare("SELECT r.capacity
                            FROM schedule sc
                            JOIN room r ON sc.room_id = r.room_id
                            WHERE sc.class_section_id = ? AND sc.is_active = 1
                            LIMIT 1");
    $rStmt->execute([$class_section_id]);
    $roomRow = $rStmt->fetch(PDO::FETCH_ASSOC);

    $eStmt = $pdo->prepare("SELECT COUNT(*) AS enrolled FROM student WHERE class_section_id = ? AND is_active = 1");
    $eStmt->execute([$class_section_id]);
    $enrolledRow = $eStmt->fetch(PDO::FETCH_ASSOC);

    $capacity_info = $roomRow ? ['capacity' => $roomRow['capacity'], 'enrolled' => $enrolledRow['enrolled']] : null;

    echo json_encode(['status' => 'success', 'data' => $students, 'capacity_info' => $capacity_info]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
