<?php
require_once __DIR__ . '/../includes/admin_check.php';
$pageTitle = "Commandes - Admin";
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['maj_statut'])) {
    $statutsValides = ['en_attente','confirmée','en_preparation','expédiée','livrée','annulée','remboursée'];
    $statut = $_POST['statut'];
    if (in_array($statut, $statutsValides)) {
        $pdo->prepare("UPDATE orders SET statut=? WHERE id=?")->execute([$statut, (int)$_POST['order_id']]);
    }
    header("Location: " . BASE_URL . "admin/commandes.php?msg=ok");
    exit();
}

$commandes = $pdo->query("
    SELECT o.*, u.nom AS client, u.email
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.date_commande DESC
")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div class="admin-content">
        <h1 style="color:#1b5e20; margin-bottom:20px;"><i class="bi bi-cart"></i> Gestion des Commandes</h1>

        <?php if (($_GET['msg'] ?? '') === 'ok'): ?>
            <div class="alert alert-success">Statut mis à jour.</div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr><th>N°</th><th>Client</th><th>Date</th><th>Total</th><th>Paiement</th><th>Statut actuel</th><th>Changer statut</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($commandes)): ?>
                        <tr><td colspan="7" style="color:#888;">Aucune commande.</td></tr>
                    <?php else: ?>
                        <?php foreach ($commandes as $cmd): ?>
                            <tr>
                                <td><strong>#<?= $cmd['id'] ?></strong></td>
                                <td>
                                    <?= htmlspecialchars($cmd['client'] ?? 'Inconnu') ?><br>
                                    <small style="color:#aaa;"><?= htmlspecialchars($cmd['email'] ?? '') ?></small>
                                </td>
                                <td><?= date('d/m/Y', strtotime($cmd['date_commande'])) ?></td>
                                <td><strong><?= number_format($cmd['total'], 2) ?> DT</strong></td>
                                <td><?= htmlspecialchars($cmd['methode_paiement']) ?></td>
                                <td style="color:#2e7d32; font-weight:bold;">
                                    <?= ucfirst(str_replace('_', ' ', $cmd['statut'])) ?>
                                </td>
                                <td>
                                    <form method="POST" style="display:flex; gap:5px;">
                                        <input type="hidden" name="order_id" value="<?= $cmd['id'] ?>">
                                        <select name="statut" style="padding:5px; border:1px solid #ccc; border-radius:4px; font-size:13px;">
                                            <?php foreach (['en_attente','confirmée','en_preparation','expédiée','livrée','annulée','remboursée'] as $s): ?>
                                                <option value="<?= $s ?>" <?= $cmd['statut'] === $s ? 'selected' : '' ?>>
                                                    <?= ucfirst(str_replace('_', ' ', $s)) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" name="maj_statut" class="btn-primary" style="padding:5px 10px; font-size:13px;">✓</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
