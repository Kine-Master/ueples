<?php
// backend/user_management/toggle_status.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/functions.php';
requireRoleApi('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']); exit;
}

try {
    $user_id = (int)($_POST['user_id'] ?? 0);
    if (!$user_id) throw new Exception("user_id is required.");

    // Guard: admin cannot deactivate themselves
    if ($user_id === (int)$_SESSION['user_id']) {
        throw new Exception("You cannot deactivate your own account.");
    }

    $stmt = $pdo->prepare("SELECT is_active, first_name, last_name FROM user WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if (!$user) throw new Exception("User not found.");

    $newStatus = $user['is_active'] ? 0 : 1;
    $pdo->prepare("UPDATE user SET is_active = ? WHERE user_id = ?")->execute([$newStatus, $user_id]);

    $action = $newStatus ? 'ACTIVATED_USER' : 'DEACTIVATED_USER';
    logAudit($_SESSION['user_id'], $action, "{$user['first_name']} {$user['last_name']} (#{$user_id})");

    echo json_encode(['status' => 'success', 'new_status' => $newStatus]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>