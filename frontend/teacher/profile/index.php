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
  <title>My Profile — Teacher</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root{--accent:#10b981;--accent-bg:rgba(16,185,129,.12)} /* Emerald Green */
    @media (max-width: 700px) { .page-content > div[style*="grid"] { grid-template-columns: 1fr !important; } }
  </style>
</head>
<body class="page-body">

<header class="main-header">
  <div class="brand"><i class="fa-solid fa-chalkboard-user"></i><span>TEACHER PORTAL<small class="brand-sub">UEP LES System</small></span></div>
  <nav class="top-nav">
    <a href="../schedule/index.php"><i class="fa-solid fa-calendar-day"></i> My Schedule</a>
    <a href="../classes/index.php"><i class="fa-solid fa-users"></i> My Classes</a>
    <a href="index.php" class="active"><i class="fa-solid fa-user-circle"></i> Profile</a>
    <a href="../../../backend/auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
      <button class="theme-btn" id="themeBtn" title="Toggle theme" onclick="toggleTheme()">🌙</button>
  </nav>
</header>

<main class="page-content" style="max-width:900px">
  <div class="page-header">
    <div class="page-header-text"><h2><i class="fa-solid fa-user-circle" style="color:var(--accent)"></i> My Profile</h2><p>Manage your account information and password</p></div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
    
    <div class="section-card">
      <div class="section-header"><h3><i class="fa-solid fa-id-card"></i> Personal Information</h3></div>
      <div style="padding:24px">
        <div id="profileAvatar" style="text-align:center;margin-bottom:24px">
          <div style="width:80px;height:80px;border-radius:50%;background:var(--accent-bg);border:2px solid var(--accent);display:flex;align-items:center;justify-content:center;font-size:2rem;color:var(--accent);margin:0 auto 12px"><i class="fa-solid fa-user-pen"></i></div>
          <div style="font-weight:700;font-size:1.1rem" id="profileFullName">—</div>
          <div><span class="badge badge-success" style="margin-top:6px">Teacher</span></div>
        </div>
        <form id="profileForm" onsubmit="saveProfile(event)">
          <div class="form-row"><div class="form-group"><label class="form-label">Last Name <span>*</span></label><input class="form-control" id="pLastName" name="last_name" required></div><div class="form-group"><label class="form-label">First Name <span>*</span></label><input class="form-control" id="pFirstName" name="first_name" required></div></div>
          <div class="form-group"><label class="form-label">Middle Name</label><input class="form-control" id="pMiddleName" name="middle_name"></div>
          <div class="form-group"><label class="form-label">Email</label><input class="form-control" id="pEmail" name="email" type="email"></div>
          <div class="form-group"><label class="form-label">Academic Rank</label><input class="form-control" id="pRank" name="academic_rank" placeholder="e.g. Teacher III"></div>
          <div class="form-row">
            <div class="form-group"><label class="form-label">School / College</label><input class="form-control" id="pSchool" name="school_college" placeholder="e.g. UEP LES"></div>
            <div class="form-group"><label class="form-label">Department / Subjects</label><input class="form-control" id="pDept" name="department" placeholder="e.g. Science Department"></div>
          </div>
          <button type="submit" class="btn btn-primary" style="width:100%"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
        </form>
      </div>
    </div>

    <div class="section-card" style="align-self:start">
      <div class="section-header"><h3><i class="fa-solid fa-lock"></i> Change Password</h3></div>
      <div style="padding:24px">
        <form id="passwordForm" onsubmit="changePassword(event)">
          <div class="form-group"><label class="form-label">Current Password <span>*</span></label><input type="password" class="form-control" id="cCurrentPass" name="current_password" required></div>
          <div class="form-group"><label class="form-label">New Password <span>*</span></label><input type="password" class="form-control" id="cNewPass" name="new_password" placeholder="Min. 6 characters" required></div>
          <div class="form-group"><label class="form-label">Confirm New Password <span>*</span></label><input type="password" class="form-control" id="cConfirmPass" name="confirm_password" required></div>
          <button type="submit" class="btn btn-danger" style="width:100%"><i class="fa-solid fa-key"></i> Update Password</button>
        </form>
      </div>
    </div>

  </div>
</main>
<div id="toastContainer"></div>
<script src="../../admin/profile/script.js"></script>
</body></html>
