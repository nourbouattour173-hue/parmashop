<?php
require_once __DIR__ . '/../includes/admin_check.php';
$pageTitle = "Utilisateurs - Admin";
require_once __DIR__ . '/../includes/db.php';

if (isset($_GET['supprimer'])) {
    $uid = (int)$_GET['supprimer'];
    if ($uid === (int)$_SESSION['user_id']) {
        $msgErr = "Vous ne pouvez pas supprimer votre propre compte.";
    } else {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
        header("Location: " . BASE_URL . "admin/utilisateurs.php?msg=supprime");
        exit();
    }
}

$users = $pdo->query("
    SELECT u.*, COUNT(o.id) AS nb_commandes
    FROM users u
    LEFT JOIN orders o ON o.user_id = u.id
    GROUP BY u.id
    ORDER BY u.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div class="admin-content">
        <h1 style="color:#1b5e20; margin-bottom:20px;"><i class="bi bi-people"></i> Gestion des Utilisateurs</h1>

        <?php if (($_GET['msg'] ?? '') === 'supprime'): ?>
            <div class="alert alert-success">Utilisateur supprimé.</div>
        <?php endif; ?>
        <?php if (!empty($msgErr)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($msgErr) ?></div>
        <?php endif; ?>

        <div class="table-container">
            <p style="color:#888; margin-bottom:15px;"><?= count($users) ?> utilisateur(s)</p>
            <table>
                <thead>
                    <tr><th>ID</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Téléphone</th><th>Commandes</th><th>Inscrit le</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td><strong><?= htmlspecialchars($u['nom'] ?? '-') ?></strong></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <span class="badge <?= $u['role'] === 'admin' ? 'badge-admin' : 'badge-client' ?>">
                                    <?= $u['role'] ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($u['telephone'] ?? '-') ?></td>
                            <td style="text-align:center;"><?= $u['nb_commandes'] ?></td>
                            <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                            <td>
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                    <a href="<?= BASE_URL ?>admin/utilisateurs.php?supprimer=<?= $u['id'] ?>"
                                       class="btn-danger" style="font-size:13px;"
                                       onclick="return confirm('Supprimer cet utilisateur ?')"><i class="bi bi-trash"></i> Supprimer</a>
                                <?php else: ?>
                                    <span style="color:#aaa; font-size:13px;">(vous)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
