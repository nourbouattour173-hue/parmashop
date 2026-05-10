<?php
$pageTitle = "PharmaShop - Accueil";
require_once __DIR__ . '/includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

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
        $quantite  = isset($_POST['quantite']) ? max(1, (int)$_POST['quantite']) : 1;
        
        $vStmt = $pdo->prepare("SELECT * FROM product_variants WHERE id = ? AND product_id = ?");
        $vStmt->execute([$variantId, $productId]);
        $variant = $vStmt->fetch(PDO::FETCH_ASSOC);

        if ($variant && $variant['stock'] >= $quantite) {
            if (!isset($_SESSION['panier'])) $_SESSION['panier'] = [];
            $cle = "v$variantId";
            if (isset($_SESSION['panier'][$cle])) {
                $_SESSION['panier'][$cle]['quantite'] += $quantite;
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
                    'quantite'   => $quantite,
                    'image'      => $pImage
                ];
            }
            $msg = "success_cart";
        } elseif ($variant && $variant['stock'] < $quantite) {
            $msg = "error_stock";
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

    if (isset($_POST['retirer_favoris'])) {
        $productId = (int)$_POST['product_id'];
        $userId = $_SESSION['user_id'];
        $pdo->prepare("DELETE FROM favoris WHERE user_id = ? AND product_id = ?")->execute([$userId, $productId]);
        $msg = "removed_fav";
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
    ORDER BY p.created_at DESC
    LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);

// Catégories
$categories = $pdo->query("SELECT * FROM categories ORDER BY position")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les favoris de l'utilisateur connecté (tableau d'IDs)
$favorisProduits = [];
if (isset($_SESSION['user_id'])) {
    $favStmt = $pdo->prepare("SELECT product_id FROM favoris WHERE user_id = ?");
    $favStmt->execute([$_SESSION['user_id']]);
    $favorisProduits = $favStmt->fetchAll(PDO::FETCH_COLUMN);
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="hero">
    <h1><i class="fas fa-prescription-bottle-medical"></i> Bienvenue sur PharmaShop</h1>
    <p>Votre parapharmacie en ligne — Soins, beauté et hygiène de grandes marques</p>
    <a href="<?= BASE_URL ?>produits.php" class="btn-primary">Découvrir nos produits</a>
</div>

<div class="container">
    <?php if ($msg === 'success_cart'): ?>
        <div class="alert alert-success"><i class="fas fa-cart-check"></i> Produit ajouté au panier !</div>
    <?php elseif ($msg === 'error_stock'): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> Stock insuffisant.</div>
    <?php elseif ($msg === 'success_fav'): ?>
        <div class="alert alert-success"><i class="fas fa-heart"></i> Ajouté aux favoris !</div>
    <?php elseif ($msg === 'already_fav'): ?>
        <div class="alert alert-info"><i class="fas fa-info-circle"></i> Déjà dans vos favoris.</div>
    <?php elseif ($msg === 'removed_fav'): ?>
        <div class="alert alert-info"><i class="fas fa-heart-broken"></i> Retiré des favoris.</div>
    <?php endif; ?>

    <h2 class="section-title"><i class="fas fa-th-large"></i> Nos Rayons</h2>
    <div class="category-pills">
        <?php foreach ($categories as $cat): ?>
            <a href="<?= BASE_URL ?>produits.php?categorie=<?= $cat['id'] ?>" class="pill">
                <?= htmlspecialchars($cat['nom']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <h2 class="section-title"><i class="fas fa-star"></i> Nouveaux produits</h2>
    <div class="products-grid">
        <?php foreach ($produits as $prod): ?>
            <?php $enFavori = in_array($prod['id'], $favorisProduits); ?>
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
                        <form method="POST" class="flex-1" style="display: flex; gap: 8px;">
                            <input type="hidden" name="variant_id" value="<?= $prod['variant_id'] ?>">
                            <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                            <input type="number" name="quantite" value="1" min="1" max="50" style="width: 50px; padding: 4px; border: 1px solid var(--color-border-light); border-radius: var(--radius-sm); text-align: center;">
                            <button type="submit" name="ajouter_panier" class="btn-primary btn-sm" style="flex: 1;">
                                <i class="bi bi-cart-plus"></i> Panier
                            </button>
                        </form>
                        <form method="POST">
                            <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                            <?php if ($enFavori): ?>
                                <!-- Produit déjà en favori : icône rouge, permet de retirer -->
                                <button type="submit" name="retirer_favoris"
                                        class="btn-favorite btn-favorite--active"
                                        title="Retirer des favoris">
                                    <i class="bi bi-heart-fill"></i>
                                </button>
                            <?php else: ?>
                                <!-- Produit pas encore en favori : icône outline -->
                                <button type="submit" name="ajouter_favoris"
                                        class="btn-favorite"
                                        title="Ajouter aux favoris">
                                    <i class="bi bi-heart"></i>
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="advantages-grid">
        <?php foreach ([
            ['fa-shipping-fast','Livraison rapide','Livraison sous 48h'],
            ['fa-shield-alt','Paiement sécurisé','Vos données sont protégées'],
            ['fa-medal','Grandes marques','La Roche-Posay, Vichy, Bioderma...'],
            ['fa-headset','Service client','Disponible 7j/7'],
        ] as $av): ?>
            <div class="advantage-card">
                <div class="icon"><i class="fas <?= $av[0] ?>"></i></div>
                <h3><?= $av[1] ?></h3>
                <p><?= $av[2] ?></p>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
