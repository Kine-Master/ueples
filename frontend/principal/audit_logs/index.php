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
    <title>System Audit Log</title>
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
            <a href="index.php" class="active"><i class="fa-solid fa-clock-rotate-left"></i> Audit Logs</a>
            <a href="../profile/index.php"><i class="fa-solid fa-user-circle"></i> Profile</a>

            <a href="../../../backend/auth/logout.php" class="btn-logout">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
            <button class="theme-btn" id="themeBtn" title="Toggle theme" onclick="toggleTheme()">🌙</button>
  </nav>
    </header>

    <main class="main-content-centered">
        <div class="workspace-container">
            
            <div class="page-header">
                <div>
                    <h2>System Audit Logs</h2>
                    <p>Monitor user activities, system changes, and security events.</p>
                </div>
                <div class="actions">
                     <button onclick="openSettingsModal()" class="btn-secondary">
                        <i class="fa-solid fa-gear"></i> Settings
                    </button>
                    <button onclick="generateReport()" class="btn-primary">
                        <i class="fa-solid fa-print"></i> Generate Report
                    </button>
                </div>
            </div>

            <div id="settingsModal" class="modal">
                <div class="modal-content">
                    
                    <div class="modal-header">
                        <h3>Audit Settings</h3>
                        <span onclick="closeSettingsModal()" class="close-btn">&times;</span>
                    </div>

                    <div class="modal-body">
                        <p>Configure the automatic retention policy for audit logs. Logs older than this threshold will be eligible for deletion.</p>

                        <div class="modal-input-group">
                            <label>Auto-Delete Threshold (Months)</label>
                            <input type="number" id="retentionMonths" class="modal-input" min="1" placeholder="e.g. 12">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button onclick="runCleanup()" class="btn-modal-danger">
                            <i class="fa-solid fa-trash"></i> Run Cleanup
                        </button>
                        <button onclick="saveSettings()" class="btn-modal-save">
                            Save Changes
                        </button>
                    </div>

                </div>
            </div>

            <div class="toolbar" style="display:flex; gap:15px; flex-wrap:wrap; padding:15px; background:white; border:1px solid #ddd;">
                
                <div class="filter-group">
                    <label>Search:</label>
                    <input type="text" id="searchInput" placeholder="User, Action..." onkeyup="debounceLoad()">
                </div>

                <div class="filter-group">
                    <label>Action Type:</label>
                    <select id="actionFilter" onchange="loadLogs()">
                        <option value="">All Actions</option>
                        </select>
                </div>

                <div class="filter-group">
                    <label>From:</label>
                    <input type="date" id="startDate" onchange="loadLogs()">
                </div>

                <div class="filter-group">
                    <label>To:</label>
                    <input type="date" id="endDate" onchange="loadLogs()">
                </div>
                
                <button onclick="resetFilters()">Reset</button>
            </div>

            <div class="table-wrapper">
                <table style="width:100%; text-align:left; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:2px solid #ddd;">
                            <th>ID</th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Action</th>
                            <th>IP Address</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody id="auditTableBody">
                        </tbody>
                </table>
            </div>

            <div id="paginationControls" style="margin-top:20px; display:flex; gap:10px; justify-content:center;">
                <button onclick="changePage(-1)" id="btnPrev">Previous</button>
                <span id="pageIndicator">Page 1</span>
                <button onclick="changePage(1)" id="btnNext">Next</button>
            </div>

        </div>
    </main>

    <script src="script.js"></script>
</body>
</html>
