<?php
require_once '../../../backend/config/functions.php';
requireRole('secretary');
?><!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <script src="../../assets/js/theme.js"></script>
  
  <script>document.documentElement.dataset.theme=localStorage.getItem('ueples_theme')||'dark';</script>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>New Schedule � Secretary</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root{--accent:#fbbf24;--accent-bg:rgba(245,158,11,.12)}

    /* -- Page layout ----------------------─ */
    .plot-page{padding:18px 20px;height:calc(100vh - var(--nav-h));display:flex;flex-direction:column;gap:14px;overflow:hidden}
    .plot-page-hdr{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-shrink:0}
    .plot-page-hdr h2{font-size:1.25rem;font-weight:800;display:flex;align-items:center;gap:10px}
    .plot-page-hdr h2 a{color:var(--text-sub);font-size:1rem}
    .plot-page-hdr h2 a:hover{color:var(--text)}

    /* -- Workspace ------------------------─ */
    .plot-workspace{display:flex;gap:16px;flex:1;min-height:0;align-items:flex-start}

    /* -- Left accordion panel -------------- */
    .acc-panel{width:320px;flex-shrink:0;height:100%;overflow-y:auto;display:flex;flex-direction:column;gap:12px;padding:4px 8px 4px 4px}
    .acc-panel::-webkit-scrollbar{width:6px}
    .acc-panel::-webkit-scrollbar-thumb{background:var(--border);border-radius:4px}
    
    .acc-item{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-sm);overflow:hidden;transition:border-color var(--t);flex-shrink:0}
    #acc-type{order:1}
    #acc-sy{order:2}
    #acc-room{order:3}
    #acc-teacher{order:4}
    #acc-details{order:5}
    #acc-coed-fields{order:6}
    #acc-time{order:7}
    .acc-item.acc-active{border-color:var(--accent)}
    .acc-trigger{width:100%;padding:14px 16px;background:none;border:none;display:flex;align-items:center;justify-content:space-between;font-size:.9rem;font-weight:700;color:var(--text)}
    .acc-trigger-left{display:flex;align-items:center;gap:10px}
    .acc-trigger-left .icon{color:var(--accent);width:16px;text-align:center}
    .acc-trigger .chevron{font-size:.8rem;color:var(--text-muted);transition:transform var(--t)}
    .acc-open .chevron{transform:rotate(180deg)}
    .acc-body{padding:16px;border-top:1px solid var(--border);display:none;background:var(--bg-ele);flex-direction:column;gap:14px}
    .acc-open .acc-body{display:flex}
    .acc-badge{font-size:.65rem;padding:3px 8px;border-radius:4px;background:var(--info-bg);color:var(--info);font-weight:700;margin-left:auto;margin-right:8px}

    .form-group{display:flex;flex-direction:column;gap:6px}
    .form-label{font-size:.75rem;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.04em}
    .form-label span{color:var(--danger)}
    .form-control{width:100%;padding:10px 14px;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text);font-size:.9rem;outline:none;transition:border-color var(--t), box-shadow var(--t)}
    .form-control:focus{border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-bg)}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
    .form-check{display:flex;align-items:center;gap:8px;font-size:.85rem;color:var(--text-sub);cursor:pointer;padding-top:4px}

    /* -- Right timetable ------------------─ */
    .plot-right{flex:1;min-width:0;height:100%;display:flex;flex-direction:column;gap:12px;overflow:hidden}
    .plot-legend{display:flex;align-items:center;gap:16px;flex-wrap:wrap;font-size:.8rem;color:var(--text);padding:10px 16px;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-sm);flex-shrink:0}
    .legend-dot{width:12px;height:12px;border-radius:3px;flex-shrink:0;display:inline-block}
    .legend-item{display:flex;align-items:center;gap:6px}
    .plot-grid-wrap{flex:1;overflow:auto;border:1px solid var(--border);border-radius:var(--radius-sm);background:var(--bg-card)}
    .plot-status{font-size:.85rem;color:var(--text-muted);padding:4px 0;flex-shrink:0;min-height:24px}

    /* -- Grid ------------------------------ */
    .plot-grid{display:grid;grid-template-columns:140px repeat(7,1fr);min-width:850px}
    .g-day-hdr{position:sticky;top:0;z-index:5;padding:10px 6px;text-align:center;font-weight:700;font-size:.75rem;color:var(--text-sub);text-transform:uppercase;letter-spacing:.05em;border-right:1px solid var(--border);border-bottom:2px solid var(--border);background:var(--bg-card)}
    .g-time{padding:4px 8px;font-size:.7rem;color:var(--text-muted);text-align:center;display:flex;align-items:center;justify-content:center;border-right:1px solid var(--border);border-bottom:1px solid rgba(128,128,128,.1)}
    .g-cell{min-height:42px; height:100%; box-sizing:border-box; border-right:1px solid rgba(128,128,128,.1);border-bottom:1px solid rgba(128,128,128,.1);background:var(--cell-free);position:relative;display:flex;align-items:flex-start;justify-content:center;padding:12px 4px 4px 4px}
    .g-cell:last-child{border-right:none}
    
    .g-cell.occupied{background:var(--cell-teacher);border-left:3px solid var(--cell-teacher-b)}
    .g-cell.occupied .cl{color:var(--text);font-weight:700}
    .g-cell.conflict{background:var(--cell-conflict);border-left:3px solid var(--cell-conflict-b)}
    .g-cell.conflict .cl{color:var(--text);font-weight:700}
    .g-cell.preview{background:var(--cell-preview);border-left:3px solid var(--cell-preview-b)}
    .g-cell.preview .cl{color:var(--text);font-weight:700}

    .g-cell .cl{font-size:.68rem;font-weight:700;text-align:center;line-height:1.25;display:block;white-space:normal;overflow:visible;word-break:break-word}

    /* -- Error box ------------------------─ */
    .err-box{display:none;padding:10px 12px;background:var(--danger-bg);color:var(--danger);border-radius:var(--radius-sm);border-left:3px solid var(--danger);font-size:.82rem;margin-bottom:8px}

    /* -- Submit btn ------------------------ */
    .save-btn{width:100%;margin-top:6px;padding:10px;font-size:.88rem}
  </style>
