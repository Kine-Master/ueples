<?php
// backend/master_data/school_year/set_active.php
// Sets one school year as the active one (deactivates all others).
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/functions.php';
requireRoleApi('secretary');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }
try {
    $id = (int)($_POST['school_year_id'] ?? 0);
    if (!$id) throw new Exception("school_year_id is required.");
    $stmt = $pdo->prepare("SELECT label FROM school_year WHERE school_year_id = ?");
    $stmt->execute([$id]);
    $sy = $stmt->fetch();
    if (!$sy) throw new Exception("School year not found.");

    // Deactivate all, then activate the selected one
    $pdo->exec("UPDATE school_year SET is_active = 0");
    $pdo->prepare("UPDATE school_year SET is_active = 1 WHERE school_year_id = ?")->execute([$id]);

    logAudit($_SESSION['user_id'], 'SET_ACTIVE_SCHOOL_YEAR', "Activated SY: {$sy['label']}");
    echo json_encode(['status' => 'success', 'label' => $sy['label']]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
