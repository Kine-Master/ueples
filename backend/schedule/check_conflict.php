<?php
// backend/schedule/check_conflict.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

// Allow GET or POST for flexibility
$data = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;

// Required fields
$required = ['teacher_id', 'day_of_week', 'time_in', 'time_out', 'room', 'semester', 'school_year'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode(['status' => 'error', 'message' => "Missing field: $field"]);
        exit;
    }
}

try {
    $teacher_id = $data['teacher_id'];
    $day        = $data['day_of_week'];
    $time_in    = $data['time_in'];
    $time_out   = $data['time_out'];
    $room       = $data['room'];
    $semester   = $data['semester'];
    $school_year= $data['school_year'];
    
    // Optional: Exclude a specific ID (used when editing a schedule so it doesn't conflict with itself)
    $exclude_id = $data['exclude_schedule_id'] ?? 0;

    // THE LOGIC:
    // Check if (Teacher is Busy) OR (Room is Occupied)
    // AND the time overlaps
    $sql = "SELECT * FROM schedule 
            WHERE is_active = 1 
            AND day_of_week = ? 
            AND semester = ? 
            AND school_year = ?
            AND schedule_id != ? 
            AND (
                (teacher_id = ?)  -- Condition 1: Teacher is already teaching
                OR 
                (room = ?)        -- Condition 2: Room is already taken
            )
            AND (
                time_in < ? AND time_out > ?  -- Condition 3: Time Overlap Logic
            )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $day, $semester, $school_year, $exclude_id,
        $teacher_id, $room,
        $time_out, $time_in // Note: Logic is (StartA < EndB) and (EndA > StartB)
    ]);

    $conflict = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($conflict) {
        // Construct a helpful error message
        $msg = "Conflict Detected!";
        if ($conflict['teacher_id'] == $teacher_id) {
            $msg .= " This teacher already has a class ({$conflict['subject']}) at this time.";
        } elseif ($conflict['room'] == $room) {
            $msg .= " Room {$room} is already occupied by another class ({$conflict['subject']}).";
        }
        
        echo json_encode(['status' => 'conflict', 'message' => $msg, 'data' => $conflict]);
    } else {
        echo json_encode(['status' => 'success', 'message' => 'No conflict']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>