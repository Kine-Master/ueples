<?php
require '../../../backend/config/functions.php';
requireRole('secretary');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <script src="../../assets/js/theme.js"></script>
  
    <title>Schedule Archives</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="workspace-body">

    <header class="main-header">
        <div class="brand">
            <i class="fa-solid fa-user-pen"></i> SECRETARY PORTAL
        </div>
        <nav class="top-nav">
            <a href="index.php" class="active">Archives</a>
            <a href="../profile/index.php">Profile</a>

            <a href="../../../backend/auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> <span>Logout
            </span></a>
            <button class="theme-btn" id="themeBtn" title="Toggle theme"></button>
  </nav>
    </header>

    <main class="main-content-centered">
        
        <div class="archive-container">
            
            <div class="page-header">
                <div>
                    <h2>Archived Schedules</h2>
                    <p>History of dissolved, deleted, or expired classes.</p>
                </div>
                <button onclick="openSettings()" class="btn-settings">
                    <i class="fa-solid fa-cog"></i> Archive Settings
                </button>
            </div>

            <div class="toolbar">
                <div class="search-group">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search subject or teacher..." onkeyup="debounceLoad()">
                </div>

                <div class="sort-group">
                    <label>Sort By:</label>
                    <select id="sortBy" onchange="loadArchives()">
                        <option value="date_created">Date Archived (Newest)</option>
                        <option value="school_year">School Year</option>
                        <option value="teacher_name">Teacher Name</option>
                        <option value="subject">Subject</option>
                    </select>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="archive-table">
                    <thead>
                        <tr>
                            <th>Teacher</th>
                            <th>Subject</th>
                            <th>Type</th>
                            <th>Schedule Details</th>
                            <th>Status</th>
                            <th>School Year</th>
                        </tr>
                    </thead>
                    <tbody id="archiveTableBody">
                        <tr>
                            <td colspan="6" style="text-align:center; padding:40px; color:#999;">
                                <i class="fa-solid fa-spinner fa-spin" style="font-size:24px; margin-bottom:10px;"></i><br>
                                Loading archives...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </main>

    <!-- Archive Settings Modal -->
    <div id="settingsModal" class="modal" style="display:none;">
        <div class="modal-content small-modal">
            <h3>Archive Policy</h3>
            <p>Define the age threshold for automatically archiving old schedules.</p>
            
            <form id="settingsForm">
                <div style="margin:20px 0;">
                    <label>Archive schedules older than:</label>
                    <select name="threshold_months" id="thresholdInput">
                        <option value="6">6 Months (1 Semester)</option>
                        <option value="12">12 Months (1 Year)</option>
                        <option value="24">24 Months (2 Years)</option>
                        <option value="36">36 Months (3 Years)</option>
                    </select>
                </div>
                
                <div class="modal-actions">
                    <button type="button" onclick="closeSettings()">Cancel</button>
                    <button type="submit" class="btn-primary">Save Policy</button>
                </div>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
    
</body>
</html>
