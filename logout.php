<?php
session_start();
$_SESSION = [];
session_destroy();


setcookie("derniere_connexion", "", time() - 3600, "/", "", false, true);
setcookie("vue_produits",       "", time() - 3600, "/", "", false, true);
require_once __DIR__ . '/includes/db.php';
header("Location: " . BASE_URL . "login.php");
exit();
?>
