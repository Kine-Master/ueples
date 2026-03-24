<?php
// backend/user/update_profile.php — All authenticated roles can update their own profile.
header('Content-Type: application/json');
require_once __DIR__ . '/../config/functions.php';
requireRoleApi(['admin', 'principal', 'secretary', 'teacher']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }
try {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name']  ?? '');
    if (!$first_name || !$last_name) throw new Exception("First name and last name are required.");
    $pdo->prepare(
        "UPDATE user SET first_name=?, middle_name=?, last_name=?,
                         email=?, academic_rank=?, school_college=?, department=?
         WHERE user_id=?"
    )->execute([
        $first_name, trim($_POST['middle_name'] ?? '') ?: null, $last_name,
        trim($_POST['email'] ?? '') ?: null,
        trim($_POST['academic_rank'] ?? '') ?: null,
        trim($_POST['school_college'] ?? '') ?: null,
        trim($_POST['department'] ?? '') ?: null,
        $_SESSION['user_id']
    ]);
    // Update session name
    $_SESSION['full_name'] = "$first_name $last_name";
    logAudit($_SESSION['user_id'], 'UPDATE_PROFILE', "Updated own profile");
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>