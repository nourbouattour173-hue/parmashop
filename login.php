<?php
session_start();
require_once __DIR__ . '/includes/db.php';
if (isset($_SESSION['user_id'])) { header("Location: " . BASE_URL . "index.php"); exit(); }

$pageTitle = "Connexion - PharmaShop";
$erreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);#trim:enléve les espaces
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $erreur = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
// removed stray opening PHP tag
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nom']     = $user['nom'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];

            // Set session cookie lifetime based on role
            // 0 means until browser is closed
            $expire = ($user['role'] === 'admin') ? 0 : time() + (2 * 24 * 60 * 60);
            
            // Update the session cookie with the correct lifetime
            setcookie(session_name(), session_id(), [
                'expires' => $expire,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            session_regenerate_id(true);

            header("Location: " . BASE_URL . ($user['role'] === 'admin' ? 'admin/index.php' : 'index.php'));
            exit();
        } else {
            $erreur = "Email ou mot de passe incorrect.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="form-container">
    <h2><i class="bi bi-key"></i> Connexion</h2>

    <?php if ($erreur): ?>
        <div class="alert alert-error"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    

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
        Pas de compte ? <a href="<?= BASE_URL ?>register.php" style="color:#2e7d32;">S'inscrire</a>
    </p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>