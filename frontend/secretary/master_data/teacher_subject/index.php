<?php
require_once '../../../../backend/config/functions.php';
requireRole('secretary');
?><!DOCTYPE html>
<html lang="en">
<head>
  <script src="../../../assets/js/theme.js"></script>
  
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Teacher Specialties — Secretary</title>
  <link rel="stylesheet" href="../../../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root{--accent:#fbbf24;--accent-bg:rgba(245,158,11,.12)}
  </style>
</head>
<body class="page-body">

<header class="main-header">
  <div class="brand"><i class="fa-solid fa-calendar-days"></i><span>SECRETARY PORTAL<small class="brand-sub">UEP LES System</small></span></div>
    <nav class="top-nav">
    <a href="../../schedule/index.php"><i class="fa-solid fa-table-cells"></i> <span>Schedules</span></a>
    <a href="../school_year/index.php"><i class="fa-solid fa-calendar"></i> <span>School Years</span></a>
    <a href="../curriculum/index.php"><i class="fa-solid fa-book"></i> <span>Curricula</span></a>
    <a href="../subject/index.php"><i class="fa-solid fa-book-open"></i> <span>Subjects</span></a>
    <a href="../building_room/index.php"><i class="fa-solid fa-building"></i> <span>Buildings &amp; Rooms</span></a>
    <a href="../class_section/index.php"><i class="fa-solid fa-users-rectangle"></i> <span>Sections</span></a>
    <a href="index.php" class="active"><i class="fa-solid fa-tags"></i> <span>Specialties</span></a>
    <a href="../../profile/index.php"><i class="fa-solid fa-user-circle"></i> <span>Profile</span></a>
    <a href="../../../../backend/auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> <span>Logout</span></a>
    <button class="theme-btn" id="themeBtn" title="Toggle theme"></button>
  </nav>
</header>

<main class="page-content">
  <div class="page-header">
    <div class="page-header-text"><h2><i class="fa-solid fa-tags" style="color:var(--accent)"></i> Teacher Specialties</h2><p>Assign subject specializations to teachers to enable scheduling conflict detection.</p></div>
    <div class="right"><button class="btn btn-primary" onclick="openAssignModal()"><i class="fa-solid fa-link"></i> Assign Subject</button></div>
  </div>

  <div class="section-card">
    <div class="toolbar">
      <select class="filter-sel" id="teacherFilter" onchange="loadSpecs()"><option value="">All Teachers</option></select>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Teacher Name</th><th>Subject</th><th>Curriculum Name</th><th>Grade Level</th><th>Actions</th></tr></thead>
        <tbody id="specBody"><tr class="no-data"><td colspan="5"><div class="spinner-wrap"><i class="fa-solid fa-spinner"></i><p>Loadingâ€¦</p></div></td></tr></tbody>
      </table>
    </div>
  </div>
</main>

<div class="modal-overlay" id="assignModal">
  <div class="modal modal-sm">
    <div class="modal-header"><h3><i class="fa-solid fa-link"></i> Assign Specialty</h3><button class="modal-close" onclick="closeModal('assignModal')"><i class="fa-solid fa-xmark"></i></button></div>
    <form id="assignForm" onsubmit="saveAssign(event)">
      <div class="modal-body">
        <div class="form-group"><label class="form-label">Teacher <span>*</span></label><select class="form-control" id="fTeacher" required></select></div>
        <div class="form-group"><label class="form-label">Subject <span>*</span></label><select class="form-control" id="fSubject" required></select></div>
        <div class="alert-info" style="margin-top:12px;font-size:.85rem;display:flex;gap:8px;padding:12px;background:rgba(56,189,248,.1);border-left:3px solid #38bdf8;border-radius:4px;color:var(--text-sub)"><i class="fa-solid fa-circle-info" style="color:#38bdf8"></i><span>Assigning a specialty means the teacher will appear as a qualified option when creating schedules for this subject.</span></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="closeModal('assignModal')">Cancel</button><button type="submit" class="btn btn-primary">Assign</button></div>
    </form>
  </div>
</div>

<div id="toastContainer"></div>
<script src="script.js"></script>
</body></html>
