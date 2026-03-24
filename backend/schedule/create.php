<?php
// backend/schedule/create.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

// 1. START SESSION SAFELY
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. HANDLE SESSION MISMATCH (The Fix)
// If the Login system set 'role' (string) but not 'role_id' (int), we map it here.
if (!isset($_SESSION['role_id']) && isset($_SESSION['role'])) {
    $roleName = strtolower($_SESSION['role']);
    if ($roleName === 'principal') $_SESSION['role_id'] = 1;
    elseif ($roleName === 'secretary') $_SESSION['role_id'] = 2;
    elseif ($roleName === 'teacher') $_SESSION['role_id'] = 3;
}

// 3. SECURITY CHECK
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id'])) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Unauthorized Access. Session data missing. Try logging out and back in.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request Method']);
    exit;
}

try {
    $role_id = (int)$_SESSION['role_id']; 
    $schedule_type = '';
    $target_teacher_id = 0;

    // 4. ROLE LOGIC
    if ($role_id === 2) { 
        // SECRETARY: Creates 'LES'
        $schedule_type = 'LES';
        
        // Validation: Ensure Teacher ID is valid
        $target_teacher_id = isset($_POST['teacher_id']) ? (int)$_POST['teacher_id'] : 0;
        if ($target_teacher_id <= 0) {
            throw new Exception("Secretary must select a valid Teacher.");
        }

    } elseif ($role_id === 3) {
        // TEACHER: Creates 'COED'
        $schedule_type = 'COED';
        $target_teacher_id = $_SESSION['user_id']; // Assign to self

    } else {
        throw new Exception("You are not authorized to create schedules.");
    }

    // 5. CONFLICT CHECK
    $sql_check = "SELECT subject FROM schedule 
                  WHERE is_active = 1 
                  AND day_of_week = ? AND semester = ? AND school_year = ?
                  AND (teacher_id = ? OR room = ?)
                  AND (time_in < ? AND time_out > ?)";
                  
    $stmt = $pdo->prepare($sql_check);
    $stmt->execute([
        $_POST['day_of_week'], $_POST['semester'], $_POST['school_year'],
        $target_teacher_id, $_POST['room'],
        $_POST['time_out'], $_POST['time_in']
    ]);

    if ($conflict = $stmt->fetch()) {
        throw new Exception("Conflict detected! '{$conflict['subject']}' is already scheduled here.");
    }

    // 6. INSERT
    $sql = "INSERT INTO schedule (
        teacher_id, subject, units, class_type, 
        time_in, time_out, day_of_week, room, 
        course_year, semester, school_year, schedule_type
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $insert = $pdo->prepare($sql);
    $insert->execute([
        $target_teacher_id,
        $_POST['subject'],
        $_POST['units'],
        $_POST['class_type'],
        $_POST['time_in'],
        $_POST['time_out'],
        $_POST['day_of_week'],
        $_POST['room'],
        $_POST['course_year'],
        $_POST['semester'],
        $_POST['school_year'],
        $schedule_type 
    ]);

    // 7. AUDIT LOG
    $tStmt = $pdo->prepare("SELECT last_name FROM user WHERE user_id = ?");
    $tStmt->execute([$target_teacher_id]);
    $tName = $tStmt->fetchColumn();

    logAudit($_SESSION['user_id'], "CREATED_SCHEDULE", "Added $schedule_type ($tName): {$_POST['subject']}");

    echo json_encode(['status' => 'success']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>