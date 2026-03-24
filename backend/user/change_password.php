<?php
// backend/user/change_password.php — All authenticated roles.
header('Content-Type: application/json');
require_once __DIR__ . '/../config/functions.php';
requireRoleApi(['admin', 'principal', 'secretary', 'teacher']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }
try {
    $current_pass = $_POST['current_password'] ?? '';
    $new_pass     = $_POST['new_password']     ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    if (!$current_pass || !$new_pass || !$confirm_pass) throw new Exception("All password fields are required.");
    if ($new_pass !== $confirm_pass) throw new Exception("New password and confirmation do not match.");
    if (strlen($new_pass) < 6) throw new Exception("New password must be at least 6 characters.");

    $stmt = $pdo->prepare("SELECT password_hash FROM user WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($current_pass, $user['password_hash'])) {
        throw new Exception("Current password is incorrect.");
    }

    $hash = password_hash($new_pass, PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE user SET password_hash = ? WHERE user_id = ?")->execute([$hash, $_SESSION['user_id']]);
    logAudit($_SESSION['user_id'], 'CHANGE_PASSWORD', "Changed own password");
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>