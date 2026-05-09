<?php
require_once __DIR__ . '/includes/session.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$brands_stmt = $pdo->query("SELECT nom, logo FROM brands ORDER BY nom LIMIT 8");
$brands_ap   = $brands_stmt->fetchAll();

$page_title = 'À Propos';
require_once 'includes/header.php';
require_once 'views/apropos.html';
require_once 'includes/footer.php';
