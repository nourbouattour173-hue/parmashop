<?php
session_start();
$_SESSION = [];
session_destroy();
require_once __DIR__ . '/includes/db.php';
header("Location: " . BASE_URL . "login.php");
exit();
?>
