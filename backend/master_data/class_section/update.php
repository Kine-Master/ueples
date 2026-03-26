<?php
// backend/master_data/class_section/update.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/functions.php';
requireRoleApi('secretary');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }
try {
    $id   = (int)($_POST['class_section_id'] ?? 0);
    $name = trim($_POST['section_name'] ?? '');
    $adviser_id = isset($_POST['adviser_id']) && $_POST['adviser_id'] !== '' ? (int)$_POST['adviser_id'] : null;
    if (!$id || !$name) throw new Exception("class_section_id and section_name are required.");

    // Removed adviser uniqueness validation: a teacher CAN advise multiple sections.

    $pdo->prepare("UPDATE class_section SET section_name=?, adviser_id=? WHERE class_section_id=?")->execute([$name, $adviser_id, $id]);
    logAudit($_SESSION['user_id'], 'UPDATE_CLASS_SECTION', "Updated class section #$id to '$name'" . ($adviser_id ? " adviser=#$adviser_id" : " adviser=none"));
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
