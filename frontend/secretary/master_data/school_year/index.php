<?php
require_once '../../../../backend/config/functions.php';
requireRole('secretary');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <script src="../../../assets/js/theme.js"></script>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>School Years — Secretary</title>
  <link rel="stylesheet" href="../../../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root {
      --accent: #fbbf24;
      --accent-bg: rgba(245, 158, 11, .12)
    }
  </style>
</head>

<body class="page-body">

  <header class="main-header">
    <div class="brand"><i class="fa-solid fa-calendar-days"></i><span>SECRETARY PORTAL<small class="brand-sub">UEP LES
          System</small></span></div>
    <nav class="top-nav">
      <a href="../../schedule/index.php"><i class="fa-solid fa-table-cells"></i> <span>Schedules</span></a>
      <a href="index.php" class="active"><i class="fa-solid fa-calendar"></i> <span>School Years</span></a>
      <a href="../curriculum/index.php"><i class="fa-solid fa-book"></i> <span>Curricula</span></a>
      <a href="../subject/index.php"><i class="fa-solid fa-book-open"></i> <span>Subjects</span></a>
      <a href="../building_room/index.php"><i class="fa-solid fa-building"></i> <span>Buildings &amp; Rooms</span></a>
      <a href="../class_section/index.php"><i class="fa-solid fa-users-rectangle"></i> <span>Sections</span></a>
      <a href="../../profile/index.php"><i class="fa-solid fa-user-circle"></i> <span>Profile</span></a>
      <a href="../../../../backend/auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i>
        <span>Logout</span></a>
      <button class="theme-btn" id="themeBtn" title="Toggle theme"></button>
    </nav>
  </header>

  <main class="page-content" style="max-width:800px">
    <div class="page-header">
      <div class="page-header-text">
        <h2><i class="fa-solid fa-calendar" style="color:var(--accent)"></i> School Years</h2>
        <p>Manage academic years and set the active one</p>
      </div>
      <div class="right"><button class="btn btn-primary" onclick="openAddModal()"><i class="fa-solid fa-plus"></i> Add
          School Year</button></div>
    </div>

    <div class="section-card">
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>#</th>
              <th>School Year Label</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="syBody">
            <tr class="no-data">
              <td colspan="4">
                <div class="spinner-wrap"><i class="fa-solid fa-spinner"></i>
                  <p>Loadingâ€¦</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <div class="modal-overlay" id="addModal">
    <div class="modal modal-sm">
      <div class="modal-header">
        <h3><i class="fa-solid fa-plus"></i> Add School Year</h3><button class="modal-close"
          onclick="closeModal('addModal')"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <form id="syForm" onsubmit="saveSY(event)">
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">School Year Label <span>*</span></label>
            <input class="form-control" id="fLabel" placeholder="e.g. 2025-2026" required pattern="\d{4}-\d{4}">
            <p class="form-hint">Must be in YYYY-YYYY format.</p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal-overlay" id="activeModal">
    <div class="modal modal-sm">
      <div class="modal-header">
        <h3><i class="fa-solid fa-bolt"></i> Set Active SY</h3><button class="modal-close"
          onclick="closeModal('activeModal')"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <p id="activeMsg" style="color:var(--text-sub);font-size:.9rem"></p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" onclick="closeModal('activeModal')">Cancel</button>
        <button class="btn btn-success" id="activeConfirmBtn" onclick="doSetActive()">Confirm</button>
      </div>
    </div>
  </div>

  <div id="toastContainer"></div>
  <script src="script.js"></script>
</body>

</html>