<?php
require_once __DIR__ . '/../includes/admin_check.php';
$pageTitle = "Modifier produit - Admin";
require_once __DIR__ . '/../includes/db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id === 0) { header("Location: " . BASE_URL . "admin/produits.php"); exit(); }

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$produit = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$produit) { header("Location: " . BASE_URL . "admin/produits.php"); exit(); }

$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
$marques    = $pdo->query("SELECT * FROM brands ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

// Charger variantes
$vStmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY prix");
$vStmt->execute([$id]);
$variantes = $vStmt->fetchAll(PDO::FETCH_ASSOC);

// Charger images
$iStmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_main DESC, id ASC");
$iStmt->execute([$id]);
$images = $iStmt->fetchAll(PDO::FETCH_ASSOC);

$erreur = $succes = "";

// Supprimer une variante
if (isset($_GET['del_v'])) {
    $pdo->prepare("DELETE FROM product_variants WHERE id = ? AND product_id = ?")->execute([(int)$_GET['del_v'], $id]);
    header("Location: " . BASE_URL . "admin/modifier_produit.php?id=$id&msg=vsupp");
    exit();
}

// Supprimer une image
if (isset($_GET['del_img'])) {
    $imgId = (int)$_GET['del_img'];
    $img = $pdo->prepare("SELECT image_path FROM product_images WHERE id = ? AND product_id = ?");
    $img->execute([$imgId, $id]);
    $imgData = $img->fetch(PDO::FETCH_ASSOC);
    if ($imgData) {
        $filePath = __DIR__ . '/../' . ltrim($imgData['image_path'], '/');
        if (file_exists($filePath) && is_file($filePath)) {
            unlink($filePath);
        }
        $pdo->prepare("DELETE FROM product_images WHERE id = ?")->execute([$imgId]);
    }
    header("Location: " . BASE_URL . "admin/modifier_produit.php?id=$id&msg=imgsupp");
    exit();
}

// Définir comme image principale
if (isset($_GET['main_img'])) {
    $imgId = (int)$_GET['main_img'];
    $pdo->prepare("UPDATE product_images SET is_main = 0 WHERE product_id = ?")->execute([$id]);
    $pdo->prepare("UPDATE product_images SET is_main = 1 WHERE id = ? AND product_id = ?")->execute([$imgId, $id]);
    header("Location: " . BASE_URL . "admin/modifier_produit.php?id=$id&msg=imgmain");
    exit();
}

// Mettre à jour les variantes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['maj_variantes'])) {
    if (!empty($_POST['variantes']) && is_array($_POST['variantes'])) {
        foreach ($_POST['variantes'] as $vId => $vData) {
            $prix = (float)$vData['prix'];
            $stock = (int)$vData['stock'];
            $pdo->prepare("UPDATE product_variants SET prix = ?, stock = ? WHERE id = ? AND product_id = ?")->execute([$prix, $stock, $vId, $id]);
        }
        $succes = "Variantes mises à jour avec succès.";
        // Rafraîchir les données
        $produit = $pdo->query("SELECT * FROM products WHERE id = $id")->fetch();
        $vStmt->execute([$id]);
        $variantes = $vStmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Modifier le produit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['maj_produit'])) {
    $nom         = trim($_POST['nom']);
    $description = trim($_POST['description']);
    $brand_id    = (int)$_POST['brand_id'];
    $category_id = (int)$_POST['category_id'];

    if (empty($nom)) { $erreur = "Le nom est obligatoire."; }
    else {
        $pdo->prepare("UPDATE products SET nom=?, description=?, brand_id=?, category_id=? WHERE id=?")
            ->execute([$nom, $description, $brand_id, $category_id, $id]);
        $succes = "Produit modifié.";
        $stmt->execute([$id]);
        $produit = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Ajouter une variante
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_variant'])) {
    $contenance = trim($_POST['contenance_v']);
    $prix       = (float)str_replace(',', '.', $_POST['prix_v']);
    $stock      = (int)$_POST['stock_v'];
    $ref        = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '-', $produit['nom']), 0, 10)) . '-' . $id . '-' . time();

    if (!empty($contenance) && $prix > 0) {
        $pdo->prepare("INSERT INTO product_variants (product_id, reference, prix, stock, contenance) VALUES (?,?,?,?,?)")
            ->execute([$id, $ref, $prix, $stock, $contenance]);
        $succes = "Variante ajoutée.";
    } else {
        $erreur = "Contenance et prix obligatoires.";
    }
    $vStmt->execute([$id]);
    $variantes = $vStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Ajouter une image
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_image'])) {
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $filename = $_FILES['image_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $newName = 'product_' . $id . '_' . time() . '.' . $ext;
            $destPath = 'assets/images/produits/' . $newName;
            $destAbsolute = __DIR__ . '/../' . $destPath;
            
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $destAbsolute)) {
                $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_main) VALUES (?, ?, 0)")
                    ->execute([$id, $destPath]);
                $succes = "Image ajoutée.";
                $iStmt->execute([$id]);
                $images = $iStmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $erreur = "Erreur lors de l'enregistrement de l'image.";
            }
        } else {
            $erreur = "Format d'image non autorisé (jpg, png, webp, gif).";
        }
    } else {
        $erreur = "Veuillez sélectionner une image valide.";
    }
}

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div class="admin-content">
        <h1 style="color:#1b5e20; margin-bottom:20px;"><i class="bi bi-pencil-square"></i> Modifier produit #<?= $id ?></h1>

        <?php if ($succes || in_array(($_GET['msg'] ?? ''), ['vsupp', 'imgsupp', 'imgmain'])): ?>
            <?php 
                $alertMsg = $succes ?: '';
                if (!$alertMsg) {
                    if (($_GET['msg'] ?? '') === 'imgsupp') $alertMsg = 'Image supprimée.';
                    elseif (($_GET['msg'] ?? '') === 'imgmain') $alertMsg = 'Image principale mise à jour.';
                    else $alertMsg = 'Variante supprimée.';
                }
            ?>
            <div class="alert alert-success"><?= $alertMsg ?></div>
        <?php endif; ?>
        <?php if ($erreur): ?><div class="alert alert-error"><?= htmlspecialchars($erreur) ?></div><?php endif; ?>

        <!-- Modifier produit -->
        <div class="table-container" style="margin-bottom:25px; max-width:700px;">
            <h3 style="color:#2e7d32; margin-bottom:20px;">📋 Informations</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Nom *</label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($produit['nom']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4"><?= htmlspecialchars($produit['description'] ?? '') ?></textarea>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                    <div class="form-group">
                        <label>Marque</label>
                        <select name="brand_id">
                            <option value="0">-- Aucune --</option>
                            <?php foreach ($marques as $m): ?>
                                <option value="<?= $m['id'] ?>" <?= $produit['brand_id'] == $m['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Catégorie</label>
                        <select name="category_id">
                            <option value="0">-- Aucune --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $produit['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" name="maj_produit" class="btn-primary">💾 Enregistrer</button>
            </form>
        </div>

        <!-- Images -->
        <div class="table-container" style="margin-bottom:25px;">
            <h3 style="color:#2e7d32; margin-bottom:15px;">🖼️ Images du produit</h3>
            <?php if (empty($images)): ?>
                <p style="color:#888;">Aucune image pour ce produit.</p>
            <?php else: ?>
                <div style="display:flex; flex-wrap:wrap; gap:15px; margin-bottom:20px;">
                    <?php foreach ($images as $img): ?>
                        <div style="position:relative; border:<?= $img['is_main'] ? '2px solid #2e7d32' : '1px solid var(--color-border-light, #eee)' ?>; padding:10px; border-radius:var(--radius-md, 8px); text-align:center; width:160px; background:#fff;">
                            <?php if ($img['is_main']): ?>
                                <span style="position:absolute; top:-10px; left:10px; background:#2e7d32; color:#fff; font-size:10px; padding:2px 8px; border-radius:10px; font-weight:bold;">Principale</span>
                            <?php endif; ?>
                            
                            <img src="<?= BASE_URL . ltrim(htmlspecialchars($img['image_path']), '/') ?>" 
                                 onerror="this.src='https://via.placeholder.com/150/e8f5e9/2e7d32?text=Introuvable'"
                                 alt="Image produit" 
                                 style="max-width:100%; height:100px; object-fit:contain; margin-bottom:10px;">
                            <br>
                            
                            <div style="display:flex; flex-direction:column; gap:5px;">
                                <?php if (!$img['is_main']): ?>
                                    <a href="<?= BASE_URL ?>admin/modifier_produit.php?id=<?= $id ?>&main_img=<?= $img['id'] ?>"
                                       class="btn-primary" style="font-size:11px; padding:4px 8px; text-align:center;"><i class="bi bi-star-fill"></i> Mettre principale</a>
                                <?php endif; ?>
                                <a href="<?= BASE_URL ?>admin/modifier_produit.php?id=<?= $id ?>&del_img=<?= $img['id'] ?>"
                                   class="btn-danger" style="font-size:11px; padding:4px 8px; text-align:center;"
                                   onclick="return confirm('Supprimer cette image ?')"><i class="bi bi-trash"></i> Supprimer</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <hr style="margin:20px 0; border:none; border-top:1px solid #eee;">
            <h4 style="margin-bottom:15px;">➕ Ajouter une image</h4>
            <form method="POST" enctype="multipart/form-data" style="display:flex; gap:15px; flex-wrap:wrap; align-items:flex-end;">
                <div class="form-group" style="margin:0;">
                    <label>Sélectionner une image</label>
                    <input type="file" name="image_file" accept="image/*" required style="padding: 5px; background: #fff; border: 1px solid var(--color-border-light, #eee); border-radius: var(--radius-sm, 4px);">
                </div>
                <button type="submit" name="add_image" class="btn-primary">Ajouter l'image</button>
            </form>
        </div>

        <!-- Variantes -->
        <div class="table-container">
            <h3 style="color:#2e7d32; margin-bottom:15px;">📏 Variantes</h3>
            <?php if (empty($variantes)): ?>
                <p style="color:#888;">Aucune variante.</p>
            <?php else: ?>
                <form method="POST">
                    <table>
                        <thead><tr><th>Contenance</th><th>Prix (DT)</th><th>Stock</th><th>Référence</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php foreach ($variantes as $v): ?>
                                <tr>
                                    <td><?= htmlspecialchars($v['contenance'] ?? '-') ?></td>
                                    <td><input type="number" step="0.01" name="variantes[<?= $v['id'] ?>][prix]" value="<?= $v['prix'] ?>" style="width:80px; padding:5px; border:1px solid #ccc; border-radius:4px;"></td>
                                    <td><input type="number" name="variantes[<?= $v['id'] ?>][stock]" value="<?= $v['stock'] ?>" style="width:80px; padding:5px; border:1px solid #ccc; border-radius:4px;"></td>
                                    <td style="font-size:12px; color:#aaa;"><?= htmlspecialchars($v['reference']) ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>admin/modifier_produit.php?id=<?= $id ?>&del_v=<?= $v['id'] ?>"
                                           class="btn-danger" style="font-size:12px; padding:5px 10px;"
                                           onclick="return confirm('Supprimer cette variante ?')"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div style="margin-top:15px; text-align:right;">
                        <button type="submit" name="maj_variantes" class="btn-primary" style="padding:8px 15px; font-size:13px;"><i class="bi bi-save"></i> Enregistrer les variantes</button>
                    </div>
                </form>
            <?php endif; ?>

            <hr style="margin:20px 0; border:none; border-top:1px solid #eee;">
            <h4 style="margin-bottom:15px;">➕ Ajouter une variante</h4>
            <form method="POST" style="display:flex; gap:15px; flex-wrap:wrap; align-items:flex-end;">
                <div class="form-group" style="margin:0;">
                    <label>Contenance *</label>
                    <input type="text" name="contenance_v" placeholder="Ex: 100ml" style="width:120px;">
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Prix (DT) *</label>
                    <input type="number" name="prix_v" step="0.01" min="0" placeholder="0.00" style="width:100px;">
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Stock</label>
                    <input type="number" name="stock_v" min="0" value="10" style="width:80px;">
                </div>
                <button type="submit" name="add_variant" class="btn-primary">Ajouter</button>
            </form>
        </div>

        <div style="margin-top:20px;">
            <a href="<?= BASE_URL ?>admin/produits.php" style="color:#888;">← Retour à la liste</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
