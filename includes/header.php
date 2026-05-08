<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'PharmaShop' ?></title>
    <link rel="stylesheet" href="http://localhost/parapharmacie/css/style.css">
</head>
<body>

<header class="navbar">
    <a href="http://localhost/parapharmacie/index.php" class="logo">💊 Pharma<span>Shop</span></a>
    <nav>
        <a href="http://localhost/parapharmacie/index.php">Accueil</a>
        <a href="http://localhost/parapharmacie/produits.php">Produits</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php
                $nb = 0;
                if (!empty($_SESSION['panier']))
                    foreach ($_SESSION['panier'] as $i) $nb += $i['quantite'];
            ?>
            <a href="http://localhost/parapharmacie/panier.php">🛒 Panier<?= $nb > 0 ? " ($nb)" : '' ?></a>
            <a href="http://localhost/parapharmacie/mon_profil.php">Mon profil</a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="http://localhost/parapharmacie/admin/index.php">⚙️ Admin</a>
            <?php endif; ?>
            <a href="http://localhost/parapharmacie/logout.php" class="btn-deconnexion">Déconnexion</a>
        <?php else: ?>
            <a href="http://localhost/parapharmacie/login.php">Connexion</a>
            <a href="http://localhost/parapharmacie/register.php">Inscription</a>
        <?php endif; ?>
    </nav>
</header>
