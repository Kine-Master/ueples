<?php
// backend/master_data/subject/toggle.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/functions.php';
requireRoleApi('secretary');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }
try {
    $id = (int)($_POST['subject_id'] ?? 0);
    if (!$id) throw new Exception("subject_id is required.");
    $stmt = $pdo->prepare("SELECT is_active, name FROM subject WHERE subject_id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) throw new Exception("Subject not found.");
    $new = $row['is_active'] ? 0 : 1;
    $pdo->prepare("UPDATE subject SET is_active = ? WHERE subject_id = ?")->execute([$new, $id]);
    logAudit($_SESSION['user_id'], $new ? 'ENABLED_SUBJECT' : 'DISABLED_SUBJECT', "Subject: {$row['name']} (#$id)");
    echo json_encode(['status' => 'success', 'is_active' => $new]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
