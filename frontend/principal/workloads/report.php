<?php
require '../../../backend/config/db.php';
require '../../../backend/config/functions.php';
requireRole('principal');
header('Location: ../dashboard/index.php');
exit;

// 1. INPUT VALIDATION
if (!isset($_GET['teacher_id'])) {
    die("Error: Teacher ID is required.");
}
$teacher_id = $_GET['teacher_id']; 

// 2. CAPTURE FILTERS
$semester_filter = $_GET['semester'] ?? '';
$school_year_filter = $_GET['school_year'] ?? '';

// 3. FETCH TEACHER INFO
$stmt = $pdo->prepare("SELECT * FROM user WHERE user_id = ?");
$stmt->execute([$teacher_id]);
$teacher = $stmt->fetch();

if (!$teacher) {
    die("Error: Teacher not found.");
}

// 4. FETCH SECRETARY INFO (For "Prepared by" Signature)
$secStmt = $pdo->prepare("SELECT first_name, last_name FROM user WHERE user_id = ?");
$secStmt->execute([$_SESSION['user_id']]);
$secretary = $secStmt->fetch();
$secretary_name = $secretary ? strtoupper($secretary['first_name'] . ' ' . $secretary['last_name']) : "SECRETARY";

// 5. BUILD DYNAMIC QUERY
$sql = "SELECT * FROM schedule WHERE teacher_id = ? AND is_active = 1";
$params = [$teacher_id];

if (!empty($semester_filter)) {
    $sql .= " AND semester = ?";
    $params[] = $semester_filter;
}

if (!empty($school_year_filter)) {
    $sql .= " AND school_year = ?";
    $params[] = $school_year_filter;
}

$sql .= " ORDER BY schedule_type DESC, subject ASC, day_of_week ASC";

$schedStmt = $pdo->prepare($sql);
$schedStmt->execute($params);
$schedules = $schedStmt->fetchAll(PDO::FETCH_ASSOC);

// 6. DYNAMIC HEADER INFO
$displaySY = !empty($school_year_filter) 
    ? $school_year_filter 
    : (count($schedules) > 0 ? $schedules[0]['school_year'] : (date('Y') . "-" . (date('Y')+1)));

$semRaw = !empty($semester_filter) 
    ? $semester_filter 
    : (count($schedules) > 0 ? $schedules[0]['semester'] : 1);

if ($semRaw == '1') $semLabel = "1st Semester";
elseif ($semRaw == '2') $semLabel = "2nd Semester";
elseif ($semRaw == 'Summer') $semLabel = "Summer";
else $semLabel = "All Semesters";

// 7. FETCH PRINCIPAL
$pStmt = $pdo->prepare("SELECT first_name, last_name FROM user WHERE role_id = 1 AND is_active = 1 LIMIT 1");
$pStmt->execute();
$principal = $pStmt->fetch();
$principal_name = $principal ? strtoupper($principal['first_name'] . ' ' . $principal['last_name']) : "PRINCIPAL NOT ASSIGNED";

// 8. GROUP SCHEDULES BY SUBJECT + TIME TO AVOID DUPLICATE UNIT COUNTING
$grouped = [];
$totalHoursGlobal = 0;

foreach($schedules as $s) {
    // Group by subject + class_type + time (subjects with same time are grouped together)
    $key = $s['subject'] . '_' . $s['class_type'] . '_' . $s['time_in'] . '_' . $s['time_out'];
    
    if(!isset($grouped[$key])) {
        $grouped[$key] = [
            'subject' => $s['subject'],
            'class_type' => $s['class_type'],
            'units' => $s['units'],
            'time_in' => $s['time_in'],
            'time_out' => $s['time_out'],
            'schedule_type' => $s['schedule_type'],
            'course_year' => $s['course_year'],
            'days' => [],
            'rooms' => [],
            'total_hours' => 0
        ];
    }
    
    // Calculate hours for this meeting
    $hours = calculateHours($s['time_in'], $s['time_out']);
    $totalHoursGlobal += $hours;
    $grouped[$key]['total_hours'] += $hours;
    
    // Add day and room to the group
    $grouped[$key]['days'][] = $s['day_of_week'];
    $grouped[$key]['rooms'][] = $s['room'];
}

// 9. CALCULATE TOTALS (Count units only ONCE per unique subject+classtype combination)
// We need to track which subjects we've already counted
$countedSubjects = [];
$totalLec = 0;
$totalLab = 0;

foreach($grouped as $group) {
    $subjectKey = $group['subject'] . '_' . $group['class_type'];
    
    // Only count units once per subject+type combination
    if(!isset($countedSubjects[$subjectKey])) {
        if($group['class_type'] === 'Lecture') {
            $totalLec += $group['units'];
        } else if($group['class_type'] === 'Laboratory') {
            $totalLab += $group['units'];
        }
        $countedSubjects[$subjectKey] = true;
    }
}

