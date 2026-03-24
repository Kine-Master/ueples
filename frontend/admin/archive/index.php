<?php
require_once '../../../backend/config/functions.php';
requireRole('admin');
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
  <title>Archives — Admin</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>:root{--accent:#7c3aed;--accent-bg:rgba(124,58,237,.12)}</style>
</head>
<body class="page-body">

<header class="main-header">
  <div class="brand"><i class="fa-solid fa-shield-halved"></i><span>ADMIN PORTAL<small class="brand-sub">UEP LES System</small></span></div>
  <nav class="top-nav">
    <a href="../dashboard/index.php"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
    <a href="../user_management/index.php"><i class="fa-solid fa-users-gear"></i> Users</a>
    <a href="../audit_logs/index.php"><i class="fa-solid fa-clock-rotate-left"></i> Audit Logs</a>
    <a href="index.php" class="active"><i class="fa-solid fa-box-archive"></i> Archives</a>
    <a href="../profile/index.php"><i class="fa-solid fa-user-circle"></i> Profile</a>
    <a href="../../../backend/auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
      <button class="theme-btn" id="themeBtn" title="Toggle theme" onclick="toggleTheme()">🌙</button>
  </nav>
</header>

<main class="page-content">
  <div class="page-header">
    <div class="page-header-text"><h2><i class="fa-solid fa-box-archive" style="color:var(--accent)"></i> Archive Management</h2><p>View deactivated (archived) schedules and configure thresholds</p></div>
    <div class="right"><button class="btn btn-secondary" onclick="openThresholdModal()"><i class="fa-solid fa-sliders"></i> Configure Thresholds</button></div>
  </div>

  <!-- Filters -->
  <div class="section-card">
    <div class="toolbar">
      <div class="search-box"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="searchInput" placeholder="Search by teacher, subject…" oninput="loadArchive()"></div>
      <select class="filter-sel" id="typeFilter" onchange="loadArchive()">
        <option value="">All Types</option>
        <option value="LES">LES (Internal)</option>
        <option value="COED">COED (External)</option>
      </select>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>#</th><th>Type</th><th>Teacher</th><th>Subject</th><th>Class / Grade</th><th>Room</th><th>Schedule</th><th>SY / Semester</th><th>Archived</th></tr></thead>
        <tbody id="archiveBody"><tr class="no-data"><td colspan="9"><div class="spinner-wrap"><i class="fa-solid fa-spinner"></i><p>Loading…</p></div></td></tr></tbody>
      </table>
    </div>
    <div class="toolbar" style="justify-content:center;border-top:1px solid var(--border);border-bottom:none">
      <span id="archiveCount" style="font-size:.85rem;color:var(--text-muted)"></span>
    </div>
  </div>
</main>

<!-- Threshold Modal -->
<div class="modal-overlay" id="thresholdModal">
  <div class="modal modal-sm">
    <div class="modal-header"><h3><i class="fa-solid fa-sliders"></i> Threshold Settings</h3><button class="modal-close" onclick="closeModal('thresholdModal')"><i class="fa-solid fa-xmark"></i></button></div>
    <div class="modal-body">
      <div class="form-group"><label class="form-label">Archive schedules older than (months)</label><input type="number" class="form-control" id="archiveThreshold" min="1" max="120" value="6"><p class="form-hint">Used for auto-archiving (future automation feature).</p></div>
      <div class="form-group"><label class="form-label">Delete audit logs older than (months)</label><input type="number" class="form-control" id="deletionThreshold" min="1" max="120" value="12"></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('thresholdModal')">Cancel</button>
      <button class="btn btn-primary" onclick="saveThresholds()"><i class="fa-solid fa-floppy-disk"></i> Save</button>
    </div>
  </div>
</div>

<div id="toastContainer"></div>
<script src="script.js"></script>
</body></html>
