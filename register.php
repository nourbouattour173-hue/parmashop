<?php
session_start();
require_once __DIR__ . '/includes/db.php';
if (isset($_SESSION['user_id'])) { header("Location: " . BASE_URL . "index.php"); exit(); }

$pageTitle = "Inscription - PharmaShop";
$erreur = $succes = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom      = trim($_POST['nom']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    $adresse  = trim($_POST['adresse']);
    $tel      = trim($_POST['telephone']);

    if (empty($nom) || empty($email) || empty($password)) {
        $erreur = "Veuillez remplir tous les champs obligatoires.";
    } elseif ($password !== $confirm) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($password) < 6) {
        $erreur = "Mot de passe trop court (min. 6 caractères).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = "Email invalide.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $erreur = "Cet email est déjà utilisé.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users (nom, email, password, role, adresse, telephone) VALUES (?,?,?,'client',?,?)")
                ->execute([$nom, $email, $hash, $adresse, $tel]);
            $succes = true;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="form-container">
    <h2>Créer un compte</h2>

    <?php if ($erreur): ?>
        <div class="alert alert-error"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <?php if ($succes): ?>
        <div class="alert alert-success">
            Compte créé avec succès !<br>
            <a href="<?= BASE_URL ?>login.php" style="color:#1b5e20; font-weight:bold;">Se connecter</a>
        </div>
    <?php else: ?>
        <form method="POST">
            <div class="form-group">
                <label>Nom complet *</label>
                <input type="text" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Mot de passe * (min. 6 caractères)</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Confirmer mot de passe *</label>
                <input type="password" name="confirm_password" required>
            </div>
            <div class="form-group">
                <label>Téléphone</label>
                <input type="tel" name="telephone" value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Adresse</label>
                <textarea name="adresse"><?= htmlspecialchars($_POST['adresse'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn-primary" style="width:100%;">Créer mon compte</button>
        </form>
        <p style="text-align:center; margin-top:20px; color:#666;">
            Déjà un compte ? <a href="<?= BASE_URL ?>login.php" style="color:#2e7d32;">Se connecter</a>
        </p>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
