<?php
require_once '../../../backend/config/functions.php';
requireRole('teacher');

$teacherId = (int)$_SESSION['user_id'];
$semester = trim($_GET['semester'] ?? '');
$schoolYearId = (int)($_GET['school_year_id'] ?? 0);

if (!$schoolYearId) {
    $stmt = $pdo->query("SELECT school_year_id FROM school_year WHERE is_active = 1 LIMIT 1");
    $schoolYearId = (int)($stmt->fetchColumn() ?: 0);
}

$teacherStmt = $pdo->prepare("SELECT first_name, last_name FROM user WHERE user_id = ? LIMIT 1");
$teacherStmt->execute([$teacherId]);
$teacher = $teacherStmt->fetch(PDO::FETCH_ASSOC);
if (!$teacher) {
    die('Teacher not found.');
}

$syLabelStmt = $pdo->prepare("SELECT label FROM school_year WHERE school_year_id = ? LIMIT 1");
$syLabelStmt->execute([$schoolYearId]);
$schoolYearLabel = (string)($syLabelStmt->fetchColumn() ?: 'N/A');

$sql = "SELECT s.schedule_type, s.day_of_week, s.time_in, s.time_out, s.semester,
               CONCAT(u.last_name, ', ', u.first_name) AS teacher_name,
               sub.name AS subject_name,
               gl.name AS grade_name,
               cs.section_name,
               r.room_name,
               b.name AS building_name,
               s.coed_subject,
               s.coed_grade_level,
               s.coed_room,
               s.coed_building
        FROM schedule s
        JOIN user u ON u.user_id = s.teacher_id
        LEFT JOIN subject sub ON sub.subject_id = s.subject_id
        LEFT JOIN class_section cs ON cs.class_section_id = s.class_section_id
        LEFT JOIN grade_level gl ON gl.grade_level_id = cs.grade_level_id
        LEFT JOIN room r ON r.room_id = s.room_id
        LEFT JOIN building b ON b.building_id = r.building_id
        WHERE s.is_active = 1 AND s.teacher_id = ?";
$params = [$teacherId];

if ($schoolYearId) {
    $sql .= " AND s.school_year_id = ?";
    $params[] = $schoolYearId;
}
if ($semester !== '') {
    $sql .= " AND s.semester = ?";
    $params[] = $semester;
}

