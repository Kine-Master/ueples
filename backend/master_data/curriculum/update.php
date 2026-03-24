<?php
// backend/master_data/curriculum/update.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/functions.php';
requireRoleApi('secretary');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }
try {
    $id   = (int)($_POST['curriculum_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    if (!$id || !$name) throw new Exception("curriculum_id and name are required.");
    $pdo->prepare("UPDATE curriculum SET name=?, description=? WHERE curriculum_id=?")
        ->execute([$name, trim($_POST['description'] ?? '') ?: null, $id]);
    logAudit($_SESSION['user_id'], 'UPDATE_CURRICULUM', "Updated curriculum #$id to '$name'");
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
