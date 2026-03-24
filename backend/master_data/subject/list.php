<?php
// backend/master_data/subject/list.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/functions.php';
requireRoleApi(['admin', 'principal', 'secretary', 'teacher']);
try {
    $curriculum_filter = $_GET['curriculum_id'] ?? '';
    $grade_filter = $_GET['grade_level_id'] ?? '';
    $sql = "SELECT s.*, c.name AS curriculum_name, gl.name AS grade_name
            FROM subject s
            JOIN curriculum c ON s.curriculum_id = c.curriculum_id
            JOIN grade_level gl ON c.grade_level_id = gl.grade_level_id
            WHERE 1=1";
    $params = [];
    if (!empty($curriculum_filter)) { $sql .= " AND s.curriculum_id = ?"; $params[] = (int)$curriculum_filter; }
    if (!empty($grade_filter)) { $sql .= " AND gl.grade_level_id = ?"; $params[] = (int)$grade_filter; }
    $sql .= " ORDER BY gl.level_order, c.name, s.name";
    $stmt = $pdo->prepare($sql); $stmt->execute($params);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
