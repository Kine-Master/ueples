<?php
// backend/user_management/reset_password.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/functions.php';
requireRoleApi('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']); exit;
}

try {
    $user_id  = (int)($_POST['user_id'] ?? 0);
    $new_pass = $_POST['new_password'] ?? '';

    if (!$user_id || strlen($new_pass) < 6) {
        throw new Exception("user_id is required and password must be at least 6 characters.");
    }

    $stmt = $pdo->prepare("SELECT user_id, first_name, last_name FROM user WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if (!$user) throw new Exception("User not found.");

    $hash = password_hash($new_pass, PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE user SET password_hash = ? WHERE user_id = ?")->execute([$hash, $user_id]);

    logAudit($_SESSION['user_id'], 'RESET_PASSWORD', "Reset password for user #{$user_id}: {$user['first_name']} {$user['last_name']}");
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>