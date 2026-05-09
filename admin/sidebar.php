<?php $p = basename($_SERVER['PHP_SELF']); ?>
<div class="admin-sidebar">
    <div class="sidebar-logo">Pharma<span>Shop</span></div>
    <nav>
        <a href="<?= BASE_URL ?>admin/index.php"        class="<?= $p==='index.php'?'active':'' ?>"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
        <a href="<?= BASE_URL ?>admin/produits.php"     class="<?= $p==='produits.php'?'active':'' ?>"><i class="bi bi-box-seam"></i> Produits</a>
        <a href="<?= BASE_URL ?>admin/ajouter_produit.php" class="<?= $p==='ajouter_produit.php'?'active':'' ?>"><i class="bi bi-plus-circle"></i> Ajouter produit</a>
        <a href="<?= BASE_URL ?>admin/commandes.php"    class="<?= $p==='commandes.php'?'active':'' ?>"><i class="bi bi-cart"></i> Commandes</a>
        <a href="<?= BASE_URL ?>admin/utilisateurs.php" class="<?= $p==='utilisateurs.php'?'active':'' ?>"><i class="bi bi-people"></i> Utilisateurs</a>
        <div class="sidebar-divider"></div>
        <a href="<?= BASE_URL ?>index.php"><i class="bi bi-globe"></i> Voir le site</a>
        <a href="<?= BASE_URL ?>logout.php" class="btn-deconnexion-sidebar"><i class="bi bi-box-arrow-right"></i> Déconnexion</a>
    </nav>
</div>
