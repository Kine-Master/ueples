<?php
// backend/master_data/school_year/create.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/functions.php';
requireRoleApi('secretary');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }
try {
    $label = trim($_POST['label'] ?? '');
    if (!preg_match('/^\d{4}-\d{4}$/', $label)) throw new Exception("Format must be YYYY-YYYY (e.g. 2025-2026).");
    $stmt = $pdo->prepare("SELECT school_year_id FROM school_year WHERE label = ?");
    $stmt->execute([$label]);
    if ($stmt->fetch()) throw new Exception("School year '$label' already exists.");
    $pdo->prepare("INSERT INTO school_year (label) VALUES (?)")->execute([$label]);
    $id = $pdo->lastInsertId();
    logAudit($_SESSION['user_id'], 'CREATE_SCHOOL_YEAR', "Created SY: $label");
    echo json_encode(['status' => 'success', 'school_year_id' => $id]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
