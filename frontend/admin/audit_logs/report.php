<?php
require_once '../../../backend/config/functions.php';
requireRole('admin');

$search = trim($_GET['search'] ?? '');
$action = trim($_GET['action'] ?? '');
$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');

$sql = "SELECT a.timestamp, a.user_action, a.details, a.ip_address,
               u.first_name, u.last_name, r.role_name
        FROM audit_log a
        LEFT JOIN user u ON u.user_id = a.user_id
        LEFT JOIN role r ON r.role_id = u.role_id
        WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR a.user_action LIKE ? OR a.details LIKE ?)";
    $term = '%' . $search . '%';
    array_push($params, $term, $term, $term, $term);
}
if ($action !== '') {
    $sql .= " AND a.user_action = ?";
    $params[] = $action;
}
if ($startDate !== '') {
    $sql .= " AND a.timestamp >= ?";
    $params[] = $startDate . ' 00:00:00';
}
if ($endDate !== '') {
    $sql .= " AND a.timestamp <= ?";
    $params[] = $endDate . ' 23:59:59';
}

$sql .= " ORDER BY a.timestamp DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Audit Log Report</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; color: #111; }
    .no-print { margin-bottom: 16px; text-align: right; }
    .no-print button { padding: 8px 12px; }
    .header { border: 1px solid #222; padding: 12px; margin-bottom: 12px; }
    .header h1 { margin: 0 0 6px; font-size: 18px; }
    .meta { font-size: 13px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #222; padding: 8px; font-size: 12px; text-align: left; vertical-align: top; }
    th { background: #efefef; }
    .footer { margin-top: 12px; font-size: 12px; text-align: right; font-weight: 700; }
    @media print { .no-print { display: none; } body { margin: 8px; } }
  </style>
</head>
<body onload="window.print()">
  <div class="no-print">
    <button onclick="window.print()">Print</button>
    <button onclick="window.close()">Close</button>
  </div>

  <div class="header">
    <h1>UEP LES Scheduling System - Audit Log Report</h1>
    <div class="meta"><strong>Date Range:</strong> <?= e($startDate ?: 'Any') ?> to <?= e($endDate ?: 'Any') ?></div>
    <div class="meta"><strong>Filters:</strong> <?= e($action ?: 'All actions') ?><?= $search !== '' ? ' | Search: ' . e($search) : '' ?></div>
    <div class="meta"><strong>Generated:</strong> <?= e(date('F d, Y h:i A')) ?></div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Timestamp</th>
        <th>User</th>
        <th>Role</th>
        <th>Action</th>
        <th>Details</th>
        <th>IP</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$rows): ?>
        <tr><td colspan="6">No entries found.</td></tr>
      <?php else: ?>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= e(date('Y-m-d h:i A', strtotime($r['timestamp']))) ?></td>
            <td><?= e(trim(($r['first_name'] ?? 'System') . ' ' . ($r['last_name'] ?? ''))) ?></td>
            <td><?= e($r['role_name'] ?: 'System') ?></td>
            <td><?= e($r['user_action']) ?></td>
            <td><?= e($r['details'] ?: '-') ?></td>
            <td><?= e($r['ip_address'] ?: '-') ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <div class="footer">Total Entries: <?= (int)count($rows) ?></div>
</body>
</html>