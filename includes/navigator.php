<?php
// On récupère les catégories
$categoriesNav = $pdo->query("SELECT * FROM categories ORDER BY position")->fetchAll(PDO::FETCH_ASSOC);

$currentCatId = $_GET['categorie'] ?? '';
$currentSubId = $_GET['sous_categorie'] ?? '';

// Sous-catégories de la catégorie courante
$subcategoriesNav = [];
if (!empty($currentCatId)) {
    $sStmt = $pdo->prepare("SELECT * FROM subcategories WHERE category_id = ? ORDER BY position");
    $sStmt->execute([$currentCatId]);
    $subcategoriesNav = $sStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="category-nav-wrapper">
    <div class="container">
        <nav class="horizontal-navigator">
            <ul class="main-cats">
                <li>
                    <a href="<?= BASE_URL ?>produits.php" class="<?= empty($currentCatId) ? 'active' : '' ?>">Tout voir</a>
                </li>
                <?php foreach ($categoriesNav as $cat): ?>
                    <li>
                        <a href="<?= BASE_URL ?>produits.php?categorie=<?= $cat['id'] ?>" class="<?= $currentCatId == $cat['id'] ? 'active' : '' ?>">
                            <?= htmlspecialchars($cat['nom']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</div>

<?php if (!empty($subcategoriesNav)): ?>
    <div class="subcategory-nav-wrapper">
        <div class="container">
            <nav class="horizontal-sub-navigator">
                <ul>
                    <?php foreach ($subcategoriesNav as $scat): ?>
                        <li>
                            <a href="<?= BASE_URL ?>produits.php?categorie=<?= $currentCatId ?>&sous_categorie=<?= $scat['id'] ?>" class="<?= $currentSubId == $scat['id'] ? 'active' : '' ?>">
                                <?= htmlspecialchars($scat['nom']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </div>
<?php endif; ?>
