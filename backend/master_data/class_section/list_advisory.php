<?php
// backend/master_data/class_section/list_advisory.php
// Returns sections where the current teacher is the adviser (for "My Classes" sidebar).
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/functions.php';
requireRoleApi('teacher');

try {
    $user_id = (int)$_SESSION['user_id'];

    $sql = "SELECT cs.class_section_id, cs.section_name, gl.name AS grade_name,
                   sy.label AS sy_label
            FROM class_section cs
            JOIN grade_level gl ON cs.grade_level_id = gl.grade_level_id
            JOIN school_year sy ON cs.school_year_id = sy.school_year_id
            WHERE cs.adviser_id = ? AND cs.is_active = 1";
    $params = [$user_id];

    // Optionally filter by school year
    $sy_filter = (int)($_GET['school_year_id'] ?? 0);
    if ($sy_filter) {
        $sql .= " AND cs.school_year_id = ?";
        $params[] = $sy_filter;
    }

    $sql .= " ORDER BY gl.level_order, cs.section_name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
