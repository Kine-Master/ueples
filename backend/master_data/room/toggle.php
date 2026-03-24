<?php
// backend/master_data/room/toggle.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/functions.php';
requireRoleApi('secretary');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }
try {
    $id = (int)($_POST['room_id'] ?? 0);
    if (!$id) throw new Exception("room_id is required.");
    $stmt = $pdo->prepare("SELECT is_active, room_name FROM room WHERE room_id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) throw new Exception("Room not found.");
    $new = $row['is_active'] ? 0 : 1;
    $pdo->prepare("UPDATE room SET is_active = ? WHERE room_id = ?")->execute([$new, $id]);
    logAudit($_SESSION['user_id'], $new ? 'ENABLED_ROOM' : 'DISABLED_ROOM', "Room: {$row['room_name']} (#$id)");
    echo json_encode(['status' => 'success', 'is_active' => $new]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
