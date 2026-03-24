<?php
require_once '../../../backend/config/functions.php';
requireRole('principal');
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
  <title>Students Overview — Principal</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root{--accent:#8b5cf6;--accent-bg:rgba(139,92,246,.12)} /* Violet */
    .class-sidebar{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-md);height:calc(100vh - 180px);overflow-y:auto;padding:16px;}
    .class-item{padding:12px;border-radius:var(--radius-sm);cursor:pointer;margin-bottom:8px;transition:var(--transition);border:1px solid transparent;background:var(--bg-sub)}
    .class-item:hover{border-color:var(--border)}
    .class-item.active{background:var(--accent-bg);border-color:var(--accent);color:var(--text)}
    .student-panel{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-md);display:flex;flex-direction:column;height:calc(100vh - 180px)}
    .panel-header{padding:16px 24px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center}
    .panel-body{padding:24px;overflow-y:auto;flex-grow:1}
    @media (max-width:800px) {
        .layout-grid { grid-template-columns: 1fr !important; }
        .class-sidebar, .student-panel { height: auto; min-height: 400px; }
    }
  </style>
</head>
<body class="page-body">

<header class="main-header">
  <div class="brand"><i class="fa-solid fa-school"></i><span>PRINCIPAL PORTAL</span></div>
  <nav class="top-nav">
    <a href="../dashboard/index.php"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
    <a href="index.php" class="active"><i class="fa-solid fa-user-graduate"></i> Students</a>
    <a href="../teachers/index.php"><i class="fa-solid fa-chalkboard-user"></i> Teachers</a>
    <a href="../rooms/index.php"><i class="fa-solid fa-door-open"></i> Rooms</a>
    <a href="../profile/index.php"><i class="fa-solid fa-user-circle"></i> Profile</a>
    <a href="../../../backend/auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
      <button class="theme-btn" id="themeBtn" title="Toggle theme" onclick="toggleTheme()">🌙</button>
  </nav>
</header>

<main class="page-content" style="max-width:1400px">
  <div class="page-header">
    <div class="page-header-text"><h2><i class="fa-solid fa-user-graduate" style="color:var(--accent)"></i> School Enrollment</h2><p>Read-only overview of student rosters by class section.</p></div>
  </div>

  <div class="layout-grid" style="display:grid;grid-template-columns:300px 1fr;gap:24px">
    
    <div class="class-sidebar">
      <h3 style="margin-top:0;font-size:1.1rem;margin-bottom:16px;color:var(--text);display:flex;align-items:center;gap:8px"><i class="fa-solid fa-list"></i> All Active Sections</h3>
      <div id="classList"><div style="text-align:center;padding:20px;color:var(--text-muted)"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</div></div>
    </div>

    <div class="student-panel">
      <div class="panel-header" id="panelHeader">
        <div>
          <h3 style="margin:0;font-size:1.2rem;color:var(--text)">Select a Class</h3>
          <p style="margin:4px 0 0 0;font-size:.85rem;color:var(--text-sub)">Choose a class section from the left panel.</p>
        </div>
      </div>
      
      <div class="panel-body">
        <div class="toolbar" id="studentToolbar" style="display:none;margin-bottom:16px">
           <input type="text" class="form-control" id="searchQ" placeholder="Search by name or LRN..." style="max-width:300px" onkeyup="if(event.key==='Enter') loadStudents()">
           <button class="btn btn-secondary" onclick="loadStudents()"><i class="fa-solid fa-search"></i></button>
        </div>

        <div class="table-wrap" id="studentTableWrap" style="display:none">
          <table class="data-table">
            <thead><tr><th>LRN</th><th>Name</th><th>Gender</th><th>Status</th></tr></thead>
            <tbody id="studentBody"></tbody>
          </table>
        </div>

        <div id="emptyState" style="text-align:center;padding:60px;color:var(--text-muted)">
            <i class="fa-regular fa-hand-pointer" style="font-size:3rem;margin-bottom:16px;opacity:0.3"></i>
            <p style="font-size:1.1rem;color:var(--text-sub)">Select a section from the sidebar to view its student roster.</p>
        </div>
      </div>
    </div>

  </div>
</main>
<script src="script.js"></script>
</body></html>
