<?php
require_once '../../../../backend/config/functions.php';
requireRole('secretary');
?><!DOCTYPE html>
<html lang="en">
<head>
  <script src="../../../assets/js/theme.js"></script>
  
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Class Sections — Secretary</title>
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
    <a href="index.php" class="active"><i class="fa-solid fa-users-rectangle"></i> <span>Sections</span></a>
    <a href="../teacher_subject/index.php"><i class="fa-solid fa-tags"></i> <span>Specialties</span></a>
    <a href="../../profile/index.php"><i class="fa-solid fa-user-circle"></i> <span>Profile</span></a>
    <a href="../../../../backend/auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> <span>Logout</span></a>
    <button class="theme-btn" id="themeBtn" title="Toggle theme"></button>
  </nav>
</header>

<main class="page-content">
  <div class="page-header">
    <div class="page-header-text"><h2><i class="fa-solid fa-users-rectangle" style="color:var(--accent)"></i> Class Sections</h2><p>Manage advisory classes and student groups per SY and Grade</p></div>
    <div class="right"><button class="btn btn-primary" onclick="openAddModal()"><i class="fa-solid fa-plus"></i> Add Section</button></div>
  </div>

  <div class="section-card">
    <div class="toolbar" style="gap:12px; display:flex">
      <select class="filter-sel" id="syFilter" onchange="loadSections()"><option value="">All School Years</option></select>
      <select class="filter-sel" id="grFilter" onchange="loadSections()"><option value="">All Grade Levels</option></select>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>#</th><th>Section Name</th><th>SY</th><th>Grade Level</th><th>Adviser</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody id="secBody"><tr class="no-data"><td colspan="7"><div class="spinner-wrap"><i class="fa-solid fa-spinner"></i><p>Loadingâ€¦</p></div></td></tr></tbody>
      </table>
    </div>
  </div>
</main>

<div class="modal-overlay" id="secModal">
  <div class="modal modal-md">
    <div class="modal-header"><h3 id="modalTitle"><i class="fa-solid fa-plus"></i> Add Section</h3><button class="modal-close" onclick="closeModal('secModal')"><i class="fa-solid fa-xmark"></i></button></div>
    <form id="secForm" onsubmit="saveSec(event)">
      <div class="modal-body">
        <input type="hidden" id="editId">
        <div class="form-row" id="creationGroup">
          <div class="form-group"><label class="form-label">School Year <span>*</span></label><select class="form-control" id="fSy" required></select></div>
          <div class="form-group"><label class="form-label">Grade Level <span>*</span></label><select class="form-control" id="fGr" required></select></div>
        </div>
        <div class="form-group">
          <label class="form-label">Section Name <span>*</span></label>
          <input class="form-control" id="fName" placeholder="e.g. Newton, Rizal" required>
        </div>
        <div class="form-group">
          <label class="form-label">Adviser (Optional)</label>
          <select class="form-control" id="fAdv"><option value="">None</option></select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('secModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="toggleModal">
  <div class="modal modal-sm">
    <div class="modal-header"><h3><i class="fa-solid fa-power-off"></i> Confirm Action</h3><button class="modal-close" onclick="closeModal('toggleModal')"><i class="fa-solid fa-xmark"></i></button></div>
    <div class="modal-body"><p id="toggleMsg" style="color:var(--text-sub);font-size:.9rem"></p></div>
    <div class="modal-footer"><button class="btn btn-secondary" onclick="closeModal('toggleModal')">Cancel</button><button class="btn" id="toggleConfirmBtn" onclick="doToggle()">Confirm</button></div>
  </div>
</div>

<div id="toastContainer"></div>
<script src="script.js"></script>
</body></html>
