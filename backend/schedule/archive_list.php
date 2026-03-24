<?php
// backend/schedule/archive_list.php — Admin-only. Lists all soft-deleted (is_active=0) schedules.
header('Content-Type: application/json');
require_once __DIR__ . '/../config/functions.php';
requireRoleApi('admin');

try {
    $search  = $_GET['search']  ?? '';
    $type    = $_GET['schedule_type'] ?? '';
    $sort_by = in_array($_GET['sort_by'] ?? '', ['date_created','school_year','subject_name','teacher_name','schedule_type'])
               ? $_GET['sort_by'] : 'date_created';
    $order   = strtoupper($_GET['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

    $sql = "SELECT s.schedule_id, s.schedule_type, s.semester, s.day_of_week, s.time_in, s.time_out,
                   s.date_created, s.last_modified,
                   CONCAT(u.last_name,', ',u.first_name) AS teacher_name,
                   sy.label AS school_year,
                   COALESCE(sub.name, s.coed_subject) AS subject_name,
                   cs.section_name, gl.name AS grade_name,
                   r.room_name, b.name AS building_name,
                   s.coed_room, s.coed_building
            FROM schedule s
            JOIN user u ON s.teacher_id = u.user_id
            JOIN school_year sy ON s.school_year_id = sy.school_year_id
            LEFT JOIN class_section cs ON s.class_section_id = cs.class_section_id
            LEFT JOIN grade_level gl ON cs.grade_level_id = gl.grade_level_id
            LEFT JOIN subject sub ON s.subject_id = sub.subject_id
            LEFT JOIN room r ON s.room_id = r.room_id
            LEFT JOIN building b ON r.building_id = b.building_id
            WHERE s.is_active = 0";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (u.last_name LIKE ? OR u.first_name LIKE ? OR sub.name LIKE ? OR s.coed_subject LIKE ?)";
        $t = "%$search%"; $params = array_merge($params, [$t,$t,$t,$t]);
    }
    if (!empty($type)) { $sql .= " AND s.schedule_type = ?"; $params[] = $type; }
    $sql .= " ORDER BY $sort_by $order";

    $stmt = $pdo->prepare($sql); $stmt->execute($params);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>