<?php
// backend/schedule/get_room_slots.php
// Returns all occupied time slots for a given room on a given day/semester.
// school_year_id is optional — falls back to the active SY.
header('Content-Type: application/json');
require_once __DIR__ . '/../config/functions.php';
requireRoleApi(['admin', 'principal', 'secretary', 'teacher']);

try {
    $room_id        = (int)($_GET['room_id']        ?? 0);
    $school_year_id = (int)($_GET['school_year_id'] ?? 0);
    $semester       = trim($_GET['semester']        ?? '');
    $day_of_week    = trim($_GET['day_of_week']     ?? '');
    $exclude_id     = (int)($_GET['exclude_schedule_id'] ?? 0);

    if (!$room_id || !$day_of_week) {
        throw new Exception("room_id and day_of_week are required.");
    }

    // Auto-resolve active school year if not provided
    if (!$school_year_id) {
        $sySmt = $pdo->query("SELECT school_year_id FROM school_year WHERE is_active = 1 LIMIT 1");
        $syRow = $sySmt->fetch(PDO::FETCH_ASSOC);
        $school_year_id = $syRow ? (int)$syRow['school_year_id'] : 0;
    }

    $sql = "SELECT s.schedule_id, s.schedule_type, s.semester,
                   s.time_in  AS start_time,
                   s.time_out AS end_time,
                   CONCAT(u.last_name, ', ', u.first_name) AS teacher_name,
                   sub.name AS subject_name,
                   s.coed_subject AS coed_subject_name,
                   cs.section_name
            FROM schedule s
            JOIN user u ON s.teacher_id = u.user_id
            LEFT JOIN subject sub ON s.subject_id = sub.subject_id
            LEFT JOIN class_section cs ON s.class_section_id = cs.class_section_id
            WHERE s.room_id = ? AND s.day_of_week = ? AND s.is_active = 1";
    $params = [$room_id, $day_of_week];

    if ($school_year_id) { $sql .= " AND s.school_year_id = ?"; $params[] = $school_year_id; }
    if (!empty($semester)) { $sql .= " AND s.semester = ?"; $params[] = $semester; }
    if ($exclude_id) { $sql .= " AND s.schedule_id != ?"; $params[] = $exclude_id; }
    $sql .= " ORDER BY s.time_in";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $slots = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return room capacity alongside
    $rStmt = $pdo->prepare("SELECT room_name, capacity FROM room WHERE room_id = ?");
    $rStmt->execute([$room_id]);
    $room = $rStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => ['slots' => $slots, 'capacity' => $room['capacity'] ?? null, 'room_name' => $room['room_name'] ?? '']]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
