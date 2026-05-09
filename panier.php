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
    <h1 class="section-title"><i class="fas fa-shopping-cart"></i> Mon Panier</h1>

    <?php if (empty($panier)): ?>
        <div class="alert alert-info">
            Votre panier est vide.
            <a href="<?= BASE_URL ?>produits.php">Continuer mes achats</a>
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
                                       class="cart-qty-input">
                            </td>
                            <td><strong class="text-primary"><?= number_format($item['prix'] * $item['quantite'], 2) ?> DT</strong></td>
                            <td>
                                <a href="<?= BASE_URL ?>panier.php?supprimer=<?= urlencode($cle) ?>"
                                   class="btn-danger btn-icon"
                                   onclick="return confirm('Supprimer cet article ?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="text-right mt-lg">
            <button type="submit" name="maj_panier" class="btn-warning"><i class="fas fa-sync-alt"></i> Mettre à jour</button>
        </div>
        </form>

        <div class="text-right font-bold mt-lg mb-lg" style="font-size:22px; color:var(--color-primary-dark);">
            Total : <?= number_format($total, 2) ?> DT
        </div>
        <div class="text-right">
            <a href="<?= BASE_URL ?>produits.php" class="text-muted mr-lg">← Continuer mes achats</a>
            <a href="<?= BASE_URL ?>commande.php" class="btn-primary" style="font-size:16px; padding:12px 30px;">
                Passer la commande
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
