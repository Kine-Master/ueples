<?php
// backend/schedule/get_available_teachers.php
// Returns teachers who can teach the given subject AND availability info.
header('Content-Type: application/json');
require_once __DIR__ . '/../config/functions.php';
requireRoleApi(['admin', 'secretary']);

try {
    $subject_id     = (int)($_GET['subject_id']     ?? 0);
    $school_year_id = (int)($_GET['school_year_id'] ?? 0);
    $semester       = trim($_GET['semester']        ?? '');
    $day_of_week    = trim($_GET['day_of_week']     ?? '');
    // Accept both naming conventions
    $time_in        = trim($_GET['time_in']  ?? $_GET['start_time'] ?? '');
    $time_out       = trim($_GET['time_out'] ?? $_GET['end_time']   ?? '');
    $exclude_id     = (int)($_GET['exclude_schedule_id'] ?? 0);
    $specialists_only = isset($_GET['specialists_only']) && $_GET['specialists_only'] === '1';

    if ($specialists_only && !$subject_id) {
        throw new Exception("subject_id is required to filter by specialists.");
    }

    // Auto-resolve active school year if not provided
    if (!$school_year_id) {
        $sySmt = $pdo->query("SELECT school_year_id FROM school_year WHERE is_active = 1 LIMIT 1");
        $syRow = $sySmt->fetch(PDO::FETCH_ASSOC);
        $school_year_id = $syRow ? (int)$syRow['school_year_id'] : 0;
    }

    if ($specialists_only) {
        // Only fetch teachers who have the subject in teacher_subject
        $sql = "SELECT u.user_id, u.first_name, u.last_name, u.academic_rank, u.department, 1 AS is_specialist
                FROM teacher_subject ts
                JOIN user u ON ts.teacher_id = u.user_id
                WHERE ts.subject_id = ? AND u.is_active = 1 AND u.role_id = 4
                ORDER BY u.last_name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$subject_id]);
    } else {
        // All active teachers; mark which ones are specialists
        $sql = "SELECT u.user_id, u.first_name, u.last_name, u.academic_rank, u.department,
                       IF(ts.subject_id IS NOT NULL, 1, 0) AS is_specialist
                FROM user u
                LEFT JOIN teacher_subject ts ON ts.teacher_id = u.user_id AND ts.subject_id = ?
                WHERE u.is_active = 1 AND u.role_id = 4
                ORDER BY u.last_name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$subject_id]);
    }
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If time info provided, mark conflict status
    if ($school_year_id && $semester && $day_of_week && $time_in && $time_out) {
        $conflictSql = "SELECT 1 FROM schedule
                        WHERE teacher_id = ? AND school_year_id = ? AND semester = ?
                          AND day_of_week = ? AND is_active = 1
                          AND time_in < ? AND time_out > ?" .
                       ($exclude_id ? " AND schedule_id != $exclude_id" : "");
        $conflictStmt = $pdo->prepare($conflictSql);

        foreach ($teachers as $key => $teacher) {
            $conflictStmt->execute([
                $teacher['user_id'], $school_year_id, $semester,
                $day_of_week, $time_out, $time_in
            ]);
            $teachers[$key]['is_available'] = !$conflictStmt->fetch();
        }
    } else {
        foreach ($teachers as &$t) { $t['is_available'] = null; }
    }

    echo json_encode(['status' => 'success', 'data' => $teachers]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
