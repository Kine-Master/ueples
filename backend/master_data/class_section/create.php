<?php
// backend/master_data/class_section/create.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/functions.php';
requireRoleApi('secretary');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }
try {
    $grade_level_id = (int)($_POST['grade_level_id'] ?? 0);
    $school_year_id = (int)($_POST['school_year_id'] ?? 0);
    $section_name   = trim($_POST['section_name'] ?? '');
    if (!$grade_level_id || !$school_year_id || !$section_name) {
        throw new Exception("grade_level_id, school_year_id, and section_name are required.");
    }
    // Check uniqueness
    $stmt = $pdo->prepare("SELECT class_section_id FROM class_section WHERE grade_level_id=? AND school_year_id=? AND section_name=?");
    $stmt->execute([$grade_level_id, $school_year_id, $section_name]);
    if ($stmt->fetch()) throw new Exception("This class section already exists for this grade and school year.");
    $pdo->prepare("INSERT INTO class_section (grade_level_id, school_year_id, section_name) VALUES (?,?,?)")
        ->execute([$grade_level_id, $school_year_id, $section_name]);
    $id = $pdo->lastInsertId();
    logAudit($_SESSION['user_id'], 'CREATE_CLASS_SECTION', "Created section '$section_name' (Grade #$grade_level_id, SY #$school_year_id)");
    echo json_encode(['status' => 'success', 'class_section_id' => $id]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