</head>
<body class="page-body">

<header class="main-header">
  <div class="brand"><i class="fa-solid fa-calendar-days"></i><span>SECRETARY PORTAL<small class="brand-sub">UEP LES System</small></span></div>
  <nav class="top-nav">
    <a href="index.php" class="active"><i class="fa-solid fa-table-cells"></i> <span>Schedules</span></a>
    <a href="../master_data/index.php"><i class="fa-solid fa-database"></i> <span>Master Data</span></a>
    <a href="../profile/index.php"><i class="fa-solid fa-user-circle"></i> <span>Profile</span></a>
    <a href="../../../backend/auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> <span>Logout</span></a>
    <button class="theme-btn" id="themeBtn" title="Toggle theme"></button>
  </nav>
</header>

<div class="plot-page">

  <div class="plot-page-hdr">
    <h2><a href="index.php"><i class="fa-solid fa-arrow-left"></i></a> <i class="fa-solid fa-calendar-plus" style="color:var(--accent)"></i> New Schedule</h2>
    <div style="font-size:.8rem;color:var(--text-muted)"><i class="fa-solid fa-info-circle"></i> Fill in fields on the left � timetable updates live</div>
  </div>

  <div class="plot-workspace">

    <!-- ----─ LEFT: Accordion Field Panel ----─ -->
    <div class="acc-panel" id="accPanel">

      <!-- 1. Type -->
      <div class="acc-item acc-open" id="acc-type">
        <button class="acc-trigger" onclick="toggleAcc('acc-type')">
          <span class="acc-trigger-left"><i class="fa-solid fa-tag icon"></i> Schedule Type</span>
          <i class="fa-solid fa-chevron-down chevron"></i>
        </button>
        <div class="acc-body">
          <div class="form-group" style="margin-bottom:0">
            <select class="form-control" id="fType" onchange="onTypeChange()">
              <option value="LES" selected>Internal (LES)</option>
              <option value="COED">External (COED / Other)</option>
            </select>
          </div>
        </div>
      </div>

      <!-- 2. SY & Sem -->
      <div class="acc-item" id="acc-sy">
        <button class="acc-trigger" onclick="toggleAcc('acc-sy')" disabled>
          <span class="acc-trigger-left"><i class="fa-solid fa-calendar icon"></i> School Year &amp; Sem</span>
          <i class="fa-solid fa-chevron-down chevron"></i>
        </button>
        <div class="acc-body">
          <div class="form-group">
            <label class="form-label">School Year <span>*</span></label>
            <select class="form-control" id="fSy" onchange="stepCompleted('sy')">
              <option value="">Loading…</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Semester <span>*</span></label>
            <select class="form-control" id="fSem" onchange="stepCompleted('sy')">
              <option value="1">1st Semester</option>
              <option value="2">2nd Semester</option>
            </select>
          </div>
        </div>
      </div>

      <!-- 3. Room -->
      <div class="acc-item" id="acc-room">
        <button class="acc-trigger" onclick="toggleAcc('acc-room')" disabled>
          <span class="acc-trigger-left"><i class="fa-solid fa-door-open icon"></i> Room</span>
          <i class="fa-solid fa-chevron-down chevron"></i>
        </button>
        <div class="acc-body">
          <div class="form-group" id="grp-room-sel">
            <label class="form-label">Building / Room <span>*</span></label>
            <select class="form-control" id="fRoom" onchange="stepCompleted('room')">
              <option value="">Select room�</option>
            </select>
          </div>
          <div class="form-group" id="grp-room-txt" style="display:none">
            <label class="form-label">Building / Room <span>*</span></label>
            <input type="text" class="form-control" id="fCoedRoomTxt" placeholder="e.g. UEP Main - Room 101" oninput="stepCompleted('room')">
          </div>
        </div>
      </div>

      <!-- 4. Teacher -->
      <div class="acc-item" id="acc-teacher">
        <button class="acc-trigger" onclick="toggleAcc('acc-teacher')" disabled>
          <span class="acc-trigger-left"><i class="fa-solid fa-user-tie icon"></i> Teacher</span>
          <i class="fa-solid fa-chevron-down chevron"></i>
        </button>
        <div class="acc-body">
          <div class="form-group">
            <label class="form-label">Teacher <span>*</span></label>
            <select class="form-control" id="fTeacher" onchange="stepCompleted('teacher')">
              <option value="">Select teacher…</option>
            </select>
          </div>
          <label class="form-check" id="specialistToggleWrap">
            <input type="checkbox" id="chkSpecialist" onchange="onSubjectChange()">
            Specialists for selected subject only
          </label>
        </div>
      </div>

      <!-- 5. LES: Grade, Section, Curriculum, Subject -->
      <div class="acc-item" id="acc-details">
        <button class="acc-trigger" onclick="toggleAcc('acc-details')" disabled>
          <span class="acc-trigger-left"><i class="fa-solid fa-graduation-cap icon"></i> Grade &amp; Class</span>
          <span class="acc-badge">LES</span>
          <i class="fa-solid fa-chevron-down chevron"></i>
        </button>
        <div class="acc-body">
          <!-- Grade Level -->
          <div class="form-group">
            <label class="form-label" for="fGrade">Grade Level <span>*</span></label>
            <select class="form-control" id="fGrade" onchange="onGradeChange()">
              <option value="">Select grade level&hellip;</option>
            </select>
          </div>
          <!-- Section -->
          <div class="form-group">
            <label class="form-label" for="fSec">Class Section <span>*</span></label>
            <select class="form-control" id="fSec" onchange="stepCompleted('subj')" disabled>
              <option value="">Select grade first&hellip;</option>
            </select>
          </div>
          <!-- Curriculum -->
          <div class="form-group">
            <label class="form-label" for="fCurriculum">Curriculum <span>*</span></label>
            <select class="form-control" id="fCurriculum" onchange="onCurriculumChange()" disabled>
              <option value="">Select grade first&hellip;</option>
            </select>
          </div>
          <!-- Subject -->
          <div class="form-group">
            <label class="form-label" for="fSubj">Subject <span>*</span></label>
            <select class="form-control" id="fSubj" onchange="onSubjectChange(); stepCompleted('subj')" disabled>
              <option value="">Select curriculum first&hellip;</option>
            </select>
          </div>
        </div>
      </div>

      <div class="acc-item" id="acc-coed-fields" style="display:none">
        <button class="acc-trigger" onclick="toggleAcc('acc-coed-fields')" disabled>
          <span class="acc-trigger-left"><i class="fa-solid fa-pen-to-square icon"></i> Course Info</span>
          <span class="acc-badge" style="background:rgba(245,158,11,.15);color:#fbbf24">COED</span>
          <i class="fa-solid fa-chevron-down chevron"></i>
        </button>
        <div class="acc-body">
          <div class="form-group">
            <label class="form-label">Course / Year Level <span>*</span></label>
            <input type="text" class="form-control" id="fCoedCourse" placeholder="e.g. BSIT 1-A" oninput="stepCompleted('class')">
          </div>
          <div class="form-group">
            <label class="form-label">Subject / Course Name <span>*</span></label>
            <input type="text" class="form-control" id="fCoedSubject" placeholder="e.g. Computer Programming" oninput="stepCompleted('subject')">
          </div>
          <div class="form-group">
            <label class="form-label">Units <span>*</span></label>
            <input type="number" step="0.5" min="0.5" class="form-control" id="fCoedUnits" placeholder="e.g. 3" oninput="stepCompleted('subject')">
          </div>
        </div>
      </div>

      <!-- 6. Day & Time -->
      <div class="acc-item" id="acc-time">
        <button class="acc-trigger" onclick="toggleAcc('acc-time')" disabled>
          <span class="acc-trigger-left"><i class="fa-regular fa-clock icon"></i> Day &amp; Time</span>
          <i class="fa-solid fa-chevron-down chevron"></i>
        </button>
        <div class="acc-body">
          <div class="form-group">
            <label class="form-label">Days of Week <span>*</span></label>
            <div style="display:flex;flex-wrap:wrap;gap:8px" id="dayCheckboxes">
              <label class="form-check"><input type="checkbox" value="Monday" onchange="stepCompleted('time')"> Mon</label>
              <label class="form-check"><input type="checkbox" value="Tuesday" onchange="stepCompleted('time')"> Tue</label>
              <label class="form-check"><input type="checkbox" value="Wednesday" onchange="stepCompleted('time')"> Wed</label>
              <label class="form-check"><input type="checkbox" value="Thursday" onchange="stepCompleted('time')"> Thu</label>
              <label class="form-check"><input type="checkbox" value="Friday" onchange="stepCompleted('time')"> Fri</label>
              <label class="form-check"><input type="checkbox" value="Saturday" onchange="stepCompleted('time')"> Sat</label>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Time In <span>*</span></label>
              <input type="time" class="form-control" id="fStart" step="900" onchange="stepCompleted('time')">
            </div>
            <div class="form-group">
              <label class="form-label">Time Out <span>*</span></label>
              <input type="time" class="form-control" id="fEnd" step="900" onchange="stepCompleted('time')">
            </div>
          </div>
          
          <div class="err-box" id="errorBox"></div>
          <button class="btn btn-primary" id="saveBtn" onclick="submitSchedule()" disabled style="margin-top:8px;width:100%">
            <i class="fa-solid fa-floppy-disk"></i> Create Schedule
          </button>
        </div>
      </div>

    </div>
    <!-- ----─ END Left Panel ----─ -->

    <!-- ----─ RIGHT: Timetable ----─ -->
    <div class="plot-right">
      <div class="plot-legend">
        <span class="legend-item"><span class="legend-dot" style="background:var(--cell-teacher)"></span> Occupied Class</span>
        <span class="legend-item"><span class="legend-dot" style="background:var(--cell-conflict)"></span> Conflict!</span>
        <span class="legend-item"><span class="legend-dot" style="background:var(--cell-preview)"></span> Proposed slot</span>
        <span class="legend-item"><span class="legend-dot" style="background:var(--cell-free);border:1px solid var(--border)"></span> Available</span>
      </div>
      <div class="plot-status" id="plotStatus">Select a school year to begin.</div>
      <div class="plot-grid-wrap">
        <div class="plot-grid" id="plotGrid">
          <!-- Rendered by JS -->
        </div>
      </div>
    </div>

  </div><!-- end plot-workspace -->
</div><!-- end plot-page -->

<div id="toastContainer"></div>
<script src="create_schedule.js"></script>
</body>
</html>
