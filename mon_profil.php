<?php
require_once __DIR__ . '/includes/auth_check.php';
$pageTitle = "Mon Profil - PharmaShop";
require_once __DIR__ . '/includes/db.php';

$erreur = $succes = "";

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom    = trim($_POST['nom']);
    $tel    = trim($_POST['telephone']);
    $adr    = trim($_POST['adresse']);
    $newPwd = $_POST['new_password'];
    $conf   = $_POST['confirm_password'];

    if (empty($nom)) {
        $erreur = "Le nom est obligatoire.";
    } elseif (!empty($newPwd) && $newPwd !== $conf) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } elseif (!empty($newPwd) && strlen($newPwd) < 6) {
        $erreur = "Mot de passe trop court.";
    } else {
        if (!empty($newPwd)) {
            $hash = password_hash($newPwd, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET nom=?, telephone=?, adresse=?, password=? WHERE id=?")
                ->execute([$nom, $tel, $adr, $hash, $_SESSION['user_id']]);
        } else {
            $pdo->prepare("UPDATE users SET nom=?, telephone=?, adresse=? WHERE id=?")
                ->execute([$nom, $tel, $adr, $_SESSION['user_id']]);
        }
        $_SESSION['nom'] = $nom;
        $succes = "Profil mis à jour.";
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Commandes
$cmdStmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY date_commande DESC");
$cmdStmt->execute([$_SESSION['user_id']]);
$commandes = $cmdStmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width:900px;">
    <h1 class="section-title">👤 Mon Profil</h1>

    <?php if ($succes): ?><div class="alert alert-success"><?= htmlspecialchars($succes) ?></div><?php endif; ?>
    <?php if ($erreur): ?><div class="alert alert-error"><?= htmlspecialchars($erreur) ?></div><?php endif; ?>

    <div class="table-container" style="margin-bottom:30px;">
        <h3 style="color:#2e7d32; margin-bottom:20px;">✏️ Modifier mes informations</h3>
        <form method="POST">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                <div class="form-group">
                    <label>Nom *</label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($user['nom'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Email (non modifiable)</label>
                    <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled style="background:#f5f5f5;">
                </div>
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="tel" name="telephone" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Adresse</label>
                    <input type="text" name="adresse" value="<?= htmlspecialchars($user['adresse'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Nouveau mot de passe</label>
                    <input type="password" name="new_password" placeholder="Laisser vide = inchangé">
                </div>
                <div class="form-group">
                    <label>Confirmer mot de passe</label>
                    <input type="password" name="confirm_password">
                </div>
            </div>
            <button type="submit" class="btn-primary">💾 Enregistrer</button>
        </form>
    </div>

    <div class="table-container">
        <h3 style="color:#2e7d32; margin-bottom:20px;">📦 Mes Commandes</h3>
        <?php if (empty($commandes)): ?>
            <p style="color:#888;">Aucune commande pour le moment.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr><th>N°</th><th>Date</th><th>Total</th><th>Paiement</th><th>Statut</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($commandes as $cmd): ?>
                        <tr>
                            <td>#<?= $cmd['id'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($cmd['date_commande'])) ?></td>
                            <td><?= number_format($cmd['total'], 2) ?> DT</td>
                            <td><?= htmlspecialchars($cmd['methode_paiement']) ?></td>
                            <td style="color:#2e7d32; font-weight:bold;">
                                <?= ucfirst(str_replace('_', ' ', $cmd['statut'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
