<?php
// backend/dashboard/stats.php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/functions.php';
requireRoleApi(['admin', 'principal', 'secretary', 'teacher']);

$user_id = (int)$_SESSION['user_id'];
$role    = $_SESSION['role'];
$data    = [];

try {
    $activeSY = getActiveSchoolYear(); // ['school_year_id' => X, 'label' => '2025-2026']
    $syId     = $activeSY['school_year_id'] ?? null;

    // ── ADMIN ────────────────────────────────────────────────
    if ($role === 'admin') {
        // User counts per role
        $stmt = $pdo->query("SELECT r.role_name, COUNT(u.user_id) AS total
                             FROM role r LEFT JOIN user u ON r.role_id = u.role_id AND u.is_active = 1
                             GROUP BY r.role_id ORDER BY r.role_id");
        $roleCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->query("SELECT COUNT(*) FROM user WHERE is_active = 1");
        $totalUsers = (int)$stmt->fetchColumn();

        // Recent audit logs (10)
        $stmt = $pdo->query("SELECT a.log_id, a.user_action, a.details, a.timestamp, a.ip_address,
                                    u.first_name, u.last_name, r.role_name
                             FROM audit_log a
                             LEFT JOIN user u ON a.user_id = u.user_id
                             LEFT JOIN role r ON u.role_id = r.role_id
                             ORDER BY a.timestamp DESC LIMIT 10");
        $recentLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [
            'role'         => 'admin',
            'active_sy'    => $activeSY,
            'total_users'  => $totalUsers,
            'role_counts'  => $roleCounts,
            'recent_logs'  => $recentLogs,
        ];
    }

    // ── PRINCIPAL ────────────────────────────────────────────
    elseif ($role === 'principal') {
        // Total active teachers
        $stmt = $pdo->query("SELECT COUNT(*) FROM user WHERE role_id = " . ROLE_TEACHER . " AND is_active = 1");
        $totalTeachers = (int)$stmt->fetchColumn();

        // Total students (current SY)
        if ($syId) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM student s
                                   JOIN class_section cs ON s.class_section_id = cs.class_section_id
                                   WHERE cs.school_year_id = ? AND s.is_active = 1");
            $stmt->execute([$syId]);
        } else {
            $stmt = $pdo->query("SELECT COUNT(*) FROM student WHERE is_active = 1");
        }
        $totalStudents = (int)$stmt->fetchColumn();

        // Total active schedules (current SY)
        if ($syId) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM schedule WHERE school_year_id = ? AND is_active = 1");
            $stmt->execute([$syId]);
        } else {
            $stmt = $pdo->query("SELECT COUNT(*) FROM schedule WHERE is_active = 1");
        }
        $totalSchedules = (int)$stmt->fetchColumn();

        // Students per grade level (current SY)
        $gradeQuery = $syId
            ? "SELECT gl.name AS grade, COUNT(s.student_id) AS total
               FROM grade_level gl
               LEFT JOIN class_section cs ON cs.grade_level_id = gl.grade_level_id AND cs.school_year_id = $syId
               LEFT JOIN student s ON s.class_section_id = cs.class_section_id AND s.is_active = 1
               GROUP BY gl.grade_level_id ORDER BY gl.level_order"
            : "SELECT gl.name AS grade, 0 AS total FROM grade_level gl ORDER BY gl.level_order";
        $stmt = $pdo->query($gradeQuery);
        $studentsPerGrade = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Building & room counts
        $stmt = $pdo->query("SELECT COUNT(*) FROM building WHERE is_active = 1");
        $totalBuildings = (int)$stmt->fetchColumn();
        $stmt = $pdo->query("SELECT COUNT(*) FROM room WHERE is_active = 1");
        $totalRooms = (int)$stmt->fetchColumn();

        // Tracking Board: Today's active classes school-wide
        $today = date('l');
        $stmt = $pdo->prepare(
            "SELECT s.time_in, s.time_out, s.day_of_week, s.schedule_type,
                    u.first_name, u.last_name, CONCAT(u.first_name, ' ', u.last_name) AS teacher_name,
                    sub.name AS subject_name, s.coed_subject,
                    cs.section_name, gl.name AS grade_name,
                    r.room_name, b.name AS building_name,
                    s.coed_room, s.coed_building
             FROM schedule s
             JOIN user u ON s.teacher_id = u.user_id
             LEFT JOIN class_section cs ON s.class_section_id = cs.class_section_id
             LEFT JOIN grade_level gl ON cs.grade_level_id = gl.grade_level_id
             LEFT JOIN subject sub ON s.subject_id = sub.subject_id
             LEFT JOIN room r ON s.room_id = r.room_id
             LEFT JOIN building b ON r.building_id = b.building_id
             WHERE s.is_active = 1 AND s.day_of_week = ?" . ($syId ? " AND s.school_year_id = ?" : "") . "
             ORDER BY s.time_in ASC"
        );
        $params = $syId ? [$today, $syId] : [$today];
        $stmt->execute($params);
        $trackingBoard = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [
            'role'               => 'principal',
            'active_sy'          => $activeSY,
            'total_teachers'     => $totalTeachers,
            'total_students'     => $totalStudents,
            'total_schedules'    => $totalSchedules,
            'students_per_grade' => $studentsPerGrade,
            'total_buildings'    => $totalBuildings,
            'total_rooms'        => $totalRooms,
            'tracking_board'     => $trackingBoard,
        ];
    }

    // ── SECRETARY ────────────────────────────────────────────
    elseif ($role === 'secretary') {
        $totalTeachers = (int)$pdo->query("SELECT COUNT(*) FROM user WHERE role_id = " . ROLE_TEACHER . " AND is_active = 1")->fetchColumn();

        if ($syId) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM class_section WHERE school_year_id = ? AND is_active = 1");
            $stmt->execute([$syId]);
            $totalSections = (int)$stmt->fetchColumn();

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM schedule WHERE school_year_id = ? AND is_active = 1");
            $stmt->execute([$syId]);
            $totalSchedules = (int)$stmt->fetchColumn();

            // Recent schedules (5)
            $stmt = $pdo->prepare(
                "SELECT s.schedule_id, s.schedule_type, s.day_of_week, s.time_in, s.time_out,
                        s.date_created, u.first_name, u.last_name,
                        cs.section_name, gl.name AS grade_name,
                        sub.name AS subject_name, s.coed_subject
                 FROM schedule s
                 JOIN user u ON s.teacher_id = u.user_id
                 LEFT JOIN class_section cs ON s.class_section_id = cs.class_section_id
                 LEFT JOIN grade_level gl ON cs.grade_level_id = gl.grade_level_id
                 LEFT JOIN subject sub ON s.subject_id = sub.subject_id
                 WHERE s.school_year_id = ? AND s.is_active = 1
                 ORDER BY s.date_created DESC LIMIT 5");
            $stmt->execute([$syId]);
            $recentSchedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $totalSections = 0;
            $totalSchedules = 0;
            $recentSchedules = [];
        }

        $data = [
            'role'             => 'secretary',
            'active_sy'        => $activeSY,
            'total_teachers'   => $totalTeachers,
            'total_sections'   => $totalSections,
            'total_schedules'  => $totalSchedules,
            'recent_schedules' => $recentSchedules,
        ];
    }

    // ── TEACHER ──────────────────────────────────────────────
    elseif ($role === 'teacher') {
        // Own class sections (current SY)
        if ($syId) {
            $stmt = $pdo->prepare(
                "SELECT cs.class_section_id, cs.section_name, gl.name AS grade_name,
                        COUNT(st.student_id) AS student_count
                 FROM class_section cs
                 JOIN grade_level gl ON cs.grade_level_id = gl.grade_level_id
                 LEFT JOIN student st ON st.class_section_id = cs.class_section_id AND st.is_active = 1
                 WHERE cs.school_year_id = ? AND cs.is_active = 1
                   AND EXISTS (
                       SELECT 1 FROM schedule sc
                       WHERE sc.class_section_id = cs.class_section_id
                         AND sc.teacher_id = ? AND sc.is_active = 1
                   )
                 GROUP BY cs.class_section_id");
            $stmt->execute([$syId, $user_id]);
            $myClasses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $myClasses = [];
        }

        // Total students in own classes
        $totalStudents = array_sum(array_column($myClasses, 'student_count'));

        // Assigned schedule count
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM schedule WHERE teacher_id = ? AND is_active = 1" . ($syId ? " AND school_year_id = ?" : ""));
        $params = $syId ? [$user_id, $syId] : [$user_id];
        $stmt->execute($params);
        $totalSchedules = (int)$stmt->fetchColumn();

        // Today's schedule
        $today = date('l');
        $stmt  = $pdo->prepare(
            "SELECT s.time_in, s.time_out, s.day_of_week, s.schedule_type,
                    sub.name AS subject_name, s.coed_subject,
                    cs.section_name, gl.name AS grade_name,
                    r.room_name, b.name AS building_name,
                    s.coed_room, s.coed_building
             FROM schedule s
             LEFT JOIN class_section cs ON s.class_section_id = cs.class_section_id
             LEFT JOIN grade_level gl ON cs.grade_level_id = gl.grade_level_id
             LEFT JOIN subject sub ON s.subject_id = sub.subject_id
             LEFT JOIN room r ON s.room_id = r.room_id
             LEFT JOIN building b ON r.building_id = b.building_id
             WHERE s.teacher_id = ? AND s.is_active = 1 AND s.day_of_week = ?
             ORDER BY s.time_in ASC");
        $stmt->execute([$user_id, $today]);
        $todaySchedule = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Advisory classes count
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM class_section WHERE adviser_id = ? AND is_active = 1" . ($syId ? " AND school_year_id = ?" : ""));
        $advParams = $syId ? [$user_id, $syId] : [$user_id];
        $stmt->execute($advParams);
        $advisoryClasses = (int)$stmt->fetchColumn();

        $data = [
            'role'            => 'teacher',
            'active_sy'       => $activeSY,
            'current_day'     => $today,
            'my_classes'      => $myClasses,
            'total_students'  => $totalStudents,
            'total_schedules' => $totalSchedules,
            'today_schedule'  => $todaySchedule,
            'teacher_stats'   => [
                'advisory_classes' => $advisoryClasses,
                'total_students'   => $totalStudents,
                'weekly_schedules' => $totalSchedules,
            ],
        ];
    }

    echo json_encode(['status' => 'success', 'data' => $data]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>