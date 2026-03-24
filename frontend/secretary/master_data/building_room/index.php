<?php
require_once '../../../../backend/config/functions.php';
requireRole('secretary');
?><!DOCTYPE html>
<html lang="en">
<head>
  <script src="../../../assets/js/theme.js"></script>
  
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Buildings & Rooms — Secretary</title>
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
    <a href="index.php" class="active"><i class="fa-solid fa-building"></i> <span>Buildings &amp; Rooms</span></a>
    <a href="../class_section/index.php"><i class="fa-solid fa-users-rectangle"></i> <span>Sections</span></a>
    <a href="../teacher_subject/index.php"><i class="fa-solid fa-tags"></i> <span>Specialties</span></a>
    <a href="../../profile/index.php"><i class="fa-solid fa-user-circle"></i> <span>Profile</span></a>
    <a href="../../../../backend/auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> <span>Logout</span></a>
    <button class="theme-btn" id="themeBtn" title="Toggle theme"></button>
  </nav>
</header>

<main class="page-content" style="max-width:1400px">
  <div class="page-header">
    <div class="page-header-text"><h2><i class="fa-solid fa-building" style="color:var(--accent)"></i> Buildings & Rooms</h2><p>Manage physical facilities and individual room capacities</p></div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 2fr;gap:24px">
    
    <!-- Buildings Column -->
    <div class="section-card" style="align-self:start">
      <div class="section-header">
        <h3><i class="fa-regular fa-building"></i> Buildings</h3>
        <button class="btn btn-primary btn-sm" onclick="openBuildModal()"><i class="fa-solid fa-plus"></i> Add</button>
      </div>
      <div class="table-wrap">
        <table class="data-table">
          <thead><tr><th>Building Name</th><th>Rooms</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody id="buildBody"><tr class="no-data"><td colspan="4">Loadingâ€¦</td></tr></tbody>
        </table>
      </div>
    </div>

    <!-- Rooms Column -->
    <div class="section-card" style="align-self:start">
      <div class="section-header">
        <h3><i class="fa-solid fa-door-open"></i> Rooms</h3>
        <button class="btn btn-primary btn-sm" onclick="openRoomModal()"><i class="fa-solid fa-plus"></i> Add</button>
      </div>
      <div class="toolbar">
        <select class="filter-sel" id="bldgFilter" onchange="loadRooms()"><option value="">All Buildings</option></select>
      </div>
      <div class="table-wrap">
        <table class="data-table">
          <thead><tr><th>#</th><th>Room Name</th><th>Building</th><th>Type</th><th>Capacity</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody id="roomBody"><tr class="no-data"><td colspan="7">Loadingâ€¦</td></tr></tbody>
        </table>
      </div>
    </div>

  </div>
</main>

<!-- Building Modal -->
<div class="modal-overlay" id="buildModal">
  <div class="modal modal-sm">
    <div class="modal-header"><h3 id="buildTitle"><i class="fa-solid fa-plus"></i> Add Building</h3><button class="modal-close" onclick="closeModal('buildModal')"><i class="fa-solid fa-xmark"></i></button></div>
    <form id="buildForm" onsubmit="saveBuild(event)">
      <div class="modal-body">
        <input type="hidden" id="ebId">
        <div class="form-group"><label class="form-label">Building Name <span>*</span></label><input class="form-control" id="fbName" required></div>
        <div class="form-group"><label class="form-label">Description</label><textarea class="form-control" id="fbDesc" rows="2"></textarea></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="closeModal('buildModal')">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
    </form>
  </div>
</div>

<!-- Room Modal -->
<div class="modal-overlay" id="roomModal">
  <div class="modal modal-sm">
    <div class="modal-header"><h3 id="roomTitle"><i class="fa-solid fa-plus"></i> Add Room</h3><button class="modal-close" onclick="closeModal('roomModal')"><i class="fa-solid fa-xmark"></i></button></div>
    <form id="roomForm" onsubmit="saveRoom(event)">
      <div class="modal-body">
        <input type="hidden" id="erId">
        <div class="form-group" id="bldgGroup"><label class="form-label">Building <span>*</span></label><select class="form-control" id="frBldg" required></select></div>
        <div class="form-group"><label class="form-label">Room Name <span>*</span></label><input class="form-control" id="frName" placeholder="e.g. Room 101" required></div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Type <span>*</span></label><select class="form-control" id="frType" required><option value="Lecture">Lecture</option><option value="Lab">Laboratory</option></select></div>
          <div class="form-group"><label class="form-label">Capacity <span>*</span></label><input type="number" class="form-control" id="frCap" min="1" value="40" required></div>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="closeModal('roomModal')">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
    </form>
  </div>
</div>

<!-- Toggle Confirm Modal -->
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
