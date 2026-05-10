<?php
/**
 * admin/messages.php
 * Gestion des messages clients (table contact_messages)
 * 
 * Structure de la table contact_messages :
 *   id INT AUTO_INCREMENT PRIMARY KEY,
 *   nom VARCHAR(100),
 *   email VARCHAR(150),
 *   sujet VARCHAR(200),
 *   message TEXT,
 *   lu TINYINT(1) DEFAULT 0,
 *   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
 */

require_once __DIR__ . '/../includes/admin_check.php';
$pageTitle = "Messages clients - PharmaShop";
require_once __DIR__ . '/../includes/db.php';

$msg_action = "";

// --- Traitement des actions POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action    = $_POST['action']     ?? '';
    $messageId = (int)($_POST['id']   ?? 0);

    if ($action === 'marquer_lu' && $messageId > 0) {
        $pdo->prepare("UPDATE contact_messages SET lu = 1 WHERE id = ?")->execute([$messageId]);
        $msg_action = "Lu";
    } elseif ($action === 'marquer_non_lu' && $messageId > 0) {
        $pdo->prepare("UPDATE contact_messages SET lu = 0 WHERE id = ?")->execute([$messageId]);
        $msg_action = "NonLu";
    } elseif ($action === 'supprimer' && $messageId > 0) {
        $pdo->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([$messageId]);
        $msg_action = "Supprime";
    }
}

