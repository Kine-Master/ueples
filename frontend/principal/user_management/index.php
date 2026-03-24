<?php
require '../../../backend/config/functions.php';
requireRole('principal');
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="workspace-body">

    <header class="main-header">
        <div class="brand">
            <i class="fa-solid fa-user-tie"></i> PRINCIPAL PORTAL
        </div>
        <nav class="top-nav">
            <a href="../dashboard/index.php">Dashboard</a>
            <a href="index.php" class="active">Manage Users</a>
            <a href="../archive/index.php">Archives</a>
            <a href="../profile/index.php">Profile</a>

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
                    <h2>User Management</h2>
                    <p>Manage faculty and staff accounts.</p>
                </div>
                <button onclick="openAddModal()" class="btn-primary">
                    <i class="fa-solid fa-plus"></i> Add User
                </button>
            </div>

            <div class="toolbar">
                <div class="search-group">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search name or username..." onkeyup="debounceLoad()">
                </div>
                <div class="filter-group">
                    <select id="roleFilter" onchange="loadUsers()">
                        <option value="">All Roles</option>
                        <option value="3">Teachers</option>
                        <option value="2">Secretaries</option>
                        <option value="1">Principals</option>
                    </select>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Username</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        </tbody>
                </table>
            </div>

        </div>
    </main>

    <?php include 'modal.php'; ?>

    <script src="script.js"></script>
</body>
</html>
