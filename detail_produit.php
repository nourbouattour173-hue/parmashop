<?php
$pageTitle = "Produit - PharmaShop";
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';

$id = (int)($_GET['id'] ?? 0);
if ($id === 0) { header("Location: " . BASE_URL . "produits.php"); exit(); }

// Récupérer le produit
$stmt = $pdo->prepare("
    SELECT p.*, b.nom AS marque, c.nom AS categorie
    FROM products p
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$produit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produit) {
    echo "<div class='container'><div class='alert alert-error'>Produit introuvable.</div></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit();
}

// Variantes — colonne exacte : contenance
$vStmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY prix");
$vStmt->execute([$id]);
$variantes = $vStmt->fetchAll(PDO::FETCH_ASSOC);

// Image principale
$iStmt = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ? LIMIT 1");
$iStmt->execute([$id]);
$image = $iStmt->fetchColumn();

// Ajout au panier
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_panier'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
    $variantId = (int)$_POST['variant_id'];
    $quantite  = max(1, (int)$_POST['quantite']);

    $chk = $pdo->prepare("SELECT * FROM product_variants WHERE id = ? AND product_id = ?");
    $chk->execute([$variantId, $id]);
    $variant = $chk->fetch(PDO::FETCH_ASSOC);

    if ($variant) {
        if ($variant['stock'] < $quantite) {
            $msg = "error:Stock insuffisant ({$variant['stock']} disponible(s)).";
        } else {
            if (!isset($_SESSION['panier'])) $_SESSION['panier'] = [];
            $cle = "v$variantId";
            if (isset($_SESSION['panier'][$cle])) {
                $_SESSION['panier'][$cle]['quantite'] += $quantite;
            } else {
                $_SESSION['panier'][$cle] = [
                    'variant_id' => $variantId,
                    'product_id' => $id,
                    'nom'        => $produit['nom'],
                    'contenance' => $variant['contenance'],
                    'prix'       => $variant['prix'],
                    'quantite'   => $quantite,
                    'image'      => $image
                ];
            }
            $msg = "success";
        }
    }
}
?>

<div class="container">

    <?php if ($msg === 'success'): ?>
        <div class="alert alert-success">
            ✅ Produit ajouté au panier !
            <a href="<?= BASE_URL ?>panier.php">Voir le panier →</a>
        </div>
    <?php elseif (str_starts_with($msg, 'error:')): ?>
        <div class="alert alert-error"><?= htmlspecialchars(substr($msg, 6)) ?></div>
    <?php endif; ?>

    <div class="product-detail">
        <img src="<?= htmlspecialchars($image ?? '') ?>"
             alt="<?= htmlspecialchars($produit['nom']) ?>"
             onerror="this.src='https://via.placeholder.com/350x350/e8f5e9/2e7d32?text=PharmaShop'">

        <div class="info">
            <p class="meta">
                🏷️ <?= htmlspecialchars($produit['marque'] ?? '') ?> &nbsp;|&nbsp;
                📂 <?= htmlspecialchars($produit['categorie'] ?? '') ?>
            </p>
            <h1><?= htmlspecialchars($produit['nom']) ?></h1>
            <p class="description">
                <?= nl2br(htmlspecialchars($produit['description'] ?? '')) ?>
            </p>

            <?php if (!empty($variantes)): ?>
                <p class="price">Dès <?= number_format(min(array_column($variantes, 'prix')), 2) ?> DT</p>

                <form method="POST">
                    <div class="form-group">
                        <label><strong>Contenance :</strong></label>
                        <select name="variant_id" required class="variant-select">
                            <?php foreach ($variantes as $v): ?>
                                <option value="<?= $v['id'] ?>">
                                    <?= htmlspecialchars($v['contenance'] ?? $v['reference']) ?> —
                                    <?= number_format($v['prix'], 2) ?> DT
                                    (Stock: <?= $v['stock'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><strong>Quantité :</strong></label>
                        <input type="number" name="quantite" value="1" min="1" max="20" class="qty-input">
                    </div>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button type="submit" name="ajouter_panier" class="btn-primary" style="font-size:16px; padding:12px 30px;">
                            🛒 Ajouter au panier
                        </button>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>login.php" class="btn-primary">
                            🔑 Connectez-vous pour acheter
                        </a>
                    <?php endif; ?>
                </form>
            <?php else: ?>
                <div class="alert alert-info">Aucune variante disponible.</div>
            <?php endif; ?>
        </div>
    </div>
    <p style="margin-top:20px;">
        <a href="<?= BASE_URL ?>produits.php" style="color:#2e7d32;">← Retour aux produits</a>
    </p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
