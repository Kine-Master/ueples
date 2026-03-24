<?php
require '../../../backend/config/functions.php';
requireRole('teacher');
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
    <title>My Archive History</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="workspace-body">

    <header class="main-header">
        <div class="brand">
            <i class="fa-solid fa-chalkboard-user"></i> TEACHER PORTAL
        </div>
        <nav class="top-nav">
            <a href="../dashboard/index.php">Dashboard</a>
            <a href="../schedule/index.php">My Workload</a>
            <a href="index.php" class="active">Archives</a>
            <a href="../profile/index.php">Profile</a>

            <a href="../../../backend/auth/logout.php" class="btn-logout">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
            <button class="theme-btn" id="themeBtn" title="Toggle theme" onclick="toggleTheme()">🌙</button>
  </nav>
    </header>

    <main class="main-content-centered">
        
        <div class="workspace-container">
            
            <input type="hidden" id="myUserId" value="<?= $_SESSION['user_id'] ?>">

            <div class="page-header">
                <div>
                    <h2>My Schedule History</h2>
                    <p>View your past schedules that have been archived or deleted.</p>
                </div>
            </div>

            <div class="toolbar">
                <div class="search-group">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search subject..." onkeyup="debounceLoad()">
                </div>

                <div class="sort-group">
                    <label>Sort By:</label>
                    <select id="sortBy" onchange="loadArchives()">
                        <option value="date_created">Date Archived (Newest)</option>
                        <option value="school_year">School Year</option>
                        <option value="subject">Subject</option>
                    </select>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="archive-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Type</th>
                            <th>Schedule Details</th>
                            <th>School Year</th>
                            <th>Status</th>
                            <th>Date Archived</th>
                        </tr>
                    </thead>
                    <tbody id="archiveTableBody">
                        </tbody>
                </table>
            </div>

        </div>
    </main>

    <script src="script.js"></script>
</body>
</html>