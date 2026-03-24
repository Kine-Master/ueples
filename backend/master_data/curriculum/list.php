<?php
// backend/master_data/curriculum/list.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/functions.php';
requireRoleApi(['admin', 'principal', 'secretary', 'teacher']);
try {
    $grade_filter = $_GET['grade_level_id'] ?? '';
    $sql = "SELECT c.*, gl.name AS grade_name FROM curriculum c
            JOIN grade_level gl ON c.grade_level_id = gl.grade_level_id WHERE 1=1";
    $params = [];
    if (!empty($grade_filter)) { $sql .= " AND c.grade_level_id = ?"; $params[] = (int)$grade_filter; }
    $sql .= " ORDER BY gl.level_order, c.name";
    $stmt = $pdo->prepare($sql); $stmt->execute($params);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
