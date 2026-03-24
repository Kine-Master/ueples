<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

// 1. Validation
if (!isset($_GET['teacher_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Teacher ID required']);
    exit;
}

$teacher_id = $_GET['teacher_id'];

// 2. Capture Optional Filter Parameters
// If the frontend sends these, we filter. If not, we ignore.
$semester = $_GET['semester'] ?? '';
$school_year = $_GET['school_year'] ?? '';

// 3. Capture Optional Sort Parameters
// Default: Sort by 'time_in' (Critical for the Grid View)
$sort_by = $_GET['sort_by'] ?? 'time_in'; 
$order = strtoupper($_GET['order'] ?? 'ASC');

// 4. Security: Whitelist allowed columns to prevent SQL Injection
// These are the only columns allowed to be sorted by.
$allowed_sorts = ['schedule_type', 'semester', 'created_at', 'school_year', 'time_in', 'subject'];
if (!in_array($sort_by, $allowed_sorts)) {
    $sort_by = 'time_in'; // Fallback to safe default
}
// Validate Order
if ($order !== 'ASC' && $order !== 'DESC') {
    $order = 'ASC';
}

try {
    // 5. Build the Dynamic SQL Query
    // Base: Always filter by Teacher and Active Status
    $sql = "SELECT s.*, u.first_name, u.last_name 
            FROM schedule s
            JOIN user u ON s.teacher_id = u.user_id
            WHERE s.teacher_id = ? 
            AND s.is_active = 1"; 
    
    $params = [$teacher_id];

    // --- DYNAMIC FILTERING ---
    if (!empty($semester)) {
        $sql .= " AND s.semester = ?";
        $params[] = $semester;
    }

    if (!empty($school_year)) {
        $sql .= " AND s.school_year = ?";
        $params[] = $school_year;
    }

    // --- DYNAMIC SORTING ---
    $sql .= " ORDER BY s.$sort_by $order";
    
    // Secondary Sort: If sorting by Type or Semester, keep times ordered within those groups
    if ($sort_by !== 'time_in') {
        $sql .= ", s.time_in ASC";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'schedules' => $schedules]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>