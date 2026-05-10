<?php
require_once __DIR__ . '/includes/auth_check.php';
$pageTitle = "Commande - PharmaShop";
require_once __DIR__ . '/includes/db.php';

$panier = $_SESSION['panier'] ?? [];#sion table vide
if (empty($panier)) { header("Location: " . BASE_URL . "panier.php"); exit(); }

$total = 0;
foreach ($panier as $item) $total += $item['prix'] * $item['quantite'];

$erreur = "";
$succes = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adresse  = trim($_POST['adresse']);
    $paiement = $_POST['methode_paiement'];
    $note     = trim($_POST['note'] ?? '');

    if (empty($adresse)) {
        $erreur = "Veuillez saisir une adresse de livraison.";
    } else {
     
        $pdo->prepare("
            INSERT INTO orders (user_id, total, adresse_livraison, methode_paiement, note_commande, statut)
            VALUES (?, ?, ?, ?, ?, 'en_attente')
        ")->execute([$_SESSION['user_id'], $total, $adresse, $paiement, $note]);

        $orderId = $pdo->lastInsertId();

        // Insérer les articles 
        foreach ($panier as $item) {
            $pdo->prepare("INSERT INTO order_items (order_id, variant_id, quantite, prix_unitaire) VALUES (?,?,?,?)")
                ->execute([$orderId, $item['variant_id'], $item['quantite'], $item['prix']]);
            $pdo->prepare("UPDATE product_variants SET stock = stock - ? WHERE id = ?")#baisser le stock
                ->execute([$item['quantite'], $item['variant_id']]);
        }

        $_SESSION['panier'] = [];
        $succes = true;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width:800px;">
    <h1 class="section-title">Finaliser ma commande</h1>

    <?php if ($succes): ?>
        <div class="alert alert-success" style="text-align:center; padding:30px; font-size:17px;">
            <strong>Commande passée avec succès !</strong><br><br>
            Vous serez notifié(e) dès l'expédition.<br><br>
            <a href="<?= BASE_URL ?>index.php" class="btn-primary">← Retour à l'accueil</a>
        </div>
    <?php else: ?>
        <?php if ($erreur): ?>
            <div class="alert alert-error"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <div class="table-container" style="margin-bottom:25px;">
            <h3 style="color:#2e7d32; margin-bottom:15px;">Récapitulatif</h3>
            <table>
                <thead><tr><th>Produit</th><th>Contenance</th><th>Qté</th><th>Prix</th></tr></thead>
                <tbody>
                    <?php foreach ($panier as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['nom']) ?></td>
                            <td><?= htmlspecialchars($item['contenance'] ?? '') ?></td>
                            <td><?= $item['quantite'] ?></td>
                            <td><?= number_format($item['prix'] * $item['quantite'], 2) ?> DT</td>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="background:#f1f8f1;">
                        <td colspan="3"><strong>TOTAL</strong></td>
                        <td><strong style="color:#2e7d32;"><?= number_format($total, 2) ?> DT</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="table-container">
            <h3 style="color:#2e7d32; margin-bottom:20px;">Informations de livraison</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Adresse de livraison *</label>
                    <textarea name="adresse" required placeholder="Ex: 12 Rue de la Paix, Tunis"></textarea>
                </div>
                <div class="form-group">
                    <label>Méthode de paiement</label>
                    <select name="methode_paiement">
                        <option value="carte">Carte bancaire</option>
                        <option value="virement">Virement bancaire</option>
                        <option value="especes">Paiement à la livraison</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Note (optionnel)</label>
                    <textarea name="note" placeholder="Instructions spéciales..."></textarea>
                </div>
                <div style="text-align:right;">
                    <a href="<?= BASE_URL ?>panier.php" style="color:#888; margin-right:20px;">← Retour</a>
                    <button type="submit" class="btn-primary" style="font-size:16px; padding:12px 30px;">
                        Confirmer (<?= number_format($total, 2) ?> DT)
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
