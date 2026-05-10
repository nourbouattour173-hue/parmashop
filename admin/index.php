<?php
require_once __DIR__ . '/../includes/admin_check.php';
$pageTitle = "Administration - PharmaShop";
require_once __DIR__ . '/../includes/db.php';

$nbProduits  = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$nbUsers     = $pdo->query("SELECT COUNT(*) FROM users WHERE role='client'")->fetchColumn();
$nbCommandes = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$chiffreAff  = $pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE statut != 'annulée'")->fetchColumn();

$dernieres = $pdo->query("
    SELECT o.*, u.nom AS client
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.date_commande DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div class="admin-content">
        <h1 style="color:#1b5e20; margin-bottom:25px;"><i class="bi bi-speedometer2"></i> Tableau de bord</h1>

        <div class="stat-grid">
            <?php foreach ([
                ['bi-box-seam', $nbProduits,              'Produits',          'var(--color-success)'],
                ['bi-people', $nbUsers,                 'Clients',           'var(--color-info)'],
                ['bi-cart', $nbCommandes,             'Commandes',         'var(--color-warning)'],
                ['bi-cash-stack', number_format($chiffreAff,2).' DT', 'CA Global', 'var(--color-secondary-light)'],
            ] as $stat): ?>
                <div class="stat-card" style="--accent: <?= $stat[3] ?>;">
                    <div class="stat-icon"><i class="bi <?= $stat[0] ?>"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $stat[1] ?></div>
                        <div class="stat-label"><?= $stat[2] ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="table-container">
            <h3 style="color:#1b5e20; margin-bottom:15px;"><i class="bi bi-clock-history"></i> Dernières commandes</h3>
            <table>
                <thead><tr><th>N°</th><th>Client</th><th>Date</th><th>Total</th><th>Statut</th></tr></thead>
                <tbody>
                    <?php foreach ($dernieres as $cmd): ?>
                        <tr>
                            <td>#<?= $cmd['id'] ?></td>
                            <td><?= htmlspecialchars($cmd['client'] ?? 'Inconnu') ?></td>
                            <td><?= date('d/m/Y', strtotime($cmd['date_commande'])) ?></td>
                            <td><?= number_format($cmd['total'], 2) ?> DT</td>
                            <td><?= ucfirst(str_replace('_', ' ', $cmd['statut'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div style="margin-top:15px;">
                <a href="<?= BASE_URL ?>admin/commandes.php" style="color:#2e7d32;">Voir toutes les commandes</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
