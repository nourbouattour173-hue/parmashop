<?php

$marques    = $pdo->query("SELECT * FROM brands ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
$skin_types = $pdo->query("SELECT * FROM skin_types ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);


$brandId    = $_GET['marque']    ?? '';
$skinTypeId = $_GET['skin_type'] ?? '';
$recherche  = $_GET['q']         ?? '';
$minPrice   = $_GET['min_price'] ?? '';
$maxPrice   = $_GET['max_price'] ?? '';
$categorieId = $_GET['categorie'] ?? '';
$subcatId    = $_GET['sous_categorie'] ?? '';
?>

<form method="GET" class="filters-sidebar">
    
    <input type="hidden" name="categorie" value="<?= htmlspecialchars($categorieId) ?>">
    <input type="hidden" name="sous_categorie" value="<?= htmlspecialchars($subcatId) ?>">

    <div class="filter-section">
        <h3>Recherche</h3>
        <input type="text" name="q" value="<?= htmlspecialchars($recherche) ?>" placeholder="Nom du produit...">
    </div>

    <div class="filter-section">
        <h3>Marques</h3>
        <select name="marque">
            <option value="">Toutes marques</option>
            <?php foreach ($marques as $m): ?>
                <option value="<?= $m['id'] ?>" <?= $brandId == $m['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="filter-section">
        <h3>Type de peau</h3>
        <select name="skin_type">
            <option value="">Tous types</option>
            <?php foreach ($skin_types as $st): ?>
                <option value="<?= $st['id'] ?>" <?= $skinTypeId == $st['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($st['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="filter-section">
        <h3>Prix (DT)</h3>
        <div class="price-inputs">
            <input type="number" name="min_price" value="<?= htmlspecialchars($minPrice) ?>" placeholder="Min">
            <span>-</span>
            <input type="number" name="max_price" value="<?= htmlspecialchars($maxPrice) ?>" placeholder="Max">
        </div>
    </div>

    <div class="filter-actions">
        <button type="submit" class="btn-primary w-100">Appliquer</button>
        <a href="<?= BASE_URL ?>produits.php" class="btn-reset">✕ Réinitialiser</a>
    </div>
</form>
