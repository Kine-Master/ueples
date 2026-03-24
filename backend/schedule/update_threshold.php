<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

try {
    $months = (int)$_POST['archive_threshold'];

    // Update the single configuration row
    $stmt = $pdo->prepare("UPDATE archive_schedule_threshold SET archive_threshold = ? WHERE threshold_id = 1");
    $stmt->execute([$months]);

    // Audit Log
    session_start();
    logAudit($_SESSION['user_id'], "Updated Schedule Archive Threshold to $months months");

    echo json_encode(['status' => 'success', 'message' => 'Threshold updated']);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>