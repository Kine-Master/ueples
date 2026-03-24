<?php
// backend/master_data/building/update.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/functions.php';
requireRoleApi('secretary');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }
try {
    $id   = (int)($_POST['building_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    if (!$id || !$name) throw new Exception("building_id and name are required.");
    $pdo->prepare("UPDATE building SET name=?, description=? WHERE building_id=?")
        ->execute([$name, trim($_POST['description'] ?? '') ?: null, $id]);
    logAudit($_SESSION['user_id'], 'UPDATE_BUILDING', "Updated building #$id to '$name'");
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
