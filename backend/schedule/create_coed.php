<?php
// backend/schedule/create_coed.php
// Creates an external (COED) schedule using free-text fields.
header('Content-Type: application/json');
require_once __DIR__ . '/../config/functions.php';
requireRoleApi('secretary');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }

try {
    $school_year_id = (int)($_POST['school_year_id'] ?? 0);
    $semester       = trim($_POST['semester']        ?? '');
    $teacher_id     = (int)($_POST['teacher_id']     ?? 0);
    $day_of_week    = trim($_POST['day_of_week']     ?? '');
    $time_in        = trim($_POST['time_in']          ?? $_POST['start_time']     ?? '');
    $time_out       = trim($_POST['time_out']         ?? $_POST['end_time']       ?? '');
    $room_id        = (int)($_POST['room_id'] ?? 0);
    $coed_subject   = trim($_POST['coed_subject']     ?? $_POST['coed_subject_name'] ?? '');
    $coed_grade     = trim($_POST['coed_grade_level'] ?? $_POST['coed_course_year']  ?? '');
    $coed_building  = trim($_POST['coed_building']    ?? '');
    $coed_room      = trim($_POST['coed_room']        ?? '');
    $coed_units     = (float)($_POST['coed_units']    ?? 0);

    if (!$school_year_id || !$semester || !$teacher_id || !$day_of_week || !$time_in || !$time_out || !$coed_subject) {
        throw new Exception("school_year_id, semester, teacher_id, day, time_in, time_out, and subject name are required.");
    }
    if (!in_array($semester, ['1', '2', 'Summer'])) throw new Exception("Invalid semester value.");
    if ($time_in >= $time_out) throw new Exception("Time Out must be after Time In.");

    // Conflict check:
    // - Teacher conflict across LES/COED by teacher_id
    // - Room conflict across COED by coed_room text
    // - Room conflict across LES by matching LES room_name against coed_room text
    $cfSql = "SELECT s.schedule_id, s.schedule_type,
                     CASE
                       WHEN s.teacher_id = ? THEN 'teacher'
                       ELSE 'room'
                     END AS conflict_type
              FROM schedule s
              LEFT JOIN room r ON r.room_id = s.room_id
              WHERE s.is_active = 1
                AND s.school_year_id = ?
                AND s.semester = ?
                AND s.day_of_week = ?
                AND s.time_in < ? AND s.time_out > ?
                AND (
                     s.teacher_id = ?
                     OR (? > 0 AND s.room_id = ?)
                     OR (? <> '' AND (
                          LOWER(TRIM(s.coed_room)) = LOWER(TRIM(?))
                          OR LOWER(TRIM(r.room_name)) = LOWER(TRIM(?))
                     ))
                )
              LIMIT 1";
    $cfStmt = $pdo->prepare($cfSql);
    $cfStmt->execute([
        $teacher_id,
        $school_year_id,
        $semester,
        $day_of_week,
        $time_out,
        $time_in,
        $teacher_id,
        $room_id,
        $room_id,
        $coed_room,
        $coed_room,
        $coed_room
    ]);
    if ($cf = $cfStmt->fetch()) {
        $conflictScope = strtoupper($cf['schedule_type']);
        $reason = $cf['conflict_type'] === 'teacher' ? 'Teacher' : 'Room';
        throw new Exception("$reason conflict with existing $conflictScope schedule. (Schedule #" . $cf['schedule_id'] . ")");
    }

    $pdo->prepare(
        "INSERT INTO schedule (schedule_type, school_year_id, semester, teacher_id, day_of_week,
                               time_in, time_out, room_id, coed_subject, coed_grade_level, coed_building, coed_room, coed_units)
         VALUES ('COED', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    )->execute([$school_year_id, $semester, $teacher_id, $day_of_week, $time_in, $time_out,
                $room_id ?: null, $coed_subject, $coed_grade ?: null, $coed_building ?: null, $coed_room ?: null, $coed_units ?: null]);

    $id = $pdo->lastInsertId();
    $stmt = $pdo->prepare("SELECT CONCAT(last_name,', ',first_name) FROM user WHERE user_id = ?"); $stmt->execute([$teacher_id]);
    logAudit($_SESSION['user_id'], 'CREATE_SCHEDULE_COED', "COED schedule #$id: '$coed_subject' → {$stmt->fetchColumn()} on $day_of_week $time_in-$time_out");

    echo json_encode(['status' => 'success', 'schedule_id' => $id]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
