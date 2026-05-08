<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/db.php';
$pageTitle = "Mon Panier - PharmaShop";
require_once __DIR__ . '/includes/header.php';

if (isset($_GET['supprimer'])) {
    unset($_SESSION['panier'][$_GET['supprimer']]);
    header("Location: " . BASE_URL . "panier.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['maj_panier'])) {
    foreach ($_POST['quantites'] as $cle => $qte) {
        $qte = (int)$qte;
        if ($qte <= 0) unset($_SESSION['panier'][$cle]);
        else           $_SESSION['panier'][$cle]['quantite'] = $qte;
    }
    header("Location: " . BASE_URL . "panier.php");
    exit();
}

$panier = $_SESSION['panier'] ?? [];
$total  = 0;
foreach ($panier as $item) $total += $item['prix'] * $item['quantite'];
?>

<div class="container">
    <h1 class="section-title">🛒 Mon Panier</h1>

    <?php if (empty($panier)): ?>
        <div class="alert alert-info">
            Votre panier est vide.
            <a href="<?= BASE_URL ?>produits.php">→ Continuer mes achats</a>
        </div>
    <?php else: ?>
        <form method="POST">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Contenance</th>
                        <th>Prix unitaire</th>
                        <th>Quantité</th>
                        <th>Sous-total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($panier as $cle => $item): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($item['nom']) ?></strong></td>
                            <td><?= htmlspecialchars($item['contenance'] ?? '') ?></td>
                            <td><?= number_format($item['prix'], 2) ?> DT</td>
                            <td>
                                <input type="number"
                                       name="quantites[<?= htmlspecialchars($cle) ?>]"
                                       value="<?= $item['quantite'] ?>"
                                       min="0" max="50"
                                       style="width:65px; padding:5px; border:1px solid #ccc; border-radius:4px;">
                            </td>
                            <td><strong style="color:#2e7d32;"><?= number_format($item['prix'] * $item['quantite'], 2) ?> DT</strong></td>
                            <td>
                                <a href="<?= BASE_URL ?>panier.php?supprimer=<?= urlencode($cle) ?>"
                                   class="btn-danger" style="font-size:13px;"
                                   onclick="return confirm('Supprimer cet article ?')">🗑️</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div style="text-align:right; margin-top:10px;">
            <button type="submit" name="maj_panier" class="btn-warning">🔄 Mettre à jour</button>
        </div>
        </form>

        <div style="text-align:right; font-size:22px; font-weight:bold; color:#2e7d32; margin:20px 0;">
            Total : <?= number_format($total, 2) ?> DT
        </div>
        <div style="text-align:right;">
            <a href="<?= BASE_URL ?>produits.php" style="color:#888; margin-right:20px;">← Continuer mes achats</a>
            <a href="<?= BASE_URL ?>commande.php" class="btn-primary" style="font-size:16px; padding:12px 30px;">
                ✅ Passer la commande
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
