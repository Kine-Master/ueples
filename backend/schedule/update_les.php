<?php
// backend/schedule/update_les.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/functions.php';
requireRoleApi('secretary');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }

try {
    $schedule_id      = (int)($_POST['schedule_id']      ?? 0);
    $class_section_id = (int)($_POST['class_section_id'] ?? 0);
    $subject_id       = (int)($_POST['subject_id']       ?? 0);
    $teacher_id       = (int)($_POST['teacher_id']       ?? 0);
    $room_id          = (int)($_POST['room_id']          ?? 0);
    $semester         = trim($_POST['semester']          ?? '');
    $day_of_week      = trim($_POST['day_of_week']       ?? '');
    $time_in          = trim($_POST['time_in']           ?? '');
    $time_out         = trim($_POST['time_out']          ?? '');

    if (!$schedule_id || !$class_section_id || !$subject_id || !$teacher_id ||
        !$room_id || !$semester || !$day_of_week || !$time_in || !$time_out) {
        throw new Exception("All fields including schedule_id are required.");
    }
    if ($time_in >= $time_out) throw new Exception("Time Out must be after Time In.");

    // Validate it's actually an LES schedule
    $stmt = $pdo->prepare("SELECT school_year_id FROM schedule WHERE schedule_id = ? AND schedule_type = 'LES' AND is_active = 1");
    $stmt->execute([$schedule_id]);
    $sched = $stmt->fetch();
    if (!$sched) throw new Exception("LES schedule not found.");

    // Teacher specialization check
    $stmt = $pdo->prepare("SELECT 1 FROM teacher_subject WHERE teacher_id = ? AND subject_id = ?");
    $stmt->execute([$teacher_id, $subject_id]);
    if (!$stmt->fetch()) throw new Exception("This teacher is not assigned to teach this subject.");

    // Conflict check (exclude self)
    $stmt = $pdo->prepare(
        "SELECT schedule_id FROM schedule
         WHERE is_active = 1 AND schedule_id != ?
           AND school_year_id = ? AND semester = ? AND day_of_week = ?
           AND (teacher_id = ? OR room_id = ?)
           AND (time_in < ? AND time_out > ?)"
    );
    $stmt->execute([$schedule_id, $sched['school_year_id'], $semester, $day_of_week,
                    $teacher_id, $room_id, $time_out, $time_in]);
    if ($conflict = $stmt->fetch()) {
        throw new Exception("Conflict detected with schedule #" . $conflict['schedule_id'] . " at this time/room.");
    }

    $pdo->prepare(
        "UPDATE schedule SET class_section_id=?, subject_id=?, teacher_id=?, room_id=?,
                             semester=?, day_of_week=?, time_in=?, time_out=?
         WHERE schedule_id=?"
    )->execute([$class_section_id, $subject_id, $teacher_id, $room_id,
                $semester, $day_of_week, $time_in, $time_out, $schedule_id]);

    logAudit($_SESSION['user_id'], 'UPDATE_SCHEDULE_LES', "Updated LES schedule #$schedule_id");
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
