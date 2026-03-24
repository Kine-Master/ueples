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
  <title>Dashboard — Principal</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root{--accent:#8b5cf6;--accent-bg:rgba(139,92,246,.12)} /* Violet for Principal */
  </style>
</head>
<body class="page-body">

<header class="main-header">
  <div class="brand"><i class="fa-solid fa-school"></i><span>PRINCIPAL PORTAL<small class="brand-sub">UEP LES System</small></span></div>
  <nav class="top-nav">
    <a href="index.php" class="active"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
    <a href="../students/index.php"><i class="fa-solid fa-user-graduate"></i> Students</a>
    <a href="../teachers/index.php"><i class="fa-solid fa-chalkboard-user"></i> Teachers</a>
    <a href="../rooms/index.php"><i class="fa-solid fa-door-open"></i> Rooms</a>
    <a href="../profile/index.php"><i class="fa-solid fa-user-circle"></i> Profile</a>
    <a href="../../../backend/auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
      <button class="theme-btn" id="themeBtn" title="Toggle theme" onclick="toggleTheme()">🌙</button>
  </nav>
</header>

<main class="page-content" style="max-width:1200px">
  <div class="page-header">
    <div class="page-header-text"><h2><i class="fa-solid fa-chart-pie" style="color:var(--accent)"></i> School Overview</h2><p>Welcome, <?= e($_SESSION['full_name']) ?>!</p></div>
  </div>

  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(56,189,248,.15);color:#38bdf8"><i class="fa-solid fa-graduation-cap"></i></div>
      <div class="stat-content"><div class="stat-label">Total Students</div><div class="stat-value" id="stStudents"><i class="fa-solid fa-spinner fa-spin" style="font-size:1rem;color:var(--text-muted)"></i></div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(16,185,129,.15);color:#10b981"><i class="fa-solid fa-chalkboard-user"></i></div>
      <div class="stat-content"><div class="stat-label">Total Teachers</div><div class="stat-value" id="stTeachers"><i class="fa-solid fa-spinner fa-spin" style="font-size:1rem;color:var(--text-muted)"></i></div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(245,158,11,.15);color:#f59e0b"><i class="fa-solid fa-book-open"></i></div>
      <div class="stat-content"><div class="stat-label">Active Schedules</div><div class="stat-value" id="stSchedules"><i class="fa-solid fa-spinner fa-spin" style="font-size:1rem;color:var(--text-muted)"></i></div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(139,92,246,.15);color:#8b5cf6"><i class="fa-solid fa-door-open"></i></div>
      <div class="stat-content"><div class="stat-label">Rooms Available</div><div class="stat-value" id="stRooms"><i class="fa-solid fa-spinner fa-spin" style="font-size:1rem;color:var(--text-muted)"></i></div></div>
    </div>
  </div>

  <div class="section-card" style="margin-top:24px">
    <div class="section-header"><h3><i class="fa-solid fa-satellite-dish" style="color:var(--accent)"></i> Live Tracking Board — Today's Active Classes</h3></div>
    <div class="table-wrap">
      <table class="data-table" id="trackingTable">
        <thead>
          <tr>
            <th>Time</th>
            <th>Type</th>
            <th>Subject</th>
            <th>Teacher</th>
            <th>Section</th>
            <th>Room</th>
          </tr>
        </thead>
        <tbody id="trackingBody">
          <tr class="no-data"><td colspan="6"><div class="spinner-wrap"><i class="fa-solid fa-spinner fa-spin"></i><p>Loading classes...</p></div></td></tr>
        </tbody>
      </table>
    </div>
  </div>
</main>
<script>
  function formatTime(time24) {
    if (!time24) return '';
    const [h, m] = time24.split(':');
    const d = new Date(); d.setHours(h); d.setMinutes(m);
    return d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
  }

  function esc(str) {
    if (!str) return '';
    return String(str).replace(/[&<>'"]/g, match =>
      ({ '&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;' })[match]
    );
  }

  async function loadDashboard() {
    try {
      const res = await fetch('../../../backend/dashboard/stats.php');
      const json = await res.json();
      if (json.status === 'success') {
        const stats = json.data;
        document.getElementById('stStudents').textContent = stats.total_students || 0;
        document.getElementById('stTeachers').textContent = stats.total_teachers || 0;
        document.getElementById('stSchedules').textContent = stats.total_schedules || 0;
        document.getElementById('stRooms').textContent = stats.total_rooms || 0;

        const body = document.getElementById('trackingBody');
        const board = stats.tracking_board;
        if (!board || board.length === 0) {
            body.innerHTML = '<tr class="no-data"><td colspan="6"><div style="padding:40px;opacity:0.6"><i class="fa-regular fa-calendar-check" style="font-size:3rem;margin-bottom:12px"></i><br>No active classes tracked for today.</div></td></tr>';
        } else {
            body.innerHTML = board.map(c => {
               const badge = c.schedule_type === 'LES' ? 'les-badge' : 'coed-badge';
               const timeLbl = `${formatTime(c.time_in)} - ${formatTime(c.time_out)}`;
               const subjLbl = c.schedule_type === 'LES' ? c.subject_name : c.coed_subject;
               const secLbl  = c.schedule_type === 'LES' ? `${c.grade_name} - ${c.section_name}` : c.coed_grade_level;
               const roomLbl = c.schedule_type === 'LES' ? (c.room_name ? c.room_name : 'TBA') : (c.coed_room ? c.coed_room : 'TBA');
               return `
                 <tr>
                   <td style="white-space:nowrap;font-size:0.8rem;color:var(--text-muted)"><i class="fa-regular fa-clock"></i> ${esc(timeLbl)}</td>
                   <td><span class="custom-badge ${badge}">${c.schedule_type}</span></td>
                   <td><strong>${esc(subjLbl)}</strong></td>
                   <td><div style="display:flex;align-items:center;gap:8px"><i class="fa-solid fa-chalkboard-user" style="color:var(--text-muted)"></i> ${esc(c.teacher_name)}</div></td>
                   <td>${esc(secLbl)}</td>
                   <td><div style="display:flex;align-items:center;gap:8px"><i class="fa-solid fa-door-open" style="color:var(--text-muted)"></i> <strong>${esc(roomLbl)}</strong></div></td>
                 </tr>
               `;
            }).join('');
        }
      }
    } catch(e) { console.error(e); }
  }
  document.addEventListener('DOMContentLoaded', loadDashboard);
</script>
</body></html>
