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

<div class="container">
    <div class="profile-header">
        <h1 class="section-title">Mon Profil</h1>
        <div class="flex gap-sm">
            <a href="<?= BASE_URL ?>logout.php" class="btn-danger btn-sm">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </div>

    <?php if ($succes): ?><div class="alert alert-success" data-auto-hide="5000"><?= htmlspecialchars($succes) ?></div><?php endif; ?>
    <?php if ($erreur): ?><div class="alert alert-error"><?= htmlspecialchars($erreur) ?></div><?php endif; ?>

    <!-- Affichage des coordonnées -->
    <div class="profile-card" id="profileInfo">
        <div class="profile-info-item">
            <span class="label">Nom complet</span>
            <span class="value"><?= htmlspecialchars($user['nom'] ?? 'Non renseigné') ?></span>
        </div>
        <div class="profile-info-item">
            <span class="label">Email</span>
            <span class="value"><?= htmlspecialchars($user['email']) ?></span>
        </div>
        <div class="profile-info-item">
            <span class="label">Téléphone</span>
            <span class="value"><?= htmlspecialchars($user['telephone'] ?? 'Non renseigné') ?></span>
        </div>
        <div class="profile-info-item">
            <span class="label">Adresse</span>
            <span class="value"><?= htmlspecialchars($user['adresse'] ?? 'Non renseigné') ?></span>
        </div>
    </div>
    <div class="profile-actions">
        <button type="button" class="btn-primary" id="btnEditProfile">
            <i class="fas fa-edit"></i> Modifier mon profil
        </button>
        <a href="<?= BASE_URL ?>mes_commandes.php" class="btn-secondary">
            <i class="fas fa-shopping-bag"></i> Voir tous mes achats
        </a>
    </div>

    <!-- Formulaire de modification (masqué par défaut) -->
    <div class="table-container mb-lg" id="editProfileForm" style="display:none;">
        <h3 class="text-primary mb-md">Modifier mes informations</h3>
        <form method="POST">
            <div class="profile-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Nom *</label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($user['nom'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Email (non modifiable)</label>
                    <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled class="input-disabled">
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
            <div class="flex gap-md mt-md">
                <button type="submit" class="btn-primary">Enregistrer les modifications</button>
                <button type="button" class="btn-outline" id="btnCancelEdit">Annuler</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btnEdit = document.getElementById('btnEditProfile');
    const btnCancel = document.getElementById('btnCancelEdit');
    const form = document.getElementById('editProfileForm');
    const info = document.getElementById('profileInfo');

    btnEdit.addEventListener('click', () => {
        form.style.display = 'block';
        info.style.display = 'none';
        btnEdit.style.display = 'none';
        form.scrollIntoView({ behavior: 'smooth' });
    });

    btnCancel.addEventListener('click', () => {
        form.style.display = 'none';
        info.style.display = 'grid';
        btnEdit.style.display = 'inline-flex';
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
