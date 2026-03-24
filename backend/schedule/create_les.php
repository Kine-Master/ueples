<?php
// backend/schedule/create_les.php
// Creates an internal (LES) schedule using FK references via the guided dropdown flow.
header('Content-Type: application/json');
require_once __DIR__ . '/../config/functions.php';
requireRoleApi('secretary');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }

try {
    // Required fields
    $school_year_id   = (int)($_POST['school_year_id']   ?? 0);
    $semester         = trim($_POST['semester']          ?? '');
    $class_section_id = (int)($_POST['class_section_id'] ?? 0);
    $subject_id       = (int)($_POST['subject_id']       ?? 0);
    $teacher_id       = (int)($_POST['teacher_id']       ?? 0);
    $room_id          = (int)($_POST['room_id']          ?? 0);
    $day_of_week      = trim($_POST['day_of_week']       ?? '');
    $time_in          = trim($_POST['time_in']  ?? $_POST['start_time'] ?? '');
    $time_out         = trim($_POST['time_out'] ?? $_POST['end_time']   ?? '');

    if (!$school_year_id || !$semester || !$class_section_id || !$subject_id ||
        !$teacher_id || !$room_id || !$day_of_week || !$time_in || !$time_out) {
        throw new Exception("All fields are required for an internal (LES) schedule.");
    }
    if (!in_array($semester, ['1', '2', 'Summer'])) throw new Exception("Invalid semester value.");
    if ($time_in >= $time_out) throw new Exception("Time Out must be after Time In.");

    // (Specialist check is advisory — all active teachers are allowed to be assigned)

    $roomNameStmt = $pdo->prepare("SELECT room_name FROM room WHERE room_id = ? LIMIT 1");
    $roomNameStmt->execute([$room_id]);
    $room_name = trim((string)$roomNameStmt->fetchColumn());

    // Conflict check:
    // - Teacher conflict across LES/COED by teacher_id
    // - Room conflict across LES by room_id
    // - Room conflict across COED by matching selected room name against coed_room text
    $sql_conflict = "SELECT s.schedule_id, s.schedule_type,
                            CASE
                              WHEN s.teacher_id = ? THEN 'teacher'
                              ELSE 'room'
                            END AS conflict_type
                     FROM schedule s
                     WHERE s.is_active = 1
                       AND s.school_year_id = ?
                       AND s.semester = ?
                       AND s.day_of_week = ?
                       AND (s.time_in < ? AND s.time_out > ?)
                       AND (
                            s.teacher_id = ?
                            OR s.room_id = ?
                            OR (? <> '' AND s.schedule_type = 'COED' AND LOWER(TRIM(s.coed_room)) = LOWER(TRIM(?)))
                       )
                     LIMIT 1";
    $stmt = $pdo->prepare($sql_conflict);
    $stmt->execute([$teacher_id, $school_year_id, $semester, $day_of_week, $time_out, $time_in, $teacher_id, $room_id, $room_name, $room_name]);
    if ($conflict = $stmt->fetch()) {
        $conflictScope = strtoupper($conflict['schedule_type']);
        $reason = $conflict['conflict_type'] === 'teacher' ? 'Teacher' : 'Room';
        throw new Exception("$reason conflict with existing $conflictScope schedule. (Schedule #" . $conflict['schedule_id'] . ")");
    }

    // Insert LES schedule
    $pdo->prepare(
        "INSERT INTO schedule (schedule_type, school_year_id, semester, teacher_id, day_of_week,
                               time_in, time_out, class_section_id, subject_id, room_id)
         VALUES ('LES', ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    )->execute([$school_year_id, $semester, $teacher_id, $day_of_week, $time_in, $time_out,
                $class_section_id, $subject_id, $room_id]);

    $id = $pdo->lastInsertId();

    // Audit with context names
    $stmt = $pdo->prepare("SELECT name FROM subject WHERE subject_id = ?"); $stmt->execute([$subject_id]);
    $subName = $stmt->fetchColumn();
    $stmt = $pdo->prepare("SELECT CONCAT(last_name,', ',first_name) FROM user WHERE user_id = ?"); $stmt->execute([$teacher_id]);
    $teacherName = $stmt->fetchColumn();
    logAudit($_SESSION['user_id'], 'CREATE_SCHEDULE_LES', "LES schedule #$id: '$subName' → $teacherName on $day_of_week $time_in-$time_out");

    echo json_encode(['status' => 'success', 'schedule_id' => $id]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
