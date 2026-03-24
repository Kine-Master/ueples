<?php
// backend/master_data/room/list.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/functions.php';
requireRoleApi(['admin', 'principal', 'secretary', 'teacher']);
try {
    $building_filter = $_GET['building_id'] ?? '';
    $sql = "SELECT r.*, b.name AS building_name FROM room r JOIN building b ON r.building_id = b.building_id WHERE 1=1";
    $params = [];
    if (!empty($building_filter)) { $sql .= " AND r.building_id = ?"; $params[] = (int)$building_filter; }
    $sql .= " ORDER BY b.name, r.room_name";
    $stmt = $pdo->prepare($sql); $stmt->execute($params);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
