<?php
$pageTitle = "PharmaShop - Accueil";
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';

// Traitement POST (Ajout Panier / Favoris)
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "login.php");
        exit();
    }

    if (isset($_POST['ajouter_panier'])) {
        $variantId = (int)$_POST['variant_id'];
        $productId = (int)$_POST['product_id'];
        
        $vStmt = $pdo->prepare("SELECT * FROM product_variants WHERE id = ? AND product_id = ?");
        $vStmt->execute([$variantId, $productId]);
        $variant = $vStmt->fetch(PDO::FETCH_ASSOC);

        if ($variant && $variant['stock'] > 0) {
            if (!isset($_SESSION['panier'])) $_SESSION['panier'] = [];
            $cle = "v$variantId";
            if (isset($_SESSION['panier'][$cle])) {
                $_SESSION['panier'][$cle]['quantite'] += 1;
            } else {
                $pStmt = $pdo->prepare("SELECT nom FROM products WHERE id = ?");
                $pStmt->execute([$productId]);
                $pNom = $pStmt->fetchColumn();
                
                $iStmt = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ? LIMIT 1");
                $iStmt->execute([$productId]);
                $pImage = $iStmt->fetchColumn();

                $_SESSION['panier'][$cle] = [
                    'variant_id' => $variantId,
                    'product_id' => $productId,
                    'nom'        => $pNom,
                    'contenance' => $variant['contenance'],
                    'prix'       => $variant['prix'],
                    'quantite'   => 1,
                    'image'      => $pImage
                ];
            }
            $msg = "success_cart";
        }
    }

    if (isset($_POST['ajouter_favoris'])) {
        $productId = (int)$_POST['product_id'];
        $userId = $_SESSION['user_id'];
        $chk = $pdo->prepare("SELECT id FROM favoris WHERE user_id = ? AND product_id = ?");
        $chk->execute([$userId, $productId]);
        if (!$chk->fetch()) {
            $fStmt = $pdo->prepare("INSERT INTO favoris (user_id, product_id) VALUES (?, ?)");
            $fStmt->execute([$userId, $productId]);
            $msg = "success_fav";
        } else {
            $msg = "already_fav";
        }
    }
}

// 8 derniers produits
$produits = $pdo->query("
    SELECT p.id, p.nom, b.nom AS marque,
           pv.prix AS prix_min,
           pv.id AS variant_id,
           (SELECT image_path FROM product_images WHERE product_id = p.id LIMIT 1) AS image
    FROM products p
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN product_variants pv ON pv.id = (
        SELECT id FROM product_variants WHERE product_id = p.id ORDER BY prix ASC LIMIT 1
    )
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
    <a href="<?= BASE_URL ?>produits.php" class="btn-primary">Découvrir nos produits</a>
</div>

<div class="container">
    <?php if ($msg === 'success_cart'): ?>
        <div class="alert alert-success">🛒 Produit ajouté au panier !</div>
    <?php elseif ($msg === 'success_fav'): ?>
        <div class="alert alert-success">❤️ Ajouté aux favoris !</div>
    <?php elseif ($msg === 'already_fav'): ?>
        <div class="alert alert-info">ℹ️ Déjà dans vos favoris.</div>
    <?php endif; ?>

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
                        <?= $prod['prix_min'] ? 'Dès ' . number_format($prod['prix_min'], 2) . ' DT' : 'N/D' ?>
                    </p>
                    
                    <div class="card-actions">
                        <form method="POST" class="flex-1">
                            <input type="hidden" name="variant_id" value="<?= $prod['variant_id'] ?>">
                            <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                            <button type="submit" name="ajouter_panier" class="btn-primary btn-sm w-100">
                                🛒 Panier
                            </button>
                        </form>
                        <form method="POST">
                            <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                            <button type="submit" name="ajouter_favoris" class="btn-favorite" title="Ajouter aux favoris">
                                ❤️
                            </button>
                        </form>
                    </div>
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
