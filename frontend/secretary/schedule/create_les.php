<?php
require_once '../../../backend/config/functions.php';
requireRole('secretary');
?><!DOCTYPE html>
<html lang="en">
<head>
  <script src="../../assets/js/theme.js"></script>
  
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Create LES Schedule — Secretary</title>
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
  <div class="brand"><i class="fa-solid fa-calendar-days"></i><span>SECRETARY PORTAL<small class="brand-sub">UEP LES System</small></span></div>
    <nav class="top-nav">
    <a href="index.php" class="active"><i class="fa-solid fa-table-cells"></i> <span>Schedules</span></a>
    <a href="../master_data/school_year/index.php"><i class="fa-solid fa-calendar"></i> <span>School Years</span></a>
    <a href="../master_data/curriculum/index.php"><i class="fa-solid fa-book"></i> <span>Curricula</span></a>
    <a href="../master_data/subject/index.php"><i class="fa-solid fa-book-open"></i> <span>Subjects</span></a>
    <a href="../master_data/building_room/index.php"><i class="fa-solid fa-building"></i> <span>Buildings &amp; Rooms</span></a>
    <a href="../master_data/class_section/index.php"><i class="fa-solid fa-users-rectangle"></i> <span>Sections</span></a>
    <a href="../profile/index.php"><i class="fa-solid fa-user-circle"></i> <span>Profile</span></a>
    <a href="../../../backend/auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> <span>Logout</span></a>
    <button class="theme-btn" id="themeBtn" title="Toggle theme"></button>
  </nav>
</header>

<main class="page-content" style="max-width:1100px">
  <div class="page-header">
    <div class="page-header-text">
      <h2><a href="index.php" style="color:var(--text-sub);margin-right:12px"><i class="fa-solid fa-arrow-left"></i></a> Create LES Schedule</h2>
      <p>Laboratory Elementary School internal schedule</p>
    </div>
  </div>

  <div class="wizard-container">
    
    <!-- Form Side -->
    <div class="section-card">
      <div class="section-header"><h3><i class="fa-solid fa-file-signature"></i> Schedule Details</h3></div>
      <div style="padding:24px">
        <div id="errorBox" class="alert-error" style="display:none;margin-bottom:20px;padding:12px;background:rgba(239,68,68,.1);color:#fca5a5;border-left:3px solid #ef4444;border-radius:4px"></div>
        <form id="lesForm" onsubmit="createSchedule(event)">
          
          <div class="form-group">
            <label class="form-label">Subject <span>*</span></label>
            <select class="form-control" id="fSubj" required onchange="checkTeacherAvailability()"><option value="">Select subjectâ€¦</option></select>
          </div>
          
          <div class="form-group">
            <label class="form-label">Class Section <span>*</span></label>
            <select class="form-control" id="fSec" required><option value="">Select sectionâ€¦</option></select>
          </div>
          
          <div class="form-row">
            <div class="form-group"><label class="form-label">Teacher <span>*</span></label><select class="form-control" id="fTeacher" required><option value="">Select teacherâ€¦</option></select></div>
            <div class="form-group"><label class="form-label">Semester <span>*</span></label><select class="form-control" id="fSem" required><option value="1">1st Semester</option><option value="2">2nd Semester</option></select></div>
          </div>
          
          <hr style="border-color:var(--border);margin:24px 0">

          <div class="form-row">
            <div class="form-group">
               <label class="form-label">Room <span>*</span></label>
               <select class="form-control" id="fRoom" required onchange="fetchRoomSlots()"><option value="">Select roomâ€¦</option></select>
            </div>
            <div class="form-group">
               <label class="form-label">Day of Week <span>*</span></label>
               <select class="form-control" id="fDay" required onchange="fetchRoomSlots()">
                 <option value="">Select dayâ€¦</option>
                 <option value="Monday">Monday</option><option value="Tuesday">Tuesday</option><option value="Wednesday">Wednesday</option><option value="Thursday">Thursday</option><option value="Friday">Friday</option><option value="Saturday">Saturday</option>
               </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group"><label class="form-label">Start Time <span>*</span></label><input type="time" class="form-control" id="fStart" required onchange="checkTeacherAvailability()"></div>
            <div class="form-group"><label class="form-label">End Time <span>*</span></label><input type="time" class="form-control" id="fEnd" required onchange="checkTeacherAvailability()"></div>
          </div>

          <button type="submit" class="btn btn-primary" style="width:100%;margin-top:12px" id="saveBtn">Create Schedule</button>
        </form>
      </div>
    </div>

    <!-- Live Preview Side -->
    <div class="room-schedule-panel" id="roomPanel" style="display:none">
      <h3 style="font-size:1.1rem;margin-top:0;margin-bottom:4px"><i class="fa-solid fa-door-open" style="color:var(--accent)"></i> Room Availability</h3>
      <p style="font-size:.85rem;color:var(--text-sub);margin-bottom:16px" id="roomSubText">Select a room and day</p>
      
      <div id="slotsContainer">
         <div style="text-align:center;padding:30px;color:var(--text-muted);font-size:.85rem">
            Select a room and day to view occupied slots.
         </div>
      </div>
    </div>

  </div>

</main>

<div id="toastContainer"></div>
<script src="create_les.js"></script>
</body></html>
