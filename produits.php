<?php
$pageTitle = "Nos Produits - PharmaShop";
require_once __DIR__ . '/includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$categorieId = $_GET['categorie'] ?? '';
$subcatId    = $_GET['sous_categorie'] ?? '';
$brandId     = $_GET['marque']    ?? '';
$skinTypeId  = $_GET['skin_type'] ?? '';
$minPrice    = $_GET['min_price'] ?? '';
$maxPrice    = $_GET['max_price'] ?? '';
$recherche   = trim($_GET['q']    ?? '');

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
                $pStmt = $pdo->prepare("SELECT nom FROM products WHERE id = ?"); $pStmt->execute([$productId]); $pNom = $pStmt->fetchColumn();
                $iStmt = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ? LIMIT 1"); $iStmt->execute([$productId]); $pImage = $iStmt->fetchColumn();
                $_SESSION['panier'][$cle] = ['variant_id'=>$variantId,'product_id'=>$productId,'nom'=>$pNom,'contenance'=>$variant['contenance'],'prix'=>$variant['prix'],'quantite'=>$quantite,'image'=>$pImage];
            }
            $msg = "success_cart";
        } elseif ($variant && $variant['stock'] < $quantite) {
            $msg = "error_stock";
        }
    }
    if (isset($_POST['ajouter_favoris'])) {
        $productId = (int)$_POST['product_id']; $userId = $_SESSION['user_id'];
        $chk = $pdo->prepare("SELECT id FROM favoris WHERE user_id = ? AND product_id = ?"); $chk->execute([$userId, $productId]);
        if (!$chk->fetch()) {
            $pdo->prepare("INSERT INTO favoris (user_id, product_id) VALUES (?, ?)")->execute([$userId, $productId]);
            $msg = "success_fav";
        } else { $msg = "already_fav"; }
    }
    if (isset($_POST['retirer_favoris'])) {
        $productId = (int)$_POST['product_id']; $userId = $_SESSION['user_id'];
        $pdo->prepare("DELETE FROM favoris WHERE user_id = ? AND product_id = ?")->execute([$userId, $productId]);
        $msg = "removed_fav";
    }
}

// Construction requête dynamique
$sql = "
    SELECT p.id, p.nom, b.nom AS marque,
           pv.prix AS prix_min,
           pv.id AS variant_id,
           c.nom AS categorie,
           (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_main DESC, id ASC LIMIT 1) AS image
    FROM products p
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN product_variants pv ON pv.id = (
        SELECT id FROM product_variants WHERE product_id = p.id ORDER BY prix ASC LIMIT 1
    )
";

if (!empty($skinTypeId)) { $sql .= " INNER JOIN product_skin_types pst ON p.id = pst.product_id "; }

$sql .= " WHERE 1=1 ";

$params = [];
if (!empty($categorieId)) { $sql .= " AND p.category_id = ?"; $params[] = $categorieId; }
if (!empty($subcatId))    { $sql .= " AND p.subcategory_id = ?"; $params[] = $subcatId; }
if (!empty($brandId))     { $sql .= " AND p.brand_id = ?";    $params[] = $brandId; }
if (!empty($recherche))   { $sql .= " AND p.nom LIKE ?";       $params[] = "%$recherche%"; }
if (!empty($skinTypeId))  { $sql .= " AND pst.skin_type_id = ?"; $params[] = $skinTypeId; }
if (!empty($minPrice))    { $sql .= " AND pv.prix >= ?";      $params[] = $minPrice; }
if (!empty($maxPrice))    { $sql .= " AND pv.prix <= ?";      $params[] = $maxPrice; }

$sql .= " ORDER BY p.nom";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les favoris de l'utilisateur (tableau d'IDs)
$favorisProduits = [];
if (isset($_SESSION['user_id'])) {
    $favStmt = $pdo->prepare("SELECT product_id FROM favoris WHERE user_id = ?");
    $favStmt->execute([$_SESSION['user_id']]);
    $favorisProduits = $favStmt->fetchAll(PDO::FETCH_COLUMN);
}

// Cookie 3 : Préférence d'affichage grille / liste (30 jours)
if (isset($_GET['vue']) && in_array($_GET['vue'], ['grille', 'liste'])) {
    setcookie('vue_produits', $_GET['vue'], time() + 60 * 60 * 24 * 30, '/', '', false, true);
    $vueProduits = $_GET['vue'];
} else {
    $vueProduits = $_COOKIE['vue_produits'] ?? 'grille';
}

// Pour le navigateur
$categories = $pdo->query("SELECT * FROM categories ORDER BY position")->fetchAll(PDO::FETCH_ASSOC);
$subcategories = [];
if (!empty($categorieId)) {
    $sStmt = $pdo->prepare("SELECT * FROM subcategories WHERE category_id = ? ORDER BY position");
    $sStmt->execute([$categorieId]);
    $subcategories = $sStmt->fetchAll(PDO::FETCH_ASSOC);
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="products-layout">
        <aside class="sidebar">
            <?php include __DIR__ . '/includes/filtre.php'; ?>
        </aside>

        <main class="products-main">
            <h1 class="section-title"><i class="bi bi-shop"></i> Nos Produits
                <span style="font-size:15px; color:#666; font-weight:normal;">(<?= count($produits) ?> produits)</span>
            </h1>

            <?php if ($msg === 'success_cart'): ?>
                <div class="alert alert-success"><i class="bi bi-cart-check"></i> Produit ajouté au panier !</div>
            <?php elseif ($msg === 'success_fav'): ?>
                <div class="alert alert-success"><i class="bi bi-heart-fill"></i> Ajouté aux favoris !</div>
            <?php elseif ($msg === 'error_stock'): ?>
                <div class="alert alert-error"><i class="bi bi-exclamation-triangle"></i> Stock insuffisant.</div>
            <?php elseif ($msg === 'already_fav'): ?>
                <div class="alert alert-info"><i class="bi bi-info-circle"></i> Déjà dans vos favoris.</div>
            <?php elseif ($msg === 'removed_fav'): ?>
                <div class="alert alert-info"><i class="bi bi-heart"></i> Retiré des favoris.</div>
            <?php endif; ?>

    <?php if (empty($produits)): ?>
        <div class="alert alert-info">Aucun produit trouvé.</div>
    <?php else: ?>
        <!-- Boutons bascule vue grille / liste -->
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-bottom:12px;">
            <a href="?<?= http_build_query(array_merge($_GET, ['vue'=>'grille'])) ?>"
               class="btn-vue <?= $vueProduits==='grille' ? 'btn-vue-active' : '' ?>"
               title="Vue grille"><i class="bi bi-grid-3x3-gap"></i> Grille</a>
            <a href="?<?= http_build_query(array_merge($_GET, ['vue'=>'liste'])) ?>"
               class="btn-vue <?= $vueProduits==='liste' ? 'btn-vue-active' : '' ?>"
               title="Vue liste"><i class="bi bi-list-ul"></i> Liste</a>
        </div>
        <div class="<?= $vueProduits === 'liste' ? 'products-list' : 'products-grid' ?>">
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
                            <form method="POST" style="flex:1; display: flex; gap: 8px;">
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
                                    <button type="submit" name="retirer_favoris"
                                            class="btn-favorite btn-favorite--active"
                                            title="Retirer des favoris">
                                        <i class="bi bi-heart-fill"></i>
                                    </button>
                                <?php else: ?>
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
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
