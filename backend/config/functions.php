<?php
/**
 * UEP LES Scheduling System v2.0
 * Core functions, constants, and session helpers.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

// ── Role ID Constants ───────────────────────────────────────────────────────
define('ROLE_ADMIN',     1);
define('ROLE_PRINCIPAL', 2);
define('ROLE_SECRETARY', 3);
define('ROLE_TEACHER',   4);

$ROLE_NAMES = [
    ROLE_ADMIN     => 'admin',
    ROLE_PRINCIPAL => 'principal',
    ROLE_SECRETARY => 'secretary',
    ROLE_TEACHER   => 'teacher',
];

// ── CSRF ───────────────────────────────────────────────────────────────────
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): void {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        die("Security Error: Invalid CSRF token. Please refresh the page.");
    }
}

// ── Output Sanitizer ────────────────────────────────────────────────────────
function e(string $string): string {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// ── Role Guard (for frontend PHP pages — redirects on failure) ──────────────
function requireRole($roles): void {
    $allowed = is_array($roles) ? $roles : [$roles];
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed, true)) {
        header("Location: /ueples/frontend/login/index.php?error=Unauthorized");
        exit;
    }
}

// ── Role Guard (for backend API endpoints — returns JSON 403 on failure) ────
function requireRoleApi($roles): void {
    $allowed = is_array($roles) ? $roles : [$roles];
    // Ensure session is started and role is set
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed, true)) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Insufficient permissions.']);
        exit;
    }
}

// ── Session Role ID Helper ──────────────────────────────────────────────────
function getRoleId(): int {
    if (isset($_SESSION['role_id'])) {
        return (int)$_SESSION['role_id'];
    }
    $map = ['admin' => ROLE_ADMIN, 'principal' => ROLE_PRINCIPAL, 'secretary' => ROLE_SECRETARY, 'teacher' => ROLE_TEACHER];
    return $map[strtolower($_SESSION['role'] ?? '')] ?? 0;
}

// ── Active School Year Helper ───────────────────────────────────────────────
function getActiveSchoolYear(): ?array {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT school_year_id, label FROM school_year WHERE is_active = 1 LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (PDOException $e) {
        return null;
    }
}

// ── Audit Logging ───────────────────────────────────────────────────────────
function logAudit(int $userId, string $action, ?string $details = null): void {
    global $pdo;
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $pdo->prepare("INSERT INTO audit_log (user_id, user_action, details, ip_address) VALUES (?, ?, ?, ?)")
            ->execute([$userId, $action, $details, $ip]);
    } catch (PDOException $e) {
        error_log("Audit Log Error: " . $e->getMessage());
    }
}
?>