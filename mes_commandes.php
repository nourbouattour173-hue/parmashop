<?php
require_once __DIR__ . '/includes/auth_check.php';
$pageTitle = "Mes Achats - PharmaShop";
require_once __DIR__ . '/includes/db.php';


$cmdStmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY date_commande DESC");
$cmdStmt->execute([$_SESSION['user_id']]);
$commandes = $cmdStmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="profile-header">
        <h1 class="section-title">Mes Achats</h1>
        <div class="flex gap-sm">
            <a href="<?= BASE_URL ?>mon_profil.php" class="btn-outline btn-sm">
                <i class="fas fa-user"></i> Mon profil
            </a>
            <a href="<?= BASE_URL ?>logout.php" class="btn-danger btn-sm">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </div>

    <div class="table-container">
        <?php if (empty($commandes)): ?>
            <p class="text-muted" style="padding: 20px;">Vous n'avez pas encore effectué d'achats.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>N° Commande</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Paiement</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commandes as $cmd): ?>
                        <tr>
                            <td>#<?= $cmd['id'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($cmd['date_commande'])) ?></td>
                            <td class="font-bold"><?= number_format($cmd['total'], 2) ?> DT</td>
                            <td><?= htmlspecialchars($cmd['methode_paiement']) ?></td>
                            <td>
                                <span class="badge badge-client">
                                    <?= ucfirst(str_replace('_', ' ', $cmd['statut'])) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div style="margin-top: 30px;">
        <a href="<?= BASE_URL ?>produits.php" class="btn-primary">
            <i class="fas fa-shopping-cart"></i> Continuer mes achats
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
