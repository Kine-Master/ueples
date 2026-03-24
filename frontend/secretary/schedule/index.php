<?php
require_once '../../../backend/config/functions.php';
requireRole('secretary');
?><!DOCTYPE html>
<html lang="en">
<head>
  <script src="../../assets/js/theme.js"></script>
  
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Schedules � Secretary</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root{--accent:#fbbf24;--accent-bg:rgba(245,158,11,.12)}


    /* -- Timetable Grid ------------------------------ */
    .plot-grid{display:grid;grid-template-columns:140px repeat(6,1fr);min-width:850px}
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
    .g-cell.coed{background:var(--cell-coed);border-left:3px solid var(--cell-coed-b)}
    .g-cell.coed .cl{color:var(--text);font-weight:700}

    .g-cell .cl{font-size:.68rem;font-weight:700;text-align:center;line-height:1.25;display:block;white-space:normal;overflow:visible;word-break:break-word}
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

<main class="page-content" style="max-width:1400px">
  <div class="page-header">
    <div class="page-header-text"><h2><i class="fa-solid fa-calendar-week" style="color:var(--accent)"></i> Master Schedule</h2><p>View and manage all active academic schedules.</p></div>
    <div class="right">
      <a href="report.php" id="printScheduleBtn" target="_blank" class="btn btn-secondary"><i class="fa-solid fa-print"></i> Print Schedule</a>
      <a href="create_schedule.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> <span>New Schedule</span></a>
    </div>
  </div>

  <div class="section-card">
    <div class="toolbar" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;justify-content:space-between">
      <div style="display:flex;gap:12px;flex-wrap:wrap">
        <select class="filter-sel" id="fType" onchange="loadSchedules()"><option value="">All Types (LES + COED)</option><option value="LES">LES Only</option><option value="COED">COED Only</option></select>
        <select class="filter-sel" id="fSem" style="display:none" onchange="loadSchedules()"><option value="">Any Semester</option><option value="1">1st Semester</option><option value="2">2nd Semester</option></select>
        <select class="filter-sel" id="fDay" onchange="loadSchedules()"><option value="">All Days</option><option value="Monday">Monday</option><option value="Tuesday">Tuesday</option><option value="Wednesday">Wednesday</option><option value="Thursday">Thursday</option><option value="Friday">Friday</option><option value="Saturday">Saturday</option></select>
        <select class="filter-sel" id="fTeacher" onchange="loadSchedules()"><option value="">All Teachers</option></select>
      </div>
      <div>
        <div class="view-toggle" style="display:flex;background:var(--bg-hover);border-radius:var(--radius-sm);overflow:hidden;border:1px solid var(--border)">
          <button class="btn view-btn active" id="btnViewTimetable" onclick="switchView('timetable')" style="border:none;border-radius:0;background:var(--accent);color:#fff;padding:8px 16px;cursor:pointer"><i class="fa-solid fa-calendar-day"></i> Timetable</button>
          <button class="btn view-btn" id="btnViewTable" onclick="switchView('table')" style="border:none;border-radius:0;background:transparent;color:var(--text-sub);padding:8px 16px;cursor:pointer"><i class="fa-solid fa-list"></i> Table</button>
        </div>
      </div>
    </div>
    <div class="table-wrap" id="viewTable" style="display:none">
      <table class="data-table" id="schedTable">
        <thead>
          <tr>
            <th>Type</th>
            <th>Subject</th>
            <th>Section</th>
            <th>Teacher</th>
            <th>Day / Time / Room</th>
            <th>Semester</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="schedBody">
          <tr class="no-data"><td colspan="7"><div class="spinner-wrap"><i class="fa-solid fa-spinner"></i><p>Loading schedules…</p></div></td></tr>
        </tbody>
      </table>
    </div>

    <!-- Timetable View -->
    <div class="timetable-wrap" id="viewTimetable">
      <div class="plot-legend" style="margin-bottom:12px;display:flex;gap:20px;font-size:0.85rem">
        <span class="legend-item" style="display:flex;align-items:center;gap:6px"><span class="legend-dot" style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#0ea5e9"></span> LES Class</span>
        <span class="legend-item" style="display:flex;align-items:center;gap:6px"><span class="legend-dot coed" style="display:inline-block;width:12px;height:12px;border-radius:50%"></span> COED Class</span>
      </div>
      <div class="plot-grid-wrap" style="height:650px;overflow-y:auto;border:1px solid var(--border);border-radius:var(--radius-md);background:var(--bg-card)">
        <div class="plot-grid" id="plotGrid">
          <!-- Rendered by JS -->
          <div style="grid-column:1/-1;padding:40px;text-align:center;color:var(--text-muted)"><i class="fa-solid fa-spinner fa-spin"></i> Loading timetable…</div>
        </div>
      </div>
    </div>
  </div>
</main>

<div class="modal-overlay" id="delModal">
  <div class="modal modal-sm">
    <div class="modal-header"><h3><i class="fa-solid fa-trash"></i> Archive Schedule</h3><button class="modal-close" onclick="closeModal('delModal')"><i class="fa-solid fa-xmark"></i></button></div>
    <div class="modal-body"><p style="color:var(--text-sub);font-size:.9rem">Are you sure you want to delete this schedule? It will be moved to the archive and can only be restored by an Administrator.</p></div>
    <div class="modal-footer"><button class="btn btn-secondary" onclick="closeModal('delModal')">Cancel</button><button class="btn btn-danger" id="delBtn" onclick="doArchive()"><i class="fa-solid fa-box-archive"></i> Archive</button></div>
  </div>
</div>

<div id="toastContainer"></div>
<script src="script.js"></script>
</body></html>
