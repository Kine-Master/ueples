<?php
// backend/config/db.php

require_once __DIR__ . '/env.php';

$rootDir = dirname(__DIR__, 2);
loadEnvFile($rootDir . '/.env');
loadEnvFile($rootDir . '/.env.local');

$host = getenv('UEP_DB_HOST') ?: 'localhost';
$port = getenv('UEP_DB_PORT') ?: '3306';
$db   = getenv('UEP_DB_NAME') ?: 'ueples_scheduling_system';
$user = getenv('UEP_DB_USER') ?: 'root';
$pass = getenv('UEP_DB_PASS') ?: 'your_password';
$tz   = getenv('UEP_TIMEZONE') ?: 'Asia/Manila';

if ($pass === false) { $pass = ''; }
if ($tz !== '') { date_default_timezone_set($tz); }

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Database Connection Failed: ' . $e->getMessage());
}
?>
