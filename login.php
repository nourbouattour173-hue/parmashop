<?php
session_start();
if (isset($_SESSION['user_id'])) { header("Location: http://localhost/parapharmacie/index.php"); exit(); }

$pageTitle = "Connexion - PharmaShop";
$erreur = "";
require_once __DIR__ . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $erreur = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nom']     = $user['nom'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];

            header("Location: http://localhost/parapharmacie/" . ($user['role'] === 'admin' ? 'admin/index.php' : 'index.php'));
            exit();
        } else {
            $erreur = "Email ou mot de passe incorrect.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="form-container">
    <h2>🔑 Connexion</h2>

    <?php if ($erreur): ?>
        <div class="alert alert-error"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <div class="alert alert-info" style="font-size:13px;">
        <strong>Admin :</strong> admin@admin.com / password<br>
        <strong>Client :</strong> farah@gmail.com / (votre mot de passe)
    </div>

    <form method="POST">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
        </div>
        <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn-primary" style="width:100%;">Se connecter</button>
    </form>
    <p style="text-align:center; margin-top:20px; color:#666;">
        Pas de compte ? <a href="http://localhost/parapharmacie/register.php" style="color:#2e7d32;">S'inscrire</a>
    </p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
