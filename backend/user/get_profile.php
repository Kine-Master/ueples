<?php
// backend/user/get_profile.php — All authenticated roles.
header('Content-Type: application/json');
require_once __DIR__ . '/../config/functions.php';
requireRoleApi(['admin', 'principal', 'secretary', 'teacher']);
try {
    $stmt = $pdo->prepare(
        "SELECT u.user_id, u.username, u.first_name, u.middle_name, u.last_name,
                u.email, u.academic_rank, u.school_college, u.department,
                u.date_created, r.role_name
         FROM user u JOIN role r ON u.role_id = r.role_id WHERE u.user_id = ?"
    );
    $stmt->execute([$_SESSION['user_id']]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$profile) throw new Exception("Profile not found.");
    echo json_encode(['status' => 'success', 'data' => $profile]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>