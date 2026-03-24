<?php
// backend/master_data/class_section/toggle.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/functions.php';
requireRoleApi('secretary');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }
try {
    $id = (int)($_POST['class_section_id'] ?? 0);
    if (!$id) throw new Exception("class_section_id is required.");
    $stmt = $pdo->prepare("SELECT is_active, section_name FROM class_section WHERE class_section_id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) throw new Exception("Class section not found.");
    $new = $row['is_active'] ? 0 : 1;
    $pdo->prepare("UPDATE class_section SET is_active = ? WHERE class_section_id = ?")->execute([$new, $id]);
    logAudit($_SESSION['user_id'], $new ? 'ENABLED_CLASS_SECTION' : 'DISABLED_CLASS_SECTION', "Section: {$row['section_name']} (#$id)");
    echo json_encode(['status' => 'success', 'is_active' => $new]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
