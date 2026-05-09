<?php
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$success = false;
$errors  = [];
$form    = ['nom' => '', 'email' => '', 'sujet' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom     = trim($_POST['nom']     ?? '');
    $email   = trim($_POST['email']   ?? '');
    $sujet   = trim($_POST['sujet']   ?? '');
    $message = trim($_POST['message'] ?? '');

    if (strlen($nom) < 2)               $errors['nom']     = 'Veuillez entrer votre nom complet.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Adresse e-mail invalide.';
    if (empty($sujet))                  $errors['sujet']   = 'Veuillez choisir un sujet.';
    if (strlen($message) < 20)          $errors['message'] = 'Le message doit contenir au moins 20 caractères.';

    if (empty($errors)) {
        $_SESSION['contact_sent'] = true;
        $success = true;
        $form = ['nom' => '', 'email' => '', 'sujet' => '', 'message' => ''];
    } else {
        $form = compact('nom', 'email', 'sujet', 'message');
    }
}

$page_title = 'Contactez-nous';
require_once 'includes/header.php';
require_once 'views/contact.html';
?>
<script src="assets/js/contact.js"></script>
<?php require_once 'includes/footer.php'; ?>
