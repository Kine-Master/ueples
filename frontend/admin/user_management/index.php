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
  <title>User Management — Admin</title>
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
    <a href="index.php" class="active"><i class="fa-solid fa-users-gear"></i> Users</a>
    <a href="../audit_logs/index.php"><i class="fa-solid fa-clock-rotate-left"></i> Audit Logs</a>
    <a href="../archive/index.php"><i class="fa-solid fa-box-archive"></i> Archives</a>
    <a href="../profile/index.php"><i class="fa-solid fa-user-circle"></i> Profile</a>
    <a href="../../../backend/auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
      <button class="theme-btn" id="themeBtn" title="Toggle theme" onclick="toggleTheme()">🌙</button>
  </nav>
</header>

<main class="page-content">
  <div class="page-header">
    <div class="page-header-text"><h2><i class="fa-solid fa-users-gear" style="color:var(--accent)"></i> User Management</h2><p>Create, edit, and manage all system accounts</p></div>
    <div class="right"><button class="btn btn-primary" onclick="openAddModal()"><i class="fa-solid fa-plus"></i> Add User</button></div>
  </div>

  <div class="section-card">
    <div class="toolbar">
      <div class="search-box"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="searchInput" placeholder="Search by name or username…" oninput="filterUsers()"></div>
      <select class="filter-sel" id="roleFilter" onchange="filterUsers()">
        <option value="">All Roles</option>
        <option value="1">Admin</option>
        <option value="2">Principal</option>
        <option value="3">Secretary</option>
        <option value="4">Teacher</option>
      </select>
      <select class="filter-sel" id="statusFilter" onchange="filterUsers()">
        <option value="">All Status</option>
        <option value="1">Active</option>
        <option value="0">Inactive</option>
      </select>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>#</th><th>Name</th><th>Username</th><th>Role</th><th>Department</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
        <tbody id="userBody"><tr class="no-data"><td colspan="8"><div class="spinner-wrap"><i class="fa-solid fa-spinner"></i><p>Loading users…</p></div></td></tr></tbody>
      </table>
    </div>
  </div>
</main>

<!-- ── Add / Edit Modal ───────────────────────────────────────── -->
<div class="modal-overlay" id="userModal">
  <div class="modal">
    <div class="modal-header"><h3 id="modalTitle"><i class="fa-solid fa-user-plus"></i> Add User</h3><button class="modal-close" onclick="closeModal('userModal')"><i class="fa-solid fa-xmark"></i></button></div>
    <form id="userForm" onsubmit="saveUser(event)">
      <div class="modal-body">
        <input type="hidden" id="editUserId">
        <div class="form-group"><label class="form-label">Role <span>*</span></label><select class="form-control" id="fRole" required><option value="">Select role…</option><option value="1">Admin</option><option value="2">Principal</option><option value="3">Secretary</option><option value="4">Teacher</option></select></div>
        <div class="form-section-title">Personal Information</div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Last Name <span>*</span></label><input class="form-control" id="fLastName" placeholder="Dela Cruz" required></div>
          <div class="form-group"><label class="form-label">First Name <span>*</span></label><input class="form-control" id="fFirstName" placeholder="Juan" required></div>
        </div>
        <div class="form-group"><label class="form-label">Middle Name</label><input class="form-control" id="fMiddleName" placeholder="Optional"></div>
        <div class="form-section-title">Account Credentials</div>
        <div class="form-group"><label class="form-label">Username <span>*</span></label><input class="form-control" id="fUsername" placeholder="jdelacruz" autocomplete="off"></div>
        <div class="form-group" id="passwordGroup"><label class="form-label">Password <span>*</span></label><input type="password" class="form-control" id="fPassword" placeholder="Min. 6 characters" autocomplete="new-password"><p class="form-hint">Leave blank to keep existing password when editing.</p></div>
        <div class="form-section-title">Optional Details</div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Email</label><input class="form-control" id="fEmail" type="email" placeholder="juan@uep.edu.ph"></div>
          <div class="form-group"><label class="form-label">Academic Rank</label><input class="form-control" id="fRank" placeholder="Instructor I"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">School / College</label><input class="form-control" id="fSchool" placeholder="CEIT"></div>
          <div class="form-group"><label class="form-label">Department</label><input class="form-control" id="fDept" placeholder="Computer Science"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('userModal')">Cancel</button>
        <button type="submit" class="btn btn-primary" id="saveBtn"><i class="fa-solid fa-floppy-disk"></i> Save User</button>
      </div>
    </form>
  </div>
</div>

<!-- ── Reset Password Modal ──────────────────────────────────── -->
<div class="modal-overlay" id="resetModal">
  <div class="modal modal-sm">
    <div class="modal-header"><h3><i class="fa-solid fa-key"></i> Reset Password</h3><button class="modal-close" onclick="closeModal('resetModal')"><i class="fa-solid fa-xmark"></i></button></div>
    <div class="modal-body">
      <p style="color:var(--text-sub);font-size:.875rem;margin-bottom:16px">Set a new password for <strong id="resetUserName"></strong>.</p>
      <div class="form-group"><label class="form-label">New Password <span>*</span></label><input type="password" class="form-control" id="resetNewPass" placeholder="Min. 6 characters"></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('resetModal')">Cancel</button>
      <button class="btn btn-warning" onclick="doResetPassword()"><i class="fa-solid fa-key"></i> Reset Password</button>
    </div>
  </div>
</div>

<!-- ── Toggle Status Confirm ─────────────────────────────────── -->
<div class="modal-overlay" id="toggleModal">
  <div class="modal modal-sm">
    <div class="modal-header"><h3 id="toggleTitle"><i class="fa-solid fa-power-off"></i> Confirm Action</h3><button class="modal-close" onclick="closeModal('toggleModal')"><i class="fa-solid fa-xmark"></i></button></div>
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