$sql .= " ORDER BY FIELD(s.day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), s.time_in";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$semesterLabel = $semester === '1' ? '1st Semester' : ($semester === '2' ? '2nd Semester' : ($semester === 'Summer' ? 'Summer' : 'All Semesters'));
$teacherName = strtoupper(($teacher['last_name'] ?? '') . ', ' . ($teacher['first_name'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Teacher Schedule Report</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 18px; color: #111; }
    .no-print { margin-bottom: 12px; text-align: right; }
    .no-print button { padding: 8px 12px; }
    .header { border: 1px solid #222; padding: 12px; margin-bottom: 10px; }
    .header h1 { margin: 0 0 6px; font-size: 18px; }
    .meta { font-size: 12px; }
    .legend { margin: 10px 0 8px; display: flex; gap: 14px; font-size: 12px; }
    .dot { width: 12px; height: 12px; display: inline-block; border-radius: 2px; margin-right: 6px; vertical-align: middle; }
    .dot.les { background: rgba(14,165,233,.35); border: 1px solid #0ea5e9; }
    .dot.coed { background: rgba(245,158,11,.35); border: 1px solid #d97706; }
    .plot-grid { display:grid; grid-template-columns:140px repeat(6,1fr); border:1px solid #222; }
    .g-day-hdr { padding:8px 6px; text-align:center; font-weight:700; font-size:11px; border-right:1px solid #222; border-bottom:2px solid #222; background:#f0f0f0; }
    .g-time { padding:4px 6px; font-size:10px; text-align:center; border-right:1px solid #222; border-bottom:1px solid #ddd; }
    .g-cell { min-height:26px; border-right:1px solid #ddd; border-bottom:1px solid #eee; background:#fff; position:relative; padding:4px; }
    .g-cell.occupied { background: rgba(14,165,233,.22); border-left:3px solid #0ea5e9; }
    .g-cell.coed { background: rgba(245,158,11,.22); border-left:3px solid #d97706; }
    .g-cell .cl { font-size:9px; font-weight:700; line-height:1.2; }
    .sig-wrap { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin-top: 28px; }
    .sig { text-align: center; font-size: 12px; }
    .line { border-top: 1px solid #222; margin-top: 36px; padding-top: 6px; font-weight: 700; }
    @media print { .no-print { display: none; } body { margin: 8px; } }
  </style>
</head>
<body onload="window.print()">
  <div class="no-print">
    <button onclick="window.print()">Print</button>
    <button onclick="window.close()">Close</button>
  </div>

  <div class="header">
    <h1>UEP LES Scheduling System - Teacher Schedule Report</h1>
    <div class="meta"><strong>Teacher:</strong> <?= e($teacherName) ?></div>
    <div class="meta"><strong>School Year:</strong> <?= e($schoolYearLabel) ?> | <strong>Semester:</strong> <?= e($semesterLabel) ?></div>
    <div class="meta"><strong>Generated:</strong> <?= e(date('F d, Y h:i A')) ?></div>
  </div>

  <div class="legend">
    <span><i class="dot les"></i> LES Class</span>
    <span><i class="dot coed"></i> COED Class</span>
  </div>
  <div id="plotGrid" class="plot-grid"></div>

  <div class="sig-wrap">
    <div class="sig"><div class="line">Prepared By</div><div>Teacher</div></div>
    <div class="sig"><div class="line">Noted By</div><div>School Secretary</div></div>
    <div class="sig"><div class="line">Approved By</div><div>Principal</div></div>
  </div>

<script>
const DATA = <?= json_encode($rows, JSON_UNESCAPED_UNICODE) ?>;
const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
const START_H = 6, END_H = 18, SLOT = 15;
const TOTAL_ROWS = (END_H - START_H) * (60 / SLOT);
function esc(s){return String(s||'').replace(/[&<>'"]/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[m]));}
function toMins(t){const [h,m]=String(t||'00:00').split(':'); return parseInt(h,10)*60+parseInt(m,10);}
function padded(h,m){return String(h).padStart(2,'0')+':'+String(m).padStart(2,'0');}
function tLabel(h,m){const d=new Date(); d.setHours(h,m,0,0); return d.toLocaleTimeString('en-US',{hour:'numeric',minute:'2-digit',hour12:true});}
function buildBlocks(rows){
  const blocks = new Map(); const covered = new Set();
  rows.forEach(r=>{
    const mIn = toMins(r.time_in), mOut = toMins(r.time_out);
    if(mIn>=mOut || mIn<START_H*60 || mOut>END_H*60) return;
    const span = Math.max(1,(mOut-mIn)/SLOT);
    const h=Math.floor(mIn/60), m=mIn%60;
    const key = `${r.day_of_week}:${padded(h,m)}`;
    const isLES = r.schedule_type==='LES';
    const subj = isLES ? (r.subject_name||'N/A') : (r.coed_subject||'N/A');
    const sec = isLES ? `${r.grade_name||''} - ${r.section_name||''}` : (r.coed_grade_level||'COED');
    const room = isLES ? `${r.room_name||'TBA'}${r.building_name?` (${r.building_name})`:''}` : `${r.coed_room||'TBA'}${r.coed_building?` (${r.coed_building})`:''}`;
    blocks.set(key,{type:r.schedule_type,label:`<strong>${esc(subj)}</strong><br>${esc(sec)}<br>${esc(room)}`,span});
    for(let x=mIn;x<mOut;x+=SLOT){const hh=Math.floor(x/60), mm=x%60; covered.add(`${r.day_of_week}:${padded(hh,mm)}`);} 
  });
  return {blocks, covered};
}
function render(){
  const grid=document.getElementById('plotGrid');
  const {blocks, covered}=buildBlocks(DATA);
  const DAY_COL={}; DAYS.forEach((d,i)=>DAY_COL[d]=i+2);
  let html='';
  html += '<div class="g-day-hdr" style="grid-column:1;grid-row:1"></div>';
  DAYS.forEach(d=>{html += `<div class="g-day-hdr" style="grid-column:${DAY_COL[d]};grid-row:1">${d}</div>`;});
  for(let r=0;r<TOTAL_ROWS;r++){
    const row=r+2; const mins=START_H*60+r*SLOT; const h1=Math.floor(mins/60), m1=mins%60; const h2=Math.floor((mins+SLOT)/60), m2=(mins+SLOT)%60;
    html += `<div class="g-time" style="grid-column:1;grid-row:${row}">${tLabel(h1,m1)}-${tLabel(h2,m2)}</div>`;
    DAYS.forEach(d=>{
      const key=`${d}:${padded(h1,m1)}`; const isBlock=blocks.has(key); const isCovered=covered.has(key);
      if(!isBlock && isCovered) return;
      let cls='', label='', span=1;
      if(isBlock){const b=blocks.get(key); cls=b.type==='LES'?'occupied':'coed'; label=b.label; span=b.span;}
      const pos=`grid-column:${DAY_COL[d]};grid-row:${row}/${row+span}`;
      html += `<div class="g-cell${cls?(' '+cls):''}" style="${pos}">${label?`<div class="cl">${label}</div>`:''}</div>`;
    });
  }
  grid.innerHTML = html;
}
render();
</script>
</body>
</html>
