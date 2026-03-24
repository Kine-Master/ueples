<?php
// backend/schedule/update.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// FIX: Auto-map role if missing
if (!isset($_SESSION['role_id']) && isset($_SESSION['role'])) {
    $r = strtolower($_SESSION['role']);
    if ($r === 'secretary') $_SESSION['role_id'] = 2;
    elseif ($r === 'teacher') $_SESSION['role_id'] = 3;
    elseif ($r === 'principal') $_SESSION['role_id'] = 1;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

try {
    $id = $_POST['schedule_id'];
    
    // 1. CONFLICT CHECK (Exclude current schedule ID)
    $check_sql = "SELECT subject FROM schedule 
                  WHERE is_active = 1 
                  AND schedule_id != ? 
                  AND day_of_week = ? AND semester = ? AND school_year = ?
                  AND (teacher_id = ? OR room = ?)
                  AND (time_in < ? AND time_out > ?)"; 

    $check = $pdo->prepare($check_sql);
    $check->execute([
        $id,
        $_POST['day_of_week'], $_POST['semester'], $_POST['school_year'],
        $_POST['teacher_id'], $_POST['room'],
        $_POST['time_out'], $_POST['time_in']
    ]);

    if ($conflict = $check->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Conflict Detected with: ' . $conflict['subject']]);
        exit;
    }

    // 2. UPDATE DATABASE
    $sql = "UPDATE schedule SET 
            subject = ?, units = ?, class_type = ?, 
            time_in = ?, time_out = ?, day_of_week = ?, 
            room = ?, course_year = ?, semester = ?, school_year = ?
            WHERE schedule_id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['subject'], $_POST['units'], $_POST['class_type'],
        $_POST['time_in'], $_POST['time_out'], $_POST['day_of_week'],
        $_POST['room'], $_POST['course_year'], $_POST['semester'], $_POST['school_year'],
        $id
    ]);

    // 3. AUDIT LOG
    logAudit($_SESSION['user_id'], "UPDATED_SCHEDULE", "Updated ID: $id (Subject: {$_POST['subject']})");

    echo json_encode(['status' => 'success']);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>