// Récupérer tous les messages, les non-lus en premier
$messages = $pdo->query("
    SELECT * FROM contact_messages
    ORDER BY lu ASC, created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

$nbNonLus = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE lu = 0")->fetchColumn();

// Message sélectionné pour affichage complet (via GET)
$messageSelectionne = null;
if (isset($_GET['voir'])) {
    $voirId = (int)$_GET['voir'];
    $vStmt  = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
    $vStmt->execute([$voirId]);
    $messageSelectionne = $vStmt->fetch(PDO::FETCH_ASSOC);

    // Marquer automatiquement comme lu à l'ouverture
    if ($messageSelectionne && $messageSelectionne['lu'] == 0) {
        $pdo->prepare("UPDATE contact_messages SET lu = 1 WHERE id = ?")->execute([$voirId]);
        $messageSelectionne['lu'] = 1;
    }
}

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="admin-content">
        <div class="flex align-center justify-between" style="margin-bottom: 25px;">
            <h1 style="color: var(--color-secondary);">
                <i class="bi bi-envelope-paper"></i> Messages clients
                <?php if ($nbNonLus > 0): ?>
                    <span class="badge badge-messages"><?= $nbNonLus ?> non lu<?= $nbNonLus > 1 ? 's' : '' ?></span>
                <?php endif; ?>
            </h1>
        </div>

        <?php if ($msg_action === 'Lu'): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle"></i> Message marqué comme lu.</div>
        <?php elseif ($msg_action === 'NonLu'): ?>
            <div class="alert alert-info"><i class="bi bi-envelope"></i> Message marqué comme non lu.</div>
        <?php elseif ($msg_action === 'Supprime'): ?>
            <div class="alert alert-error"><i class="bi bi-trash"></i> Message supprimé.</div>
        <?php endif; ?>

        <?php if ($messageSelectionne): ?>
            <!-- ================================================
                 VUE DÉTAIL D'UN MESSAGE
                 ================================================ -->
            <div class="message-detail-card">
                <div class="message-detail-header">
                    <div>
                        <h2><?= htmlspecialchars($messageSelectionne['sujet'] ?? '(sans sujet)') ?></h2>
                        <p class="message-meta">
                            <i class="bi bi-person"></i> <strong><?= htmlspecialchars($messageSelectionne['nom']) ?></strong>
                            &nbsp;&mdash;&nbsp;
                            <i class="bi bi-envelope"></i>
                            <a href="mailto:<?= htmlspecialchars($messageSelectionne['email']) ?>">
                                <?= htmlspecialchars($messageSelectionne['email']) ?>
                            </a>
                            &nbsp;&mdash;&nbsp;
                            <i class="bi bi-clock"></i>
                            <?= date('d/m/Y à H:i', strtotime($messageSelectionne['created_at'])) ?>
                        </p>
                    </div>
                    <a href="<?= BASE_URL ?>admin/messages.php" class="btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Retour à la liste
                    </a>
                </div>

                <div class="message-detail-body">
                    <?= nl2br(htmlspecialchars($messageSelectionne['message'])) ?>
                </div>

                <div class="message-detail-actions">
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $messageSelectionne['id'] ?>">
                        <?php if ($messageSelectionne['lu']): ?>
                            <input type="hidden" name="action" value="marquer_non_lu">
                            <button type="submit" class="btn-warning btn-sm">
                                <i class="bi bi-envelope"></i> Marquer non lu
                            </button>
                        <?php else: ?>
                            <input type="hidden" name="action" value="marquer_lu">
                            <button type="submit" class="btn-secondary btn-sm">
                                <i class="bi bi-envelope-open"></i> Marquer lu
                            </button>
                        <?php endif; ?>
                    </form>
                    <form method="POST" style="display:inline;"
                          onsubmit="return confirm('Supprimer définitivement ce message ?')">
                        <input type="hidden" name="id" value="<?= $messageSelectionne['id'] ?>">
                        <input type="hidden" name="action" value="supprimer">
                        <button type="submit" class="btn-danger btn-sm">
                            <i class="bi bi-trash"></i> Supprimer
                        </button>
                    </form>
                </div>
            </div>

        <?php else: ?>
            <!-- ================================================
                 LISTE DES MESSAGES
                 ================================================ -->
            <?php if (empty($messages)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-inbox"></i> Aucun message reçu pour le moment.
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Statut</th>
                                <th>Expéditeur</th>
                                <th>E-mail</th>
                                <th>Sujet</th>
                                <th>Reçu le</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $m): ?>
                                <tr class="<?= $m['lu'] ? '' : 'message-row--unread' ?>">
                                    <td>
                                        <?php if (!$m['lu']): ?>
                                            <span class="badge-unread" title="Non lu"><i class="bi bi-circle-fill"></i></span>
                                        <?php else: ?>
                                            <span style="color: var(--color-text-muted);" title="Lu"><i class="bi bi-check2-circle"></i></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= htmlspecialchars($m['nom']) ?></strong></td>
                                    <td>
                                        <a href="mailto:<?= htmlspecialchars($m['email']) ?>" style="color: var(--color-info);">
                                            <?= htmlspecialchars($m['email']) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($m['sujet'] ?? '(sans sujet)') ?></td>
                                    <td style="white-space: nowrap;">
                                        <?= date('d/m/Y H:i', strtotime($m['created_at'])) ?>
                                    </td>
                                    <td>
                                        <div class="flex gap-sm">
                                            <!-- Voir le message complet -->
                                            <a href="<?= BASE_URL ?>admin/messages.php?voir=<?= $m['id'] ?>"
                                               class="btn-primary btn-sm">
                                                <i class="bi bi-eye"></i> Voir
                                            </a>

                                            <!-- Marquer lu / non lu -->
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                                <?php if ($m['lu']): ?>
                                                    <input type="hidden" name="action" value="marquer_non_lu">
                                                    <button type="submit" class="btn-warning btn-sm" title="Marquer non lu">
                                                        <i class="bi bi-envelope"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <input type="hidden" name="action" value="marquer_lu">
                                                    <button type="submit" class="btn-secondary btn-sm" title="Marquer lu">
                                                        <i class="bi bi-envelope-open"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </form>

                                            <!-- Supprimer -->
                                            <form method="POST" style="display:inline;"
                                                  onsubmit="return confirm('Supprimer ce message ?')">
                                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                                <input type="hidden" name="action" value="supprimer">
                                                <button type="submit" class="btn-danger btn-sm" title="Supprimer">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
