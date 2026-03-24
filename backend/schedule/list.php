<?php
// backend/schedule/list.php
// Returns schedules with full context (LES: joins to class, subject, room; COED: free-text fields).
header('Content-Type: application/json');
require_once __DIR__ . '/../config/functions.php';
requireRoleApi(['admin', 'principal', 'secretary', 'teacher']);

try {
    $sy_filter      = (int)($_GET['school_year_id'] ?? 0);
    $type_filter    = $_GET['schedule_type'] ?? '';
    $teacher_filter = (int)($_GET['teacher_id'] ?? 0);
    $day_filter     = $_GET['day_of_week'] ?? '';
    $sem_filter     = $_GET['semester'] ?? '';
    $active_only    = ($_GET['active'] ?? '1') === '1';

    // Teachers can only see their own schedules
    if ($_SESSION['role'] === 'teacher') {
        $teacher_filter = (int)$_SESSION['user_id'];
    }

    $sql = "SELECT s.schedule_id, s.schedule_type, s.semester, s.day_of_week,
                   s.time_in, s.time_out, s.is_active, s.date_created,
                   u.user_id AS teacher_id,
                   CONCAT(u.last_name, ', ', u.first_name) AS teacher_name,
                   sy.label AS school_year,
                   -- LES fields
                   cs.class_section_id, cs.section_name,
                   gl.grade_level_id, gl.name AS grade_name,
                   sub.subject_id, sub.name AS subject_name, sub.units,
                   r.room_id, r.room_name, r.capacity AS room_capacity,
                   b.name AS building_name,
                   -- COED fields
                   s.coed_subject, s.coed_grade_level, s.coed_building, s.coed_room, s.coed_units
            FROM schedule s
            JOIN user u       ON s.teacher_id       = u.user_id
            JOIN school_year sy ON s.school_year_id = sy.school_year_id
            LEFT JOIN class_section cs ON s.class_section_id = cs.class_section_id
            LEFT JOIN grade_level gl   ON cs.grade_level_id  = gl.grade_level_id
            LEFT JOIN subject sub      ON s.subject_id       = sub.subject_id
            LEFT JOIN room r           ON s.room_id          = r.room_id
            LEFT JOIN building b       ON r.building_id      = b.building_id
            WHERE 1=1";
    $params = [];

    if ($active_only)      { $sql .= " AND s.is_active = 1"; }
    if ($sy_filter)        { $sql .= " AND s.school_year_id = ?"; $params[] = $sy_filter; }
    if (!empty($type_filter)) { $sql .= " AND s.schedule_type = ?"; $params[] = $type_filter; }
    if ($teacher_filter)   { $sql .= " AND s.teacher_id = ?"; $params[] = $teacher_filter; }
    if (!empty($day_filter))  { $sql .= " AND s.day_of_week = ?"; $params[] = $day_filter; }
    if (!empty($sem_filter))  { $sql .= " AND s.semester = ?"; $params[] = $sem_filter; }

    $sql .= " ORDER BY s.day_of_week, s.time_in";
    $stmt = $pdo->prepare($sql); $stmt->execute($params);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
