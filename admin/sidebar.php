<?php $p = basename($_SERVER['PHP_SELF']); ?>
<div class="admin-sidebar">
    <div style="padding:20px 25px; color:#a5d6a7; font-size:12px; font-weight:bold; letter-spacing:1px;">ADMINISTRATION</div>
    <a href="http://localhost/parapharmacie/admin/index.php"        class="<?= $p==='index.php'?'active':'' ?>">📊 Tableau de bord</a>
    <a href="http://localhost/parapharmacie/admin/produits.php"     class="<?= $p==='produits.php'?'active':'' ?>">📦 Produits</a>
    <a href="http://localhost/parapharmacie/admin/ajouter_produit.php" class="<?= $p==='ajouter_produit.php'?'active':'' ?>">➕ Ajouter produit</a>
    <a href="http://localhost/parapharmacie/admin/commandes.php"    class="<?= $p==='commandes.php'?'active':'' ?>">🛒 Commandes</a>
    <a href="http://localhost/parapharmacie/admin/utilisateurs.php" class="<?= $p==='utilisateurs.php'?'active':'' ?>">👥 Utilisateurs</a>
    <div style="border-top:1px solid #2e7d32; margin:15px 0;"></div>
    <a href="http://localhost/parapharmacie/index.php">🌐 Voir le site</a>
    <a href="http://localhost/parapharmacie/logout.php" style="color:#ef9a9a;">🚪 Déconnexion</a>
</div>
