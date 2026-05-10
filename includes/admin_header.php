<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';

// CSS Versioning
$style_file = __DIR__ . '/../assets/css/style.css';
$style_version = file_exists($style_file) ? filemtime($style_file) : time();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Administration - PharmaShop' ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- CSS Principal -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css?v=<?= $style_version ?>">
</head>
<body class="admin-body">
    <nav class="navbar" style="background: var(--color-secondary); border-bottom: none;">
        <div class="container" style="max-width: 100%;">
            <a href="<?= BASE_URL ?>admin/index.php" class="navbar-brand" style="color: white;">
                <div class="logo-icon" style="background: var(--color-accent);">
                    <i class="fas fa-leaf" style="color: var(--color-secondary);"></i>
                </div>
                Pharma<span style="color: var(--color-accent);">Shop</span> <small style="font-size: 0.5em; opacity: 0.8; margin-left: 10px;">ADMIN</small>
            </a>
            
            <div class="navbar-nav">
                <a href="<?= BASE_URL ?>index.php" class="nav-link" style="color: white;" target="_blank">
                    <i class="fas fa-external-link-alt"></i> Voir le site
                </a>
                <a href="<?= BASE_URL ?>logout.php" class="nav-link" style="color: #ff8a80;">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
    </nav>
