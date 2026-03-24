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
  <title>Admin Dashboard — UEP LES</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>:root{--accent:#7c3aed;--accent-bg:rgba(124,58,237,.12)}</style>
</head>
<body class="page-body">

<header class="main-header">
  <div class="brand"><i class="fa-solid fa-shield-halved"></i><span>ADMIN PORTAL<small class="brand-sub">UEP LES System</small></span></div>
  <nav class="top-nav">
    <a href="index.php" class="active"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
    <a href="../user_management/index.php"><i class="fa-solid fa-users-gear"></i> Users</a>
    <a href="../audit_logs/index.php"><i class="fa-solid fa-clock-rotate-left"></i> Audit Logs</a>
    <a href="../archive/index.php"><i class="fa-solid fa-box-archive"></i> Archives</a>
    <a href="../profile/index.php"><i class="fa-solid fa-user-circle"></i> Profile</a>
    <a href="../../../backend/auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
      <button class="theme-btn" id="themeBtn" title="Toggle theme" onclick="toggleTheme()">🌙</button>
  </nav>
</header>

<main class="page-content">
  <div class="page-header">
    <div class="page-header-text">
      <h2>Welcome, <span id="adminName"><?= e($_SESSION['full_name']) ?></span></h2>
      <p>System administration overview</p>
    </div>
    <div class="right" id="currentDate"></div>
  </div>

  <!-- Stats -->
  <div class="stats-grid" id="statsGrid">
    <div class="stat-card"><div class="stat-icon purple"><i class="fa-solid fa-users"></i></div><div class="stat-info"><h3>Total Users</h3><div class="val" id="totalUsers">—</div><small>Active accounts</small></div></div>
    <div class="stat-card"><div class="stat-icon red"><i class="fa-solid fa-shield-halved"></i></div><div class="stat-info"><h3>Admins</h3><div class="val" id="countAdmin">—</div><small>System admins</small></div></div>
    <div class="stat-card"><div class="stat-icon blue"><i class="fa-solid fa-user-tie"></i></div><div class="stat-info"><h3>Principals</h3><div class="val" id="countPrincipal">—</div><small>School principals</small></div></div>
    <div class="stat-card"><div class="stat-icon amber"><i class="fa-solid fa-user-pen"></i></div><div class="stat-info"><h3>Secretaries</h3><div class="val" id="countSecretary">—</div><small>Schedulers</small></div></div>
    <div class="stat-card"><div class="stat-icon green"><i class="fa-solid fa-chalkboard-user"></i></div><div class="stat-info"><h3>Teachers</h3><div class="val" id="countTeacher">—</div><small>Faculty</small></div></div>
    <div class="stat-card"><div class="stat-icon cyan"><i class="fa-solid fa-calendar-check"></i></div><div class="stat-info"><h3>Active SY</h3><div class="val" id="activeSY" style="font-size:1.1rem;font-weight:700;">—</div><small>Current school year</small></div></div>
  </div>

  <!-- Recent Audit Activity -->
  <div class="section-card">
    <div class="section-header">
      <h3><i class="fa-solid fa-clock-rotate-left"></i> Recent System Activity</h3>
      <a href="../audit_logs/index.php" class="btn btn-secondary btn-sm">View All</a>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>User</th><th>Role</th><th>Action</th><th>Details</th><th>Time</th></tr></thead>
        <tbody id="auditBody"><tr class="no-data"><td colspan="5"><div class="spinner-wrap"><i class="fa-solid fa-spinner"></i><p>Loading...</p></div></td></tr></tbody>
      </table>
    </div>
  </div>
</main>

<div id="toastContainer"></div>
<script src="script.js"></script>
</body></html>
