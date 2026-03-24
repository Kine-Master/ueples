<?php
// backend/master_data/building/create.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/functions.php';
requireRoleApi('secretary');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }
try {
    $name = trim($_POST['name'] ?? '');
    if (!$name) throw new Exception("Building name is required.");
    $pdo->prepare("INSERT INTO building (name, description) VALUES (?,?)")
        ->execute([$name, trim($_POST['description'] ?? '') ?: null]);
    $id = $pdo->lastInsertId();
    logAudit($_SESSION['user_id'], 'CREATE_BUILDING', "Created building '$name'");
    echo json_encode(['status' => 'success', 'building_id' => $id]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
