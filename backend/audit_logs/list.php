<?php
// backend/audit_logs/list.php — Admin only.
header('Content-Type: application/json');
require_once __DIR__ . '/../config/functions.php';
requireRoleApi('admin');

$search        = $_GET['search']       ?? '';
$action_filter = $_GET['action']       ?? '';
$start_date    = $_GET['start_date']   ?? '';
$end_date      = $_GET['end_date']     ?? '';
$limit         = isset($_GET['limit']) && $_GET['limit'] === 'all' ? PHP_INT_MAX : 50;
$page          = max(1, (int)($_GET['page'] ?? 1));
$offset        = ($page - 1) * min($limit, 50);

try {
    $sql = "SELECT a.log_id, a.user_action, a.details, a.timestamp, a.ip_address,
                   u.first_name, u.last_name, u.username, r.role_name
            FROM audit_log a
            LEFT JOIN user u ON a.user_id = u.user_id
            LEFT JOIN role r ON u.role_id = r.role_id
            WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR a.user_action LIKE ? OR a.details LIKE ?)";
        $t = "%$search%"; $params = array_merge($params, [$t,$t,$t,$t]);
    }
    if (!empty($action_filter)) { $sql .= " AND a.user_action = ?"; $params[] = $action_filter; }
    if (!empty($start_date)) { $sql .= " AND a.timestamp >= ?"; $params[] = "$start_date 00:00:00"; }
    if (!empty($end_date))   { $sql .= " AND a.timestamp <= ?"; $params[] = "$end_date 23:59:59"; }

    $sql .= " ORDER BY a.timestamp DESC";
    if ($limit !== PHP_INT_MAX) { $sql .= " LIMIT $limit OFFSET $offset"; }

    $stmt = $pdo->prepare($sql); $stmt->execute($params);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
