<?php
require_once '../../../backend/config/functions.php';
requireRole('secretary');
header('Location: ../schedule/index.php');
exit;
?>
