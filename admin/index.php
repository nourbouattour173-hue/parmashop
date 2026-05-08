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

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div class="admin-content">
        <h1 style="color:#1b5e20; margin-bottom:25px;">📊 Tableau de bord</h1>

        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:20px; margin-bottom:35px;">
            <?php foreach ([
                ['📦', $nbProduits,              'Produits',          '#2e7d32'],
                ['👥', $nbUsers,                 'Clients',           '#1565c0'],
                ['🛒', $nbCommandes,             'Commandes',         '#f57f17'],
                ['💰', number_format($chiffreAff,2).' DT', 'Chiffre d\'affaires', '#6a1b9a'],
            ] as $stat): ?>
                <div style="background:white; border-radius:10px; padding:25px; text-align:center;
                            box-shadow:0 2px 8px rgba(0,0,0,0.07); border-top:4px solid <?= $stat[3] ?>;">
                    <div style="font-size:35px;"><?= $stat[0] ?></div>
                    <div style="font-size:28px; font-weight:bold; color:<?= $stat[3] ?>;"><?= $stat[1] ?></div>
                    <div style="color:#888;"><?= $stat[2] ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="table-container">
            <h3 style="color:#1b5e20; margin-bottom:15px;">🕐 Dernières commandes</h3>
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
                <a href="<?= BASE_URL ?>admin/commandes.php" style="color:#2e7d32;">Voir toutes les commandes →</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
