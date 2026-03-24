<?php
// backend/schedule/update_coed.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/functions.php';
requireRoleApi('secretary');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['status'=>'error','message'=>'POST only']); exit; }

try {
    $schedule_id  = (int)($_POST['schedule_id']  ?? 0);
    $teacher_id   = (int)($_POST['teacher_id']   ?? 0);
    $semester     = trim($_POST['semester']       ?? '');
    $day_of_week  = trim($_POST['day_of_week']    ?? '');
    $time_in      = trim($_POST['time_in']        ?? '');
    $time_out     = trim($_POST['time_out']       ?? '');
    $coed_subject = trim($_POST['coed_subject']   ?? '');

    if (!$schedule_id || !$teacher_id || !$semester || !$day_of_week || !$time_in || !$time_out || !$coed_subject) {
        throw new Exception("Required fields: schedule_id, teacher_id, semester, day, time_in, time_out, subject name.");
    }
    if ($time_in >= $time_out) throw new Exception("Time Out must be after Time In.");

    $stmt = $pdo->prepare("SELECT school_year_id FROM schedule WHERE schedule_id = ? AND schedule_type = 'COED' AND is_active = 1");
    $stmt->execute([$schedule_id]);
    $sched = $stmt->fetch();
    if (!$sched) throw new Exception("COED schedule not found.");

    // Teacher conflict (exclude self)
    $stmt = $pdo->prepare(
        "SELECT schedule_id FROM schedule
         WHERE is_active = 1 AND schedule_id != ?
           AND school_year_id = ? AND semester = ? AND day_of_week = ?
           AND teacher_id = ? AND time_in < ? AND time_out > ?"
    );
    $stmt->execute([$schedule_id, $sched['school_year_id'], $semester, $day_of_week, $teacher_id, $time_out, $time_in]);
    if ($conflict = $stmt->fetch()) {
        throw new Exception("Conflict: teacher already has schedule #" . $conflict['schedule_id'] . " at this time.");
    }

    $pdo->prepare(
        "UPDATE schedule SET teacher_id=?, semester=?, day_of_week=?, time_in=?, time_out=?,
                             coed_subject=?, coed_grade_level=?, coed_building=?, coed_room=?, coed_units=?
         WHERE schedule_id=?"
    )->execute([
        $teacher_id, $semester, $day_of_week, $time_in, $time_out,
        $coed_subject,
        trim($_POST['coed_grade_level'] ?? '') ?: null,
        trim($_POST['coed_building']    ?? '') ?: null,
        trim($_POST['coed_room']        ?? '') ?: null,
        (float)($_POST['coed_units']    ?? 0) ?: null,
        $schedule_id
    ]);

    logAudit($_SESSION['user_id'], 'UPDATE_SCHEDULE_COED', "Updated COED schedule #$schedule_id");
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
