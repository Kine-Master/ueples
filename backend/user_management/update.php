<?php
// backend/user_management/update.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/functions.php';
requireRoleApi('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']); exit;
}

try {
    $user_id    = (int)($_POST['user_id'] ?? 0);
    $role_id    = (int)($_POST['role_id'] ?? 0);
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');

    if (!$user_id || !$role_id || !$first_name || !$last_name) {
        throw new Exception("Required fields: user_id, role, first name, last name.");
    }

    // Prevent admin from demoting/removing their own role (safety guard)
    if ($user_id === (int)$_SESSION['user_id'] && $role_id !== ROLE_ADMIN) {
        throw new Exception("You cannot change your own role.");
    }

    $stmt = $pdo->prepare(
        "UPDATE user SET role_id=?, first_name=?, middle_name=?, last_name=?,
                         email=?, academic_rank=?, school_college=?, department=?
         WHERE user_id=?"
    );
    $stmt->execute([
        $role_id, $first_name,
        trim($_POST['middle_name'] ?? '') ?: null, $last_name,
        trim($_POST['email'] ?? '') ?: null,
        trim($_POST['academic_rank'] ?? '') ?: null,
        trim($_POST['school_college'] ?? '') ?: null,
        trim($_POST['department'] ?? '') ?: null,
        $user_id,
    ]);

    logAudit($_SESSION['user_id'], 'UPDATE_USER', "Updated user #$user_id: $first_name $last_name");
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>