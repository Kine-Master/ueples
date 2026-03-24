<?php
// backend/master_data/room/update.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/functions.php';
requireRoleApi('secretary');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }
try {
    $id       = (int)($_POST['room_id'] ?? 0);
    $room_name= trim($_POST['room_name'] ?? '');
    $capacity = (int)($_POST['capacity'] ?? 40);
    if (!$id || !$room_name || $capacity < 1) throw new Exception("room_id, room_name, and valid capacity are required.");
    $pdo->prepare("UPDATE room SET room_name=?, capacity=?, room_type=? WHERE room_id=?")
        ->execute([$room_name, $capacity, trim($_POST['room_type'] ?? '') ?: null, $id]);
    logAudit($_SESSION['user_id'], 'UPDATE_ROOM', "Updated room #$id to '$room_name' (capacity $capacity)");
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
