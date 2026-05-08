<?php
$pageTitle = "Mes Favoris - PharmaShop";
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Suppression d'un favori
if (isset($_GET['remove'])) {
    $fId = (int)$_GET['remove'];
    $pdo->prepare("DELETE FROM favoris WHERE id = ? AND user_id = ?")->execute([$fId, $userId]);
    header("Location: favoris.php");
    exit();
}

// Récupérer les favoris avec les détails produits
$sql = "
    SELECT f.id AS fav_id, p.*, b.nom AS marque,
           pv.prix AS prix_min,
           pv.id AS variant_id,
           (SELECT image_path FROM product_images WHERE product_id = p.id LIMIT 1) AS image
    FROM favoris f
    JOIN products p ON f.product_id = p.id
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN product_variants pv ON pv.id = (
        SELECT id FROM product_variants WHERE product_id = p.id ORDER BY prix ASC LIMIT 1
    )
    WHERE f.user_id = ?
    ORDER BY f.created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$favoris = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h1 class="section-title">❤️ Mes Favoris</h1>

    <?php if (empty($favoris)): ?>
        <div class="alert alert-info">Vous n'avez pas encore de favoris. <a href="produits.php">Découvrir les produits</a></div>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($favoris as $prod): ?>
                <div class="product-card">
                    <a href="<?= BASE_URL ?>detail_produit.php?id=<?= $prod['id'] ?>" class="card-image-link">
                        <img src="<?= htmlspecialchars($prod['image'] ?? '') ?>"
                             alt="<?= htmlspecialchars($prod['nom']) ?>"
                             onerror="this.src='https://via.placeholder.com/300x200/e8f5e9/2e7d32?text=PharmaShop'">
                    </a>
                    <div class="card-body">
                        <p class="brand"><?= htmlspecialchars($prod['marque'] ?? '') ?></p>
                        <a href="<?= BASE_URL ?>detail_produit.php?id=<?= $prod['id'] ?>">
                            <h3><?= htmlspecialchars($prod['nom']) ?></h3>
                        </a>
                        <p class="price">
                            <?= $prod['prix_min'] ? number_format($prod['prix_min'], 2) . ' DT' : 'N/D' ?>
                        </p>
                        
                        <div class="card-actions">
                            <form method="POST" action="produits.php" class="flex-1">
                                <input type="hidden" name="variant_id" value="<?= $prod['variant_id'] ?>">
                                <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                <button type="submit" name="ajouter_panier" class="btn-primary btn-sm w-100">
                                    🛒 Panier
                                </button>
                            </form>
                            <a href="favoris.php?remove=<?= $prod['fav_id'] ?>" class="btn-danger btn-icon" title="Retirer des favoris">
                                🗑️
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
