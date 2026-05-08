<?php
require_once __DIR__ . '/../includes/admin_check.php';
$pageTitle = "Ajouter produit - Admin";
require_once __DIR__ . '/../includes/db.php';

$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
$marques    = $pdo->query("SELECT * FROM brands ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
$erreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom         = trim($_POST['nom']);
    $description = trim($_POST['description']);
    $brand_id    = (int)$_POST['brand_id'];
    $category_id = (int)$_POST['category_id'];
    $contenance  = trim($_POST['contenance']);
    $prix        = (float)str_replace(',', '.', $_POST['prix']);
    $stock       = (int)$_POST['stock'];

    if (empty($nom) || empty($contenance) || $prix <= 0) {
        $erreur = "Nom, contenance et prix sont obligatoires.";
    } else {
        // Insérer le produit
        $pdo->prepare("INSERT INTO products (nom, description, brand_id, category_id) VALUES (?,?,?,?)")
            ->execute([$nom, $description, $brand_id, $category_id]);
        $newId = $pdo->lastInsertId();

        // Référence unique
        $ref = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '-', $nom), 0, 15)) . '-' . $newId;

        // Insérer la variante — colonne contenance (pas taille)
        $pdo->prepare("INSERT INTO product_variants (product_id, reference, prix, stock, contenance) VALUES (?,?,?,?,?)")
            ->execute([$newId, $ref, $prix, $stock, $contenance]);

        header("Location: " . BASE_URL . "admin/produits.php?msg=ajoute");
        exit();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div class="admin-content">
        <h1 style="color:#1b5e20; margin-bottom:25px;">➕ Ajouter un produit</h1>

        <?php if ($erreur): ?>
            <div class="alert alert-error"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <div class="table-container" style="max-width:700px;">
            <form method="POST">
                <h3 style="color:#2e7d32; margin-bottom:20px;">📋 Informations</h3>

                <div class="form-group">
                    <label>Nom du produit *</label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                    <div class="form-group">
                        <label>Marque</label>
                        <select name="brand_id">
                            <option value="0">-- Aucune --</option>
                            <?php foreach ($marques as $m): ?>
                                <option value="<?= $m['id'] ?>" <?= ($_POST['brand_id'] ?? 0) == $m['id'] ? 'selected' : '' ?>>
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
                                <option value="<?= $cat['id'] ?>" <?= ($_POST['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <hr style="margin:20px 0; border:none; border-top:1px solid #eee;">
                <h3 style="color:#2e7d32; margin-bottom:15px;">📏 Première variante</h3>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px;">
                    <div class="form-group">
                        <label>Contenance *</label>
                        <input type="text" name="contenance" value="<?= htmlspecialchars($_POST['contenance'] ?? '') ?>"
                               placeholder="Ex: 200ml" required>
                    </div>
                    <div class="form-group">
                        <label>Prix (DT) *</label>
                        <input type="number" name="prix" step="0.01" min="0"
                               value="<?= htmlspecialchars($_POST['prix'] ?? '') ?>" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label>Stock</label>
                        <input type="number" name="stock" min="0" value="<?= htmlspecialchars($_POST['stock'] ?? '10') ?>">
                    </div>
                </div>

                <div style="display:flex; gap:15px; margin-top:10px;">
                    <button type="submit" class="btn-primary">✅ Enregistrer</button>
                    <a href="<?= BASE_URL ?>admin/produits.php" style="padding:10px 20px; color:#888; text-decoration:none;">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
