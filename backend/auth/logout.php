<?php
// backend/auth/logout.php
require_once __DIR__ . '/../config/functions.php';

// Log before destroying session
if (isset($_SESSION['user_id'])) {
    logAudit($_SESSION['user_id'], 'LOGOUT', 'User logged out');
}

// Destroy session completely
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

header("Location: ../../frontend/login/index.php");
exit;
?>