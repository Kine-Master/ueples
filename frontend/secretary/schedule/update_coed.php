<?php
require_once '../../../backend/config/functions.php';
requireRole('secretary');

$schedule_id = $_GET['id'] ?? null;
if (!$schedule_id) { header("Location: index.php"); exit; }
?><!DOCTYPE html>
<html lang="en">
<head>
  <script src="../../assets/js/theme.js"></script>
  
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Edit COED Schedule — Secretary</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root{--accent:#fbbf24;--accent-bg:rgba(245,158,11,.12)}
    .wizard-container{display:grid;grid-template-columns:1fr 340px;gap:24px;}
    @media (max-width:800px) { .wizard-container{grid-template-columns:1fr;} }
    .room-schedule-panel{background:var(--bg-sub);border:1px solid var(--border);border-radius:var(--radius-md);padding:16px;}
    .slot-item{background:rgba(255,255,255,.05);padding:10px;border-radius:var(--radius-sm);margin-bottom:8px;border-left:3px solid var(--accent);font-size:.85rem;}
    .slot-item strong{display:block;color:var(--text);font-size:.9rem;margin-bottom:2px}
  </style>
</head>
<body class="page-body">
<header class="main-header">
  <div class="brand"><i class="fa-solid fa-calendar-days"></i><span>SECRETARY PORTAL</span></div>
  <nav class="top-nav">
    <a href="index.php" class="active"><i class="fa-solid fa-table-cells"></i> Schedules</a>
    <a href="../master_data/index.php"><i class="fa-solid fa-database"></i> Master Data</a>
    <a href="../profile/index.php"><i class="fa-solid fa-user-circle"></i> Profile</a>
    <a href="../../../backend/auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    <button class="theme-btn" id="themeBtn" title="Toggle theme"></button>
  </nav>
</header>

<main class="page-content" style="max-width:1100px">
  <div class="page-header">
    <div class="page-header-text">
      <h2><a href="index.php" style="color:var(--text-sub);margin-right:12px"><i class="fa-solid fa-arrow-left"></i></a> Edit COED Schedule</h2>
    </div>
  </div>

  <div class="wizard-container">
    <div class="section-card">
      <div class="section-header"><h3><i class="fa-solid fa-graduation-cap"></i> Course Details</h3></div>
      <div style="padding:24px">
        <div id="errorBox" class="alert-error" style="display:none;margin-bottom:20px;padding:12px"></div>
        <form id="coedForm" onsubmit="updateSchedule(event)">
          <input type="hidden" id="fId" value="<?= e($schedule_id) ?>">
          
          <div class="form-row">
            <div class="form-group"><label class="form-label">Subject <span>*</span></label><input class="form-control" id="fSubj" required></div>
            <div class="form-group"><label class="form-label">Course & Year <span>*</span></label><input class="form-control" id="fCourse" required></div>
          </div>
          
          <div class="form-row">
            <div class="form-group"><label class="form-label">Teacher <span>*</span></label><select class="form-control" id="fTeacher" required></select></div>
            <div class="form-group"><label class="form-label">Semester <span>*</span></label><select class="form-control" id="fSem" required><option value="1">1st Sem</option><option value="2">2nd Sem</option></select></div>
          </div>
          
          <hr style="border-color:var(--border);margin:24px 0">

          <div class="form-row">
            <div class="form-group"><label class="form-label">Room <span>*</span></label><select class="form-control" id="fRoom" required onchange="fetchRoomSlots()"></select></div>
            <div class="form-group"><label class="form-label">Day <span>*</span></label><select class="form-control" id="fDay" required onchange="fetchRoomSlots()">
                 <option value="Monday">Monday</option><option value="Tuesday">Tuesday</option><option value="Wednesday">Wednesday</option><option value="Thursday">Thursday</option><option value="Friday">Friday</option><option value="Saturday">Saturday</option>
               </select></div>
          </div>

          <div class="form-row">
            <div class="form-group"><label class="form-label">Start Time <span>*</span></label><input type="time" class="form-control" id="fStart" required></div>
            <div class="form-group"><label class="form-label">End Time <span>*</span></label><input type="time" class="form-control" id="fEnd" required></div>
          </div>

          <button type="submit" class="btn btn-warning" style="width:100%;margin-top:12px" id="saveBtn">Save Changes</button>
        </form>
      </div>
    </div>
    
    <div class="room-schedule-panel" id="roomPanel" style="display:none">
      <h3 style="font-size:1.1rem;margin-top:0;margin-bottom:4px"><i class="fa-solid fa-door-open" style="color:var(--accent)"></i> Room Availability</h3>
      <div id="slotsContainer"></div>
    </div>
  </div>
</main>
<div id="toastContainer"></div>
<script src="update_coed.js"></script>
</body></html>
