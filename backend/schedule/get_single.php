<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No ID provided']);
    exit;
}

try {
    // Join with USER table to get the teacher's name for display
    $sql = "SELECT s.*, u.first_name, u.last_name 
            FROM schedule s
            JOIN user u ON s.teacher_id = u.user_id
            WHERE s.schedule_id = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_GET['id']]);
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($schedule) {
        // Format name for frontend
        $schedule['teacher_name'] = $schedule['last_name'] . ', ' . $schedule['first_name'];
        echo json_encode(['status' => 'success', 'data' => $schedule]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Schedule not found']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>