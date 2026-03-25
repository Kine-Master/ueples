<?php
// backend/master_data/class_section/list.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/functions.php';
requireRoleApi(['admin', 'principal', 'secretary', 'teacher']);
try {
    $sy_filter    = $_GET['school_year_id'] ?? '';
    $grade_filter = $_GET['grade_level_id'] ?? '';
    $sql = "SELECT cs.*, gl.name AS grade_name, sy.label AS sy_label,
                   CONCAT(u.first_name, ' ', u.last_name) AS adviser_name
            FROM class_section cs
            JOIN grade_level gl ON cs.grade_level_id = gl.grade_level_id
            JOIN school_year sy ON cs.school_year_id = sy.school_year_id
            LEFT JOIN user u ON cs.adviser_id = u.user_id
            WHERE 1=1";
    $params = [];
    if (!empty($sy_filter))    { $sql .= " AND cs.school_year_id = ?";   $params[] = (int)$sy_filter; }
    if (!empty($grade_filter)) { $sql .= " AND cs.grade_level_id = ?";   $params[] = (int)$grade_filter; }
    $sql .= " ORDER BY gl.level_order, cs.section_name";
    $stmt = $pdo->prepare($sql); $stmt->execute($params);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
