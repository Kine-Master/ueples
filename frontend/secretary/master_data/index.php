<?php
require_once '../../../backend/config/functions.php';
requireRole('secretary');
$module = $_GET['module'] ?? 'school_year';
$allowed = ['school_year','curriculum','subject','class_section','building_room','teacher_subject'];
if (!in_array($module, $allowed, true)) $module = 'school_year';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <script src="../../assets/js/theme.js"></script>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Master Data — Secretary</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root{--accent:#fbbf24;--accent-bg:rgba(245,158,11,.12)}
    .md-wrap{display:grid;grid-template-columns:260px 1fr;gap:18px}
    .md-side{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:12px;align-self:start}
    .md-link{display:flex;align-items:center;gap:8px;padding:10px 12px;border-radius:8px;color:var(--text-sub);font-weight:600}
    .md-link:hover{background:var(--bg-ele);color:var(--text)}
    .md-link.active{background:var(--accent-bg);color:var(--accent)}
    .md-pane{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;min-height:760px}
    .md-pane iframe{width:100%;height:760px;border:0;background:var(--bg)}
    @media (max-width:980px){.md-wrap{grid-template-columns:1fr}.md-pane iframe{height:900px}}
  </style>
</head>
<body class="page-body">
<header class="main-header">
  <div class="brand"><i class="fa-solid fa-calendar-days"></i><span>SECRETARY PORTAL<small class="brand-sub">UEP LES System</small></span></div>
  <nav class="top-nav">
    <a href="../schedule/index.php"><i class="fa-solid fa-table-cells"></i> <span>Schedules</span></a>
    <a href="index.php" class="active"><i class="fa-solid fa-database"></i> <span>Master Data</span></a>
    <a href="../profile/index.php"><i class="fa-solid fa-user-circle"></i> <span>Profile</span></a>
    <a href="../../../backend/auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> <span>Logout</span></a>
    <button class="theme-btn" id="themeBtn" title="Toggle theme"></button>
  </nav>
</header>

<main class="page-content" style="max-width:1400px">
  <div class="page-header"><div class="page-header-text"><h2><i class="fa-solid fa-database" style="color:var(--accent)"></i> Master Data</h2><p>Manage school-year setup, curricular structure, rooms, sections, and specialties.</p></div></div>
  <div class="md-wrap">
    <aside class="md-side">
      <a class="md-link <?= $module==='school_year'?'active':'' ?>" href="?module=school_year"><i class="fa-solid fa-calendar"></i> School Years</a>
      <a class="md-link <?= $module==='curriculum'?'active':'' ?>" href="?module=curriculum"><i class="fa-solid fa-book"></i> Curricula</a>
      <a class="md-link <?= $module==='subject'?'active':'' ?>" href="?module=subject"><i class="fa-solid fa-book-open"></i> Subjects</a>
      <a class="md-link <?= $module==='class_section'?'active':'' ?>" href="?module=class_section"><i class="fa-solid fa-users-rectangle"></i> Sections</a>
      <a class="md-link <?= $module==='building_room'?'active':'' ?>" href="?module=building_room"><i class="fa-solid fa-building"></i> Buildings & Rooms</a>
      <a class="md-link <?= $module==='teacher_subject'?'active':'' ?>" href="?module=teacher_subject"><i class="fa-solid fa-tags"></i> Specialties</a>
    </aside>
    <section class="md-pane">
      <iframe id="moduleFrame" src="<?= e('./' . $module . '/index.php?embed=1') ?>" title="Master Data Module"></iframe>
    </section>
  </div>
</main>
<script>
  const frame = document.getElementById('moduleFrame');
  function stripEmbeddedHeader() {
    try {
      const doc = frame.contentDocument || frame.contentWindow.document;
      const header = doc.querySelector('.main-header');
      if (header) header.style.display = 'none';
      if (doc.body) {
        doc.body.style.paddingTop = '0';
      }
      const page = doc.querySelector('.page-content');
      if (page) page.style.paddingTop = '20px';
    } catch (e) {}
  }
  frame.addEventListener('load', stripEmbeddedHeader);
</script>
</body>
</html>
