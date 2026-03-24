<?php
require '../../../backend/config/functions.php';
requireRole('principal');
header('Location: ../dashboard/index.php');
exit;
?>
<!DOCTYPE html>
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
    <title>Faculty Workloads</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="workspace-body">

    <header class="main-header">
        <div class="brand">
            <i class="fa-solid fa-user-tie"></i> PRINCIPAL PORTAL
        </div>
        <nav class="top-nav">
            <a href="../dashboard/index.php"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
            <a href="../students/index.php"><i class="fa-solid fa-user-graduate"></i> Students</a>
            <a href="../teachers/index.php"><i class="fa-solid fa-chalkboard-user"></i> Teachers</a>
            <a href="../rooms/index.php"><i class="fa-solid fa-door-open"></i> Rooms</a>
            <a href="index.php" class="active"><i class="fa-solid fa-chart-column"></i> Workloads</a>
            <a href="../profile/index.php"><i class="fa-solid fa-user-circle"></i> Profile</a>

            <a href="../../../backend/auth/logout.php" class="btn-logout">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
            <button class="theme-btn" id="themeBtn" title="Toggle theme" onclick="toggleTheme()">🌙</button>
  </nav>
    </header>

    <main class="main-content-full">
        <div class="split-layout">
            
            <aside class="teacher-panel">
                <div class="panel-header">
                    <h3>Faculty List</h3>
                    <div class="search-box">
                        <i class="fa-solid fa-search"></i>
                        <input type="text" id="searchTeacher" placeholder="Search faculty...">
                    </div>
                </div>
                <div id="teacherList" class="list-container">
                    <div class="loading">Loading faculty...</div>
                </div>
            </aside>

            <section class="workspace-panel">
                
                <div id="emptyState" class="empty-state">
                    <i class="fa-solid fa-chalkboard-user"></i>
                    <h2>Select a Faculty Member</h2>
                    <p>Click on a teacher to view their current workload and schedule.</p>
                </div>

                <div id="scheduleWorkspace" style="display:none;">
                    
                    <div class="ws-header">
                        <div>
                            <h2 id="selectedTeacherName">Teacher Name</h2>
                            <div class="meta-badges">
                                <span id="selectedTeacherDept" class="badge dept-badge">Department</span>
                                <span class="badge sy-badge">SY: <span id="displaySY">...</span></span>
                            </div>
                        </div>
                        <div class="ws-actions">
                            <button onclick="printReport()" class="btn-primary">
                                <i class="fa-solid fa-print"></i> Generate Report
                            </button>
                        </div>
                    </div>

                    <div class="toolbar">
                        <div class="view-toggle">
                            <button class="toggle-btn active" onclick="switchView('grid')" title="Timetable View">
                                <i class="fa-solid fa-calendar-week"></i> Grid
                            </button>
                            <button class="toggle-btn" onclick="switchView('list')" title="List View">
                                <i class="fa-solid fa-list"></i> List
                            </button>
                        </div>

                        <div class="control-group">
                            <label><i class="fa-solid fa-filter"></i> Semester:</label>
                            <select id="filterSemester" onchange="reloadData()">
                                <option value="1" selected>1st Semester</option>
                                <option value="2">2nd Semester</option>
                                <option value="Summer">Summer</option>
                            </select>
                        </div>

                        <div class="legend-inline">
                            <span><span class="dot les"></span> LES</span>
                            <span><span class="dot coed"></span> COED</span>
                        </div>
                    </div>

                    <div class="content-area">
                        <div id="viewGrid" class="view-section">
                            <div class="timetable-grid" id="gridContent"></div>
                        </div>

                        <div id="viewList" class="view-section" style="display:none;">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>Type</th>
                                        <th>Day & Time</th>
                                        <th>Room</th>
                                        <th>Sem</th>
                                    </tr>
                                </thead>
                                <tbody id="listContent"></tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </section>
        </div>
    </main>

    <script src="script.js"></script>
</body>
</html>
