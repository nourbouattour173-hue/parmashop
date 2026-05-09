<?php
$pageTitle = "Produit - PharmaShop";
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';

$id = (int)($_GET['id'] ?? 0);
if ($id === 0) { header("Location: " . BASE_URL . "produits.php"); exit(); }

// Récupérer le produit
$stmt = $pdo->prepare("
    SELECT p.*, b.nom AS marque, c.nom AS categorie, sc.nom AS sous_categorie
    FROM products p
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN subcategories sc ON p.subcategory_id = sc.id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$produit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produit) {
    echo "<div class='container'><div class='alert alert-error'>Produit introuvable.</div></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit();
}

// Récupérer types de peau
$stStmt = $pdo->prepare("
    SELECT st.nom 
    FROM skin_types st
    JOIN product_skin_types pst ON st.id = pst.skin_type_id
    WHERE pst.product_id = ?
");
$stStmt->execute([$id]);
$skin_types = $stStmt->fetchAll(PDO::FETCH_COLUMN);

// Récupérer toutes les images
$iStmt = $pdo->prepare("SELECT image_path, is_main FROM product_images WHERE product_id = ? ORDER BY is_main DESC");
$iStmt->execute([$id]);
$images = $iStmt->fetchAll(PDO::FETCH_ASSOC);
$imagePrincipale = $images[0]['image_path'] ?? '';

// Variantes
$vStmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY prix");
$vStmt->execute([$id]);
$variantes = $vStmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si le produit est en favori pour l'utilisateur connecté
$enFavori = false;
if (isset($_SESSION['user_id'])) {
    $favChk = $pdo->prepare("SELECT id FROM favoris WHERE user_id = ? AND product_id = ?");
    $favChk->execute([$_SESSION['user_id'], $id]);
    $enFavori = (bool)$favChk->fetch();
}

// Ajout au panier / gestion favoris
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "login.php");
        exit();
    }

    if (isset($_POST['ajouter_panier'])) {
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
                        'image'      => $imagePrincipale
                    ];
                }
                $msg = "success";
            }
        }
    }

    if (isset($_POST['ajouter_favoris'])) {
        $favChk2 = $pdo->prepare("SELECT id FROM favoris WHERE user_id = ? AND product_id = ?");
        $favChk2->execute([$_SESSION['user_id'], $id]);
        if (!$favChk2->fetch()) {
            $pdo->prepare("INSERT INTO favoris (user_id, product_id) VALUES (?, ?)")->execute([$_SESSION['user_id'], $id]);
        }
        $enFavori = true;
        $msg = "fav_added";
    }

    if (isset($_POST['retirer_favoris'])) {
        $pdo->prepare("DELETE FROM favoris WHERE user_id = ? AND product_id = ?")->execute([$_SESSION['user_id'], $id]);
        $enFavori = false;
        $msg = "fav_removed";
    }
}
?>

<div class="container">

    <?php if ($msg === 'success'): ?>
        <div class="alert alert-success">
            <i class="bi bi-cart-check"></i> Produit ajouté au panier !
            <a href="<?= BASE_URL ?>panier.php">Voir le panier</a>
        </div>
    <?php elseif (str_starts_with($msg, 'error:')): ?>
        <div class="alert alert-error"><i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars(substr($msg, 6)) ?></div>
    <?php elseif ($msg === 'fav_added'): ?>
        <div class="alert alert-success"><i class="bi bi-heart-fill"></i> Ajouté aux favoris !</div>
    <?php elseif ($msg === 'fav_removed'): ?>
        <div class="alert alert-info"><i class="bi bi-heart"></i> Retiré des favoris.</div>
    <?php endif; ?>

    <div class="product-detail">
        <div class="product-gallery">
            <div class="main-image-container">
                <img src="<?= htmlspecialchars($imagePrincipale) ?>" 
                     id="main-img" 
                     alt="<?= htmlspecialchars($produit['nom']) ?>">
            </div>
            
            <?php if (count($images) > 1): ?>
                <div class="thumbnails">
                    <?php foreach ($images as $img): ?>
                        <img src="<?= htmlspecialchars($img['image_path']) ?>" 
                             onclick="document.getElementById('main-img').src=this.src; document.querySelectorAll('.thumbnails img').forEach(i=>i.classList.remove('active')); this.classList.add('active');"
                             alt="Thumbnail"
                             class="<?= $img['image_path'] == $imagePrincipale ? 'active' : '' ?>">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="info">
            <p class="meta">
                <i class="bi bi-tag"></i> <?= htmlspecialchars($produit['marque'] ?? '') ?> &nbsp;|&nbsp;
                <i class="bi bi-folder"></i> <?= htmlspecialchars($produit['categorie'] ?? '') ?>
                <?php if (!empty($produit['sous_categorie'])): ?>
                    <span class="text-primary">&rsaquo; <?= htmlspecialchars($produit['sous_categorie']) ?></span>
                <?php endif; ?>
            </p>

            <?php if (!empty($skin_types)): ?>
                <div class="skin-tags">
                    <?php foreach ($skin_types as $st): ?>
                        <span class="skin-pill"><?= htmlspecialchars($st) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <h1><?= htmlspecialchars($produit['nom']) ?></h1>
            <p class="description">
                <?= nl2br(htmlspecialchars($produit['description'] ?? '')) ?>
            </p>

            <?php if (!empty($variantes)): ?>
                <form method="POST">
                    <div class="form-group">
                        <label><strong>Contenance :</strong></label>
                        <select name="variant_id" required class="variant-select">
                            <?php foreach ($variantes as $v): ?>
                                <option value="<?= $v['id'] ?>">
                                    <?= htmlspecialchars($v['contenance'] ?? $v['reference']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><strong>Quantité :</strong></label>
                        <input type="number" name="quantite" value="1" min="1" max="20" class="qty-input">
                    </div>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="detail-actions">
                            <button type="submit" name="ajouter_panier" class="btn-primary" style="flex:1; padding:15px;">
                                <i class="bi bi-cart-plus"></i> Ajouter au panier
                            </button>
                        </div>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>login.php" class="btn-primary w-100">
                            <i class="bi bi-person-lock"></i> Connectez-vous pour acheter
                        </a>
                    <?php endif; ?>
                </form>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Bouton favori séparé du formulaire panier -->
                    <form method="POST" style="margin-top: 12px;">
                        <?php if ($enFavori): ?>
                            <button type="submit" name="retirer_favoris" class="btn-favorite btn-favorite--active btn-favorite--large" title="Retirer des favoris">
                                <i class="bi bi-heart-fill"></i> Dans vos favoris
                            </button>
                        <?php else: ?>
                            <button type="submit" name="ajouter_favoris" class="btn-favorite btn-favorite--large" title="Ajouter aux favoris">
                                <i class="bi bi-heart"></i> Ajouter aux favoris
                            </button>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info"><i class="bi bi-info-circle"></i> Aucune variante disponible.</div>
            <?php endif; ?>
        </div>
    </div>
    <p class="mt-lg">
        <a href="<?= BASE_URL ?>produits.php" class="text-primary"><i class="bi bi-arrow-left"></i> Retour aux produits</a>
    </p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
