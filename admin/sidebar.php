<?php $p = basename($_SERVER['PHP_SELF']); ?>
<div class="admin-sidebar">
    <nav>
        <a href="<?= BASE_URL ?>admin/index.php"            class="<?= $p==='index.php'?'active':'' ?>"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
        <a href="<?= BASE_URL ?>admin/produits.php"         class="<?= $p==='produits.php'?'active':'' ?>"><i class="fas fa-box"></i> Produits</a>
        <a href="<?= BASE_URL ?>admin/commandes.php"        class="<?= $p==='commandes.php'?'active':'' ?>"><i class="fas fa-shopping-cart"></i> Commandes</a>
        <a href="<?= BASE_URL ?>admin/utilisateurs.php"     class="<?= $p==='utilisateurs.php'?'active':'' ?>"><i class="fas fa-users"></i> Utilisateurs</a>
        <a href="<?= BASE_URL ?>admin/messages.php"         class="<?= $p==='messages.php'?'active':'' ?>"><i class="fas fa-envelope"></i> Messages</a>
        <div class="sidebar-divider"></div>
    </nav>
</div>
