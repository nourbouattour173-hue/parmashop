<?php
require_once __DIR__ . '/../includes/admin_check.php';
$pageTitle = "Produits - Admin";
require_once __DIR__ . '/../includes/db.php';

// Suppression
if (isset($_GET['supprimer'])) {
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([(int)$_GET['supprimer']]);
    header("Location: " . BASE_URL . "admin/produits.php?msg=supprime");
    exit();
}

$recherche = trim($_GET['q'] ?? '');
$sql = "
    SELECT p.id, p.nom, b.nom AS marque, c.nom AS categorie,
           COUNT(pv.id) AS nb_variantes, MIN(pv.prix) AS prix_min
    FROM products p
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN product_variants pv ON pv.product_id = p.id
    WHERE 1=1
";
$params = [];
if (!empty($recherche)) { $sql .= " AND p.nom LIKE ?"; $params[] = "%$recherche%"; }
$sql .= " GROUP BY p.id ORDER BY p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div class="admin-content">
        <div class="flex justify-between align-center mb-lg">
            <h1 class="text-primary">📦 Gestion des Produits</h1>
            <a href="<?= BASE_URL ?>admin/ajouter_produit.php" class="btn-primary">➕ Ajouter</a>
        </div>

        <?php if (($_GET['msg'] ?? '') === 'supprime'): ?>
            <div class="alert alert-success">Produit supprimé.</div>
        <?php elseif (($_GET['msg'] ?? '') === 'ajoute'): ?>
            <div class="alert alert-success">Produit ajouté.</div>
        <?php elseif (($_GET['msg'] ?? '') === 'modifie'): ?>
            <div class="alert alert-success">Produit modifié.</div>
        <?php endif; ?>

        <form method="GET" class="flex gap-sm mb-lg">
            <input type="text" name="q" value="<?= htmlspecialchars($recherche) ?>"
                   placeholder="🔍 Rechercher..." class="flex-1">
            <button type="submit" class="btn-primary">Rechercher</button>
            <?php if ($recherche): ?>
                <a href="<?= BASE_URL ?>admin/produits.php" class="align-center p-md text-muted">✕</a>
            <?php endif; ?>
        </form>

        <div class="table-container">
            <p class="text-muted mb-sm"><?= count($produits) ?> produit(s)</p>
            <table>
                <thead>
                    <tr><th>ID</th><th>Nom</th><th>Marque</th><th>Catégorie</th><th>Prix min.</th><th>Variantes</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($produits as $prod): ?>
                        <tr>
                            <td><?= $prod['id'] ?></td>
                            <td><strong><?= htmlspecialchars($prod['nom']) ?></strong></td>
                            <td><?= htmlspecialchars($prod['marque'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($prod['categorie'] ?? '-') ?></td>
                            <td><?= $prod['prix_min'] ? number_format($prod['prix_min'], 2) . ' DT' : '-' ?></td>
                            <td style="text-align:center;"><?= $prod['nb_variantes'] ?></td>
                            <td style="white-space:nowrap;">
                                <a href="<?= BASE_URL ?>admin/modifier_produit.php?id=<?= $prod['id'] ?>" class="btn-warning">✏️ Modifier</a>
                                &nbsp;
                                <a href="<?= BASE_URL ?>admin/produits.php?supprimer=<?= $prod['id'] ?>"
                                   class="btn-danger"
                                   onclick="return confirm('Supprimer ce produit ?')">🗑️ Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
