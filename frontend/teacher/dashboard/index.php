<?php
require_once '../../../backend/config/functions.php';
requireRole('teacher');
// Dashboard removed for teacher role — redirect to schedule index
header('Location: ../schedule/index.php');
exit;
?>