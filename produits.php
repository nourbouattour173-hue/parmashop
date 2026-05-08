<?php
$pageTitle = "Nos Produits - PharmaShop";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/db.php';

$categorieId = $_GET['categorie'] ?? '';
$brandId     = $_GET['marque']    ?? '';
$recherche   = trim($_GET['q']    ?? '');

// Construction requête dynamique
$sql = "
    SELECT p.id, p.nom, b.nom AS marque,
           MIN(pv.prix) AS prix_min,
           c.nom AS categorie,
           (SELECT image_path FROM product_images WHERE product_id = p.id LIMIT 1) AS image
    FROM products p
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN product_variants pv ON pv.product_id = p.id
    WHERE 1=1
";
$params = [];
if (!empty($categorieId)) { $sql .= " AND p.category_id = ?"; $params[] = $categorieId; }
if (!empty($brandId))     { $sql .= " AND p.brand_id = ?";    $params[] = $brandId; }
if (!empty($recherche))   { $sql .= " AND p.nom LIKE ?";       $params[] = "%$recherche%"; }
$sql .= " GROUP BY p.id ORDER BY p.nom";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
$marques    = $pdo->query("SELECT * FROM brands ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h1 class="section-title">🛍️ Nos Produits
        <span style="font-size:15px; color:#666; font-weight:normal;">(<?= count($produits) ?> produits)</span>
    </h1>

    <form method="GET" class="filters">
        <input type="text" name="q" value="<?= htmlspecialchars($recherche) ?>"
               placeholder="🔍 Rechercher..." style="flex:1; min-width:180px; padding:8px 12px; border:1px solid #ccc; border-radius:6px;">
        <select name="categorie">
            <option value="">Toutes catégories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $categorieId == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="marque">
            <option value="">Toutes marques</option>
            <?php foreach ($marques as $m): ?>
                <option value="<?= $m['id'] ?>" <?= $brandId == $m['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-primary">Filtrer</button>
        <a href="http://localhost/parapharmacie/produits.php" style="color:#888; text-decoration:none; align-self:center;">✕ Reset</a>
    </form>

    <?php if (empty($produits)): ?>
        <div class="alert alert-info">Aucun produit trouvé.</div>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($produits as $prod): ?>
                <div class="product-card">
                    <img src="<?= htmlspecialchars($prod['image'] ?? '') ?>"
                         alt="<?= htmlspecialchars($prod['nom']) ?>"
                         onerror="this.src='https://via.placeholder.com/300x200/e8f5e9/2e7d32?text=PharmaShop'">
                    <div class="card-body">
                        <p class="brand"><?= htmlspecialchars($prod['marque'] ?? '') ?></p>
                        <h3><?= htmlspecialchars($prod['nom']) ?></h3>
                        <p class="price">
                            <?= $prod['prix_min'] ? 'Dès ' . number_format($prod['prix_min'], 2) . ' DT' : 'N/D' ?>
                        </p>
                        <a href="http://localhost/parapharmacie/detail_produit.php?id=<?= $prod['id'] ?>" class="btn-details">
                            Voir le produit
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
