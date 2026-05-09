<?php
require_once __DIR__ . '/includes/db.php';
$pageTitle = "Contact - PharmaShop";
require_once __DIR__ . '/includes/header.php';

$success = false;
$error   = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom     = trim($_POST['nom']     ?? '');
    $email   = trim($_POST['email']   ?? '');
    $sujet   = trim($_POST['sujet']   ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($nom) || empty($email) || empty($sujet) || empty($message)) {
        $error = "Veuillez remplir tous les champs.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse e-mail invalide.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (nom, email, sujet, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nom, $email, $sujet, $message]);
            $success = true;
        } catch (PDOException $e) {
            $error = "Une erreur est survenue lors de l'envoi de votre message.";
        }
    }
}
?>

<div class="hero" style="background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%); padding: 60px 20px;">
    <h1>Contactez-nous</h1>
    <p>Une question ? Un conseil ? Notre équipe est à votre écoute.</p>
</div>

<div class="container">
    <div style="max-width: 900px; margin: 60px auto; display: grid; grid-template-columns: 1fr 1.5fr; gap: 60px;">
        
        <div>
            <h2 class="section-title">Coordonnées</h2>
            <div style="margin-top: 30px;">
                <div style="display: flex; gap: 20px; margin-bottom: 30px;">
                    <div style="font-size: 24px; color: var(--color-primary-dark);"><i class="fas fa-map-marker-alt"></i></div>
                    <div>
                        <h4 style="margin-bottom: 5px;">Adresse</h4>
                        <p style="color: var(--color-text-light);">123 Rue de la Santé, Tunis, Tunisie</p>
                    </div>
                </div>
                <div style="display: flex; gap: 20px; margin-bottom: 30px;">
                    <div style="font-size: 24px; color: var(--color-primary-dark);"><i class="fas fa-phone-alt"></i></div>
                    <div>
                        <h4 style="margin-bottom: 5px;">Téléphone</h4>
                        <p style="color: var(--color-text-light);">+216 71 000 000</p>
                    </div>
                </div>
                <div style="display: flex; gap: 20px; margin-bottom: 30px;">
                    <div style="font-size: 24px; color: var(--color-primary-dark);"><i class="fas fa-envelope"></i></div>
                    <div>
                        <h4 style="margin-bottom: 5px;">E-mail</h4>
                        <p style="color: var(--color-text-light);">contact@parmashop.tn</p>
                    </div>
                </div>
                <div style="display: flex; gap: 20px;">
                    <div style="font-size: 24px; color: var(--color-primary-dark);"><i class="fas fa-clock"></i></div>
                    <div>
                        <h4 style="margin-bottom: 5px;">Horaires</h4>
                        <p style="color: var(--color-text-light);">Lun - Sam : 08h30 - 19h30</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-container" style="margin: 0; width: 100%; max-width: 100%;">
            <h2 class="section-title" style="margin-top: 0;">Envoyez-nous un message</h2>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Nom complet</label>
                    <input type="text" name="nom" required placeholder="Votre nom...">
                </div>
                <div class="form-group">
                    <label>Adresse e-mail</label>
                    <input type="email" name="email" required placeholder="votre@email.com">
                </div>
                <div class="form-group">
                    <label>Sujet</label>
                    <select name="sujet" required>
                        <option value="">Sélectionnez un sujet</option>
                        <option value="Information">Demande d'information</option>
                        <option value="Commande">Suivi de commande</option>
                        <option value="Produit">Conseil sur un produit</option>
                        <option value="Autre">Autre demande</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea name="message" rows="5" required placeholder="Comment pouvons-nous vous aider ?"></textarea>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%;">Envoyer le message</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
