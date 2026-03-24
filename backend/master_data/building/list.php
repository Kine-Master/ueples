<?php
// backend/master_data/building/list.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/functions.php';
requireRoleApi(['admin', 'principal', 'secretary', 'teacher']);
try {
    $stmt = $pdo->query("SELECT b.*, COUNT(r.room_id) AS room_count FROM building b LEFT JOIN room r ON r.building_id = b.building_id AND r.is_active = 1 GROUP BY b.building_id ORDER BY b.name");
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
