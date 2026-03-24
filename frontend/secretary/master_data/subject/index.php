<?php
require_once '../../../../backend/config/functions.php';
requireRole('secretary');
?><!DOCTYPE html>
<html lang="en">
<head>
  <script src="../../../assets/js/theme.js"></script>
  
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Subjects — Secretary</title>
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
    <a href="index.php" class="active"><i class="fa-solid fa-book-open"></i> <span>Subjects</span></a>
    <a href="../building_room/index.php"><i class="fa-solid fa-building"></i> <span>Buildings &amp; Rooms</span></a>
    <a href="../class_section/index.php"><i class="fa-solid fa-users-rectangle"></i> <span>Sections</span></a>
    <a href="../teacher_subject/index.php"><i class="fa-solid fa-tags"></i> <span>Specialties</span></a>
    <a href="../../profile/index.php"><i class="fa-solid fa-user-circle"></i> <span>Profile</span></a>
    <a href="../../../../backend/auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> <span>Logout</span></a>
    <button class="theme-btn" id="themeBtn" title="Toggle theme"></button>
  </nav>
</header>

<main class="page-content">
  <div class="page-header">
    <div class="page-header-text"><h2><i class="fa-solid fa-book-open" style="color:var(--accent)"></i> Subjects</h2><p>Manage subjects associated with curricula</p></div>
    <div class="right"><button class="btn btn-primary" onclick="openAddModal()"><i class="fa-solid fa-plus"></i> Add Subject</button></div>
  </div>

  <div class="section-card">
    <div class="toolbar">
      <select class="filter-sel" id="currFilter" onchange="loadSubjects()">
        <option value="">All Curricula</option>
      </select>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>#</th><th>Subject Name</th><th>Curriculum Name</th><th>Grade Level</th><th>Units</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody id="subjBody"><tr class="no-data"><td colspan="7"><div class="spinner-wrap"><i class="fa-solid fa-spinner"></i><p>Loadingâ€¦</p></div></td></tr></tbody>
      </table>
    </div>
  </div>
</main>

<div class="modal-overlay" id="subjModal">
  <div class="modal modal-sm">
    <div class="modal-header"><h3 id="modalTitle"><i class="fa-solid fa-plus"></i> Add Subject</h3><button class="modal-close" onclick="closeModal('subjModal')"><i class="fa-solid fa-xmark"></i></button></div>
    <form id="subjForm" onsubmit="saveSubj(event)">
      <div class="modal-body">
        <input type="hidden" id="editId">
        <div class="form-group">
          <label class="form-label">Subject Name <span>*</span></label>
          <input class="form-control" id="fName" placeholder="e.g. Mathematics 1" required>
        </div>
        <div class="form-group" id="currGroup">
          <label class="form-label">Curriculum <span>*</span></label>
          <select class="form-control" id="fCurr" required></select>
        </div>
        <div class="form-group">
          <label class="form-label">Units</label>
          <input type="number" step="0.01" class="form-control" id="fUnits" placeholder="1.0" value="1.0">
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea class="form-control" id="fDesc" rows="3" placeholder="Optional notes"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('subjModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="toggleModal">
  <div class="modal modal-sm">
    <div class="modal-header"><h3><i class="fa-solid fa-power-off"></i> Confirm Action</h3><button class="modal-close" onclick="closeModal('toggleModal')"><i class="fa-solid fa-xmark"></i></button></div>
    <div class="modal-body"><p id="toggleMsg" style="color:var(--text-sub);font-size:.9rem"></p></div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('toggleModal')">Cancel</button>
      <button class="btn" id="toggleConfirmBtn" onclick="doToggle()">Confirm</button>
    </div>
  </div>
</div>

<div id="toastContainer"></div>
<script src="script.js"></script>
</body></html>
