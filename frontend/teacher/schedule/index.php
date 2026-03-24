<?php
require_once '../../../backend/config/functions.php';
requireRole('teacher');
?><!DOCTYPE html>
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
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>My Schedule — Teacher</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root{--accent:#10b981;--accent-bg:rgba(16,185,129,.12)} /* Emerald Green */

    /* ── Timetable Grid ────────────────────────────── */
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
  <div class="brand"><i class="fa-solid fa-chalkboard-user"></i><span>TEACHER PORTAL<small class="brand-sub">UEP LES System</small></span></div>
  <nav class="top-nav">
    <a href="index.php" class="active"><i class="fa-solid fa-calendar-day"></i> My Schedule</a>
    <a href="../classes/index.php"><i class="fa-solid fa-users"></i> My Classes</a>
    <a href="../profile/index.php"><i class="fa-solid fa-user-circle"></i> Profile</a>
    <a href="../../../backend/auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
      <button class="theme-btn" id="themeBtn" title="Toggle theme" onclick="toggleTheme()">🌙</button>
  </nav>
</header>

<main class="page-content">
  <div class="page-header">
    <div class="page-header-text"><h2><i class="fa-solid fa-calendar-day" style="color:var(--accent)"></i> My Weekly Schedule</h2><p>Overview of all assigned LES and COED classes.</p></div>
    <div class="right" style="display:flex; gap:12px; align-items:center;">
        <a class="btn btn-secondary" id="printScheduleBtn" target="_blank" href="report.php"><i class="fa-solid fa-print"></i> Print Schedule</a>
        <div class="view-toggle" style="display:flex;background:var(--bg-hover);border-radius:var(--radius-sm);overflow:hidden;border:1px solid var(--border)">
          <button class="btn view-btn active" id="btnViewTimetable" onclick="switchView('timetable')" style="border:none;border-radius:0;background:var(--accent);color:#fff;padding:8px 16px;cursor:pointer"><i class="fa-solid fa-calendar-day"></i> Timetable</button>
          <button class="btn view-btn" id="btnViewTable" onclick="switchView('table')" style="border:none;border-radius:0;background:transparent;color:var(--text-sub);padding:8px 16px;cursor:pointer"><i class="fa-solid fa-list"></i> Table</button>
        </div>
        <select class="filter-sel" id="fSchoolYear" onchange="loadSchedule()"><option value="">Loading school years...</option></select>
        <select class="filter-sel" id="fSem" onchange="loadSchedule()"><option value="1">1st Semester</option><option value="2">2nd Semester</option></select>
    </div>
  </div>

  <div class="section-card" style="margin-top:16px;">
    <!-- Tabular View -->
    <div class="table-wrap" id="viewTable" style="display:none">
      <table class="data-table" id="schedTable">
        <thead>
          <tr>
            <th>Type</th>
            <th>Subject</th>
            <th>Section</th>
            <th>Day / Time / Room</th>
            <th>Semester</th>
          </tr>
        </thead>
        <tbody id="schedBody">
          <tr class="no-data"><td colspan="5"><div class="spinner-wrap"><i class="fa-solid fa-spinner fa-spin"></i><p>Loading schedule...</p></div></td></tr>
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
<script src="script.js"></script>
</body></html>
