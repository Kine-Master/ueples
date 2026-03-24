<?php
// backend/user_management/get_user.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/functions.php';
requireRoleApi('admin');

$user_id = (int)($_GET['user_id'] ?? 0);
if (!$user_id) { echo json_encode(['status' => 'error', 'message' => 'user_id required']); exit; }

try {
    $stmt = $pdo->prepare(
        "SELECT u.user_id, u.username, u.first_name, u.middle_name, u.last_name,
                u.email, u.academic_rank, u.school_college, u.department,
                u.is_active, u.role_id, r.role_name, u.date_created
         FROM user u JOIN role r ON u.role_id = r.role_id WHERE u.user_id = ?"
    );
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) throw new Exception("User not found.");
    echo json_encode(['status' => 'success', 'data' => $user]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>