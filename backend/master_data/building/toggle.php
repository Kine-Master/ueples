<?php
// backend/master_data/building/toggle.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/functions.php';
requireRoleApi('secretary');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }
try {
    $id = (int)($_POST['building_id'] ?? 0);
    if (!$id) throw new Exception("building_id is required.");
    $stmt = $pdo->prepare("SELECT is_active, name FROM building WHERE building_id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) throw new Exception("Building not found.");
    $new = $row['is_active'] ? 0 : 1;
    $pdo->prepare("UPDATE building SET is_active = ? WHERE building_id = ?")->execute([$new, $id]);
    logAudit($_SESSION['user_id'], $new ? 'ENABLED_BUILDING' : 'DISABLED_BUILDING', "Building: {$row['name']} (#$id)");
    echo json_encode(['status' => 'success', 'is_active' => $new]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
