<?php
// backend/master_data/subject/create.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/functions.php';
requireRoleApi('secretary');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }
try {
    $curriculum_id = (int)($_POST['curriculum_id'] ?? 0);
    $name          = trim($_POST['name'] ?? '');
    $units         = (float)($_POST['units'] ?? 0);
    if (!$curriculum_id || !$name) throw new Exception("curriculum_id and name are required.");
    $stmt = $pdo->prepare("SELECT curriculum_id FROM curriculum WHERE curriculum_id = ?");
    $stmt->execute([$curriculum_id]);
    if (!$stmt->fetch()) throw new Exception("Invalid curriculum.");
    $pdo->prepare("INSERT INTO subject (curriculum_id, name, units, description) VALUES (?,?,?,?)")
        ->execute([$curriculum_id, $name, $units, trim($_POST['description'] ?? '') ?: null]);
    $id = $pdo->lastInsertId();
    logAudit($_SESSION['user_id'], 'CREATE_SUBJECT', "Created subject '$name' (curriculum #$curriculum_id)");
    echo json_encode(['status' => 'success', 'subject_id' => $id]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
