<?php 
if (session_status() === PHP_SESSION_NONE) session_start(); 
require_once __DIR__ . '/db.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'PharmaShop' ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>

<header class="navbar">
    <a href="<?= BASE_URL ?>index.php" class="logo">💊 Pharma<span>Shop</span></a>
    <nav>
        <a href="<?= BASE_URL ?>index.php">Accueil</a>
        <a href="<?= BASE_URL ?>produits.php">Produits</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php
                $nbPanier = 0;
                if (!empty($_SESSION['panier']))
                    foreach ($_SESSION['panier'] as $i) $nbPanier += $i['quantite'];
                
                $nbFav = 0;
                if (isset($_SESSION['user_id'])) {
                    $fStmt = $pdo->prepare("SELECT COUNT(*) FROM favoris WHERE user_id = ?");
                    $fStmt->execute([$_SESSION['user_id']]);
                    $nbFav = $fStmt->fetchColumn();
                }
            ?>
            <a href="<?= BASE_URL ?>panier.php">🛒 Panier<?= $nbPanier > 0 ? " ($nbPanier)" : '' ?></a>
            <a href="<?= BASE_URL ?>favoris.php">❤️ Favoris<?= $nbFav > 0 ? " ($nbFav)" : '' ?></a>
            <a href="<?= BASE_URL ?>mon_profil.php">Mon profil</a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="<?= BASE_URL ?>admin/index.php">⚙️ Admin</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>logout.php" class="btn-deconnexion">Déconnexion</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>login.php">Connexion</a>
            <a href="<?= BASE_URL ?>register.php">Inscription</a>
        <?php endif; ?>
    </nav>
</header>
<?php include __DIR__ . '/navigator.php'; ?>
