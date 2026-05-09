<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin - PharmaShop' ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="admin-body">
<header class="admin-topbar">
    <div class="container flex justify-between align-center">
        <div class="admin-logo">
            <i class="bi bi-shield-lock"></i> Pharma<span>Shop</span> Admin
        </div>
        <div class="admin-nav-links">
            <a href="<?= BASE_URL ?>logout.php" class="btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-right"></i> Déconnexion
            </a>
        </div>
    </div>
</header>
