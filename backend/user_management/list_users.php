<?php
// backend/user_management/list_users.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/functions.php';
requireRoleApi('admin');

$search      = $_GET['search'] ?? '';
$role_filter = $_GET['role']   ?? '';
$sort_by     = $_GET['sort']   ?? 'last_name';
$order       = strtoupper($_GET['order'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

$allowed_sorts = ['last_name', 'first_name', 'username', 'department', 'role_name', 'is_active', 'date_created'];
if (!in_array($sort_by, $allowed_sorts)) $sort_by = 'last_name';

try {
    $sql = "SELECT u.user_id, u.username, u.first_name, u.middle_name, u.last_name,
                   u.email, u.department, u.academic_rank, u.school_college,
                   u.is_active, u.date_created, u.role_id, r.role_name
            FROM user u JOIN role r ON u.role_id = r.role_id WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
        $term = "%$search%";
        $params = array_merge($params, [$term, $term, $term, $term]);
    }
    if (!empty($role_filter)) {
        $sql .= " AND u.role_id = ?";
        $params[] = (int)$role_filter;
    }

    $sql .= " ORDER BY $sort_by $order";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>