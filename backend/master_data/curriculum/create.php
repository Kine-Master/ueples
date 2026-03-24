<?php
// backend/master_data/curriculum/create.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/functions.php';
requireRoleApi('secretary');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }
try {
    $grade_level_id = (int)($_POST['grade_level_id'] ?? 0);
    $name           = trim($_POST['name'] ?? '');
    if (!$grade_level_id || !$name) throw new Exception("grade_level_id and name are required.");
    $stmt = $pdo->prepare("SELECT grade_level_id FROM grade_level WHERE grade_level_id = ?");
    $stmt->execute([$grade_level_id]);
    if (!$stmt->fetch()) throw new Exception("Invalid grade level.");
    $pdo->prepare("INSERT INTO curriculum (grade_level_id, name, description) VALUES (?,?,?)")
        ->execute([$grade_level_id, $name, trim($_POST['description'] ?? '') ?: null]);
    $id = $pdo->lastInsertId();
    logAudit($_SESSION['user_id'], 'CREATE_CURRICULUM', "Created curriculum '$name' (Grade Level ID: $grade_level_id)");
    echo json_encode(['status' => 'success', 'curriculum_id' => $id]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
