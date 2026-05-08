<?php
session_start();
$_SESSION = [];
session_destroy();
header("Location: http://localhost/parapharmacie/login.php");
exit();
?>
