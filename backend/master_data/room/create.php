<?php
// backend/master_data/room/create.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/functions.php';
requireRoleApi('secretary');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }
try {
    $building_id = (int)($_POST['building_id'] ?? 0);
    $room_name   = trim($_POST['room_name'] ?? '');
    $capacity    = (int)($_POST['capacity'] ?? 40);
    if (!$building_id || !$room_name) throw new Exception("building_id and room_name are required.");
    if ($capacity < 1) throw new Exception("Capacity must be at least 1.");
    $pdo->prepare("INSERT INTO room (building_id, room_name, capacity, room_type) VALUES (?,?,?,?)")
        ->execute([$building_id, $room_name, $capacity, trim($_POST['room_type'] ?? '') ?: null]);
    $id = $pdo->lastInsertId();
    logAudit($_SESSION['user_id'], 'CREATE_ROOM', "Created room '$room_name' (Building #$building_id, capacity $capacity)");
    echo json_encode(['status' => 'success', 'room_id' => $id]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