// HELPER FUNCTION
function calculateHours($in, $out) {
    $start = strtotime($in);
    $end = strtotime($out);
    return round(abs($end - $start) / 3600, 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <script>
    (function(){
      var t = localStorage.getItem('ueples_theme') || 'dark';
      document.documentElement.dataset.theme = t;
      window.addEventListener('DOMContentLoaded', function() {
        var btn = document.getElementById('themeBtn');
        if(btn) btn.textContent = t === 'dark' ? '🌙' : '☀️';
      });
    })();
    function toggleTheme() {
      var next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
      document.documentElement.dataset.theme = next;
      localStorage.setItem('ueples_theme', next);
      var btn = document.getElementById('themeBtn');
      if(btn) btn.textContent = next === 'dark' ? '🌙' : '☀️';
    }
  </script>
    <meta charset="UTF-8">
    <title>Workload Report - <?= e($teacher['last_name']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; padding: 20px; }
        
        /* HEADER LAYOUT */
        .report-header-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        .report-header-table td { padding: 10px; background-color: #999; color: white; font-weight: bold; border: 1px solid #666; }
        .header-title { text-transform: uppercase; font-size: 14px; }
        
        /* MAIN DATA TABLE */
        .workload-table { width: 100%; border-collapse: collapse; margin-top: -1px; } 
        .workload-table th, .workload-table td { 
            border: 1px solid #333; 
            padding: 8px 4px; 
            text-align: center; 
            font-size: 11px;
            vertical-align: middle;
        }
        .workload-table th { font-weight: bold; text-transform: uppercase; height: 40px; }
        
        /* Column Widths */
        .col-subject { width: 18%; }
        .col-units { width: 8%; }
        .col-time { width: 10%; }
        
        /* Footer/Signatures */
        .signatures { margin-top: 40px; display: flex; justify-content: space-between; page-break-inside: avoid; }
        .sig-block { text-align: center; width: 200px; }
        .sig-line { border-top: 1px solid black; margin-top: 40px; padding-top: 5px; font-weight: bold; }

        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            .report-header-table td { background-color: #999 !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="margin-bottom:20px; text-align:right;">
        <button onclick="window.print()" style="padding:10px 20px; font-weight:bold; cursor:pointer;">PRINT REPORT</button>
        <button onclick="window.close()" style="padding:10px 20px; cursor:pointer; margin-left:10px;">CLOSE</button>
    </div>

    <table class="report-header-table">
        <tr>
            <td width="50%" class="header-title">
                <?= e(strtoupper($teacher['last_name'] . ', ' . $teacher['first_name'])) ?> WORKLOAD REPORT
            </td>
            <td width="50%">
                SCHOOL YEAR: <?= e($displaySY) ?><br>
                SEMESTER: <?= e($semLabel) ?>
            </td>
        </tr>
    </table>

    <table class="workload-table">
        <thead>
            <tr>
                <th class="col-subject">SUBJECT</th>
                <th class="col-units">UNITS</th>
                <th>CLASS TYPE</th>
                <th>DAY</th>
                <th>TIME IN</th>
                <th>TIME OUT</th>
                <th>ROOM</th>
                <th>GRADE/SECTION</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $rowCount = max(count($grouped), 8);
            $groupArray = array_values($grouped);
            
            for($i = 0; $i < $rowCount; $i++):
                if(isset($groupArray[$i])):
                    $g = $groupArray[$i];
                    
                    // Combine days and rooms
                    $daysStr = implode(', ', $g['days']);
                    $roomsStr = implode(', ', array_unique($g['rooms']));
            ?>
            <tr>
                <td><?= e($g['subject']) ?></td>
                <td><?= $g['units'] ?></td>
                <td><?= e($g['class_type']) ?></td>
                <td><?= $daysStr ?></td>
                <td><?= date('h:i A', strtotime($g['time_in'])) ?></td>
                <td><?= date('h:i A', strtotime($g['time_out'])) ?></td>
                <td><?= $roomsStr ?></td>
                <td><?= e($g['course_year']) ?></td>
            </tr>
            <?php else: ?>
            <tr>
                <td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
            </tr>
            <?php endif; endfor; ?>

            <tr style="font-weight:bold; background-color:#f0f0f0;">
                <td style="text-align:right; padding-right:10px;">TOTAL UNITS:</td>
                <td colspan="2">Lec: <?= $totalLec ?: '-' ?> | Lab: <?= $totalLab ?: '-' ?></td>
                <td colspan="2" style="text-align:right; padding-right:10px;">TOTAL HOURS/WEEK:</td>
                <td colspan="3"><?= number_format($totalHoursGlobal, 2) ?></td>
            </tr>
        </tbody>
    </table>

    <div class="signatures">
        <div class="sig-block">
            <p>Prepared by:</p>
            <div class="sig-line"><?= $secretary_name ?></div> 
            <span>School Secretary</span>
        </div>
        
        <div class="sig-block">
            <p>Confirmed:</p>
            <div class="sig-line"><?= strtoupper($teacher['first_name'] . ' ' . $teacher['last_name']) ?></div>
            <span>Faculty Member</span>
        </div>

        <div class="sig-block">
            <p>Approved by:</p>
            <div class="sig-line"><?= $principal_name ?></div>
            <span>School Principal</span>
        </div>
    </div>

</body>
</html>
