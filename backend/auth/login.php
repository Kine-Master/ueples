<?php
// backend/auth/login.php
require_once __DIR__ . '/../config/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../frontend/login/index.php");
    exit;
}

// 1. CSRF Check
if (!isset($_POST['csrf_token'])) {
    header("Location: ../../frontend/login/index.php?error=Security+token+missing");
    exit;
}
verifyCsrfToken($_POST['csrf_token']);

// 2. Get credentials
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header("Location: ../../frontend/login/index.php?error=Please+fill+in+all+fields");
    exit;
}

// 3. Database lookup — only active users
$sql = "SELECT u.user_id, u.password_hash, u.first_name, u.last_name, u.role_id, r.role_name
        FROM user u
        JOIN role r ON u.role_id = r.role_id
        WHERE u.username = ? AND u.is_active = 1";
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    header("Location: ../../frontend/login/index.php?error=Database+error");
    exit;
}

// 4. Verify password
if (!$user || !password_verify($password, $user['password_hash'])) {
    header("Location: ../../frontend/login/index.php?error=Invalid+credentials");
    exit;
}

// 5. Set session
session_regenerate_id(true);
$_SESSION['user_id']   = $user['user_id'];
$_SESSION['role_id']   = (int)$user['role_id'];
$_SESSION['role']      = strtolower($user['role_name']);
$_SESSION['full_name'] = trim($user['first_name'] . ' ' . $user['last_name']);

// 6. Audit log
logAudit($user['user_id'], 'LOGIN', 'Logged in from ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));

// 7. Redirect by role
$redirectMap = [
    'admin'     => '../../frontend/admin/dashboard/index.php',
    'principal' => '../../frontend/principal/dashboard/index.php',
    'secretary' => '../../frontend/secretary/schedule/index.php',
    'teacher'   => '../../frontend/teacher/dashboard/index.php',
];

$dest = $redirectMap[$_SESSION['role']] ?? '../../frontend/login/index.php?error=Unknown+role';
header("Location: $dest");
exit;
?>