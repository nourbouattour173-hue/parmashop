<?php
$pageTitle = "PharmaShop - Accueil";
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';

// 8 derniers produits
$produits = $pdo->query("
    SELECT p.id, p.nom, b.nom AS marque,
           MIN(pv.prix) AS prix_min,
           (SELECT image_path FROM product_images WHERE product_id = p.id LIMIT 1) AS image
    FROM products p
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN product_variants pv ON pv.product_id = p.id
    GROUP BY p.id
    ORDER BY p.created_at DESC
    LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);

// Catégories
$categories = $pdo->query("SELECT * FROM categories ORDER BY position")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="hero">
    <h1>💊 Bienvenue sur PharmaShop</h1>
    <p>Votre parapharmacie en ligne — Soins, beauté et hygiène de grandes marques</p>
    <a href="<?= BASE_URL ?>produits.php" class="btn">Découvrir nos produits</a>
</div>

<div class="container">

    <h2 class="section-title">🗂️ Nos Rayons</h2>
    <div class="category-pills">
        <?php foreach ($categories as $cat): ?>
            <a href="<?= BASE_URL ?>produits.php?categorie=<?= $cat['id'] ?>" class="pill">
                <?= htmlspecialchars($cat['nom']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <h2 class="section-title">⭐ Nouveaux produits</h2>
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
                    <a href="<?= BASE_URL ?>detail_produit.php?id=<?= $prod['id'] ?>" class="btn-details">
                        Voir le produit
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="advantages-grid">
        <?php foreach ([
            ['🚚','Livraison rapide','Livraison sous 48h'],
            ['🔒','Paiement sécurisé','Vos données sont protégées'],
            ['🏆','Grandes marques','La Roche-Posay, Vichy, Bioderma...'],
            ['💬','Service client','Disponible 7j/7'],
        ] as $av): ?>
            <div class="advantage-card">
                <div class="icon"><?= $av[0] ?></div>
                <h3><?= $av[1] ?></h3>
                <p><?= $av[2] ?></p>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
