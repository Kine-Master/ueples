<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/functions.php';
requireRoleApi(['admin', 'principal', 'secretary', 'teacher']);

try {
    // Fetch users who are Teachers (role_id = 4)
    // We select specific columns to keep the payload light
    $sql = "SELECT user_id, first_name, last_name, department, academic_rank 
            FROM user 
            WHERE role_id = 4 AND is_active = 1 
            ORDER BY last_name ASC";
            
    $stmt = $pdo->query($sql);
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $teachers]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>