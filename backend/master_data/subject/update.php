<?php
// backend/master_data/subject/update.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/functions.php';
requireRoleApi('secretary');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }
try {
    $id    = (int)($_POST['subject_id'] ?? 0);
    $name  = trim($_POST['name'] ?? '');
    $units = (float)($_POST['units'] ?? 0);
    if (!$id || !$name) throw new Exception("subject_id and name are required.");
    $pdo->prepare("UPDATE subject SET name=?, units=?, description=? WHERE subject_id=?")
        ->execute([$name, $units, trim($_POST['description'] ?? '') ?: null, $id]);
    logAudit($_SESSION['user_id'], 'UPDATE_SUBJECT', "Updated subject #$id to '$name'");
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
