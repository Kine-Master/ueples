<?php
// backend/master_data/class_section/update.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/functions.php';
requireRoleApi('secretary');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }
try {
    $id   = (int)($_POST['class_section_id'] ?? 0);
    $name = trim($_POST['section_name'] ?? '');
    if (!$id || !$name) throw new Exception("class_section_id and section_name are required.");
    $pdo->prepare("UPDATE class_section SET section_name=? WHERE class_section_id=?")->execute([$name, $id]);
    logAudit($_SESSION['user_id'], 'UPDATE_CLASS_SECTION', "Updated class section #$id to '$name'");
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
