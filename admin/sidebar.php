<?php $p = basename($_SERVER['PHP_SELF']); ?>
<div class="admin-sidebar">
    <div class="sidebar-logo">Pharma<span>Shop</span></div>
    <nav>
        <a href="<?= BASE_URL ?>admin/index.php"        class="<?= $p==='index.php'?'active':'' ?>">📊 Tableau de bord</a>
        <a href="<?= BASE_URL ?>admin/produits.php"     class="<?= $p==='produits.php'?'active':'' ?>">📦 Produits</a>
        <a href="<?= BASE_URL ?>admin/ajouter_produit.php" class="<?= $p==='ajouter_produit.php'?'active':'' ?>">➕ Ajouter produit</a>
        <a href="<?= BASE_URL ?>admin/commandes.php"    class="<?= $p==='commandes.php'?'active':'' ?>">🛒 Commandes</a>
        <a href="<?= BASE_URL ?>admin/utilisateurs.php" class="<?= $p==='utilisateurs.php'?'active':'' ?>">👥 Utilisateurs</a>
        <div class="sidebar-divider"></div>
        <a href="<?= BASE_URL ?>index.php">🌐 Voir le site</a>
        <a href="<?= BASE_URL ?>logout.php" class="btn-deconnexion-sidebar">🚪 Déconnexion</a>
    </nav>
</div>
