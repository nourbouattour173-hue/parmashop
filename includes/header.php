<?php 
if (session_status() === PHP_SESSION_NONE) session_start(); 
require_once __DIR__ . '/db.php';

// Redirect to login if not logged in
$currentPage = basename($_SERVER['PHP_SELF']);
if (!isset($_SESSION['user_id']) && !in_array($currentPage, ['login.php', 'register.php'])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// ── Compteurs ──
$cart_count = 0;
if (!empty($_SESSION['panier']))
    foreach ($_SESSION['panier'] as $i) $cart_count += $i['quantite'];

$favorites_count = 0;
if (isset($_SESSION['user_id'])) {
    $fStmt = $pdo->prepare("SELECT COUNT(*) FROM favoris WHERE user_id = ?");
    $fStmt->execute([$_SESSION['user_id']]);
    $favorites_count = $fStmt->fetchColumn();
}

// ── Page courante ──
$current_page = basename($_SERVER['PHP_SELF']);

// ── Catégories depuis la BDD ──
$categoriesNav = $pdo->query("SELECT * FROM categories ORDER BY position")->fetchAll(PDO::FETCH_ASSOC);

// Construire les enfants (sous-catégories)
$categories = [];
foreach ($categoriesNav as $cat) {
    $sStmt = $pdo->prepare("SELECT * FROM subcategories WHERE category_id = ? ORDER BY position");
    $sStmt->execute([$cat['id']]);
    $cat['children'] = $sStmt->fetchAll(PDO::FETCH_ASSOC);
    $categories[] = $cat;
}

// ── Filtres actifs depuis l'URL ──
$active_cat_id = isset($_GET['categorie']) ? (int)$_GET['categorie'] : 0;
$active_subcat_id = isset($_GET['sous_categorie']) ? (int)$_GET['sous_categorie'] : 0;

$style_file = __DIR__ . '/../assets/css/style.css';
$style_version = file_exists($style_file) ? filemtime($style_file) : time();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>
    <?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : '' ?>PharmaShop
  </title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap"
        rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous">

  <!-- Bootstrap Icons (conservé pour compatibilité) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <!-- CSS Principal -->
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css?v=<?= $style_version ?>">
</head>
<body>

<!-- ========================================
     NAVBAR
     ======================================== -->
<nav class="navbar" role="navigation" aria-label="Navigation principale">
  <div class="container">

    <!-- Bouton Hamburger (mobile) -->
    <button class="hamburger-btn"
            id="hamburgerBtn"
            aria-label="Ouvrir le menu catégories"
            aria-expanded="false"
            aria-controls="categoryBar">
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
    </button>

    <!-- Logo -->
    <a href="<?= BASE_URL ?>index.php" class="navbar-brand">
      <div class="logo-icon">
        <i class="fas fa-leaf" aria-hidden="true"></i>
      </div>
      Pharma<span>Shop</span>
    </a>

    <!-- Barre de recherche -->
    <form action="<?= BASE_URL ?>produits.php"
          method="GET"
          class="navbar-search"
          role="search"
          aria-label="Recherche de produits">
      <i class="fas fa-search search-icon" aria-hidden="true"></i>
      <input
        type="search"
        name="q"
        placeholder="Rechercher un produit, une marque..."
        value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
        autocomplete="off"
        aria-label="Rechercher un produit"
      >
      <button type="submit" class="search-submit-btn" aria-label="Lancer la recherche">
        <i class="fas fa-arrow-right" aria-hidden="true"></i>
      </button>
    </form>

    <!-- Liens de navigation -->
    <div class="navbar-nav">

      <!-- À propos -->
      <a href="<?= BASE_URL ?>apropos.php"
         class="nav-link <?= $current_page === 'apropos.php' ? 'active' : '' ?>">
        <i class="fas fa-circle-info" aria-hidden="true"></i>
        <span>À propos</span>
      </a>

      <!-- Contact -->
      <a href="<?= BASE_URL ?>contact.php"
         class="nav-link <?= $current_page === 'contact.php' ? 'active' : '' ?>">
        <i class="fas fa-envelope" aria-hidden="true"></i>
        <span>Contact</span>
      </a>

      <?php if (isset($_SESSION['user_id'])): ?>

        <!-- Profil utilisateur -->
        <a href="<?= BASE_URL ?>mon_profil.php"
           class="nav-link <?= $current_page === 'mon_profil.php' ? 'active' : '' ?>">
          <i class="fas fa-user" aria-hidden="true"></i>
          <span><?= htmlspecialchars($_SESSION['user_nom'] ?? 'Mon compte') ?></span>
        </a>

        <!-- Cookie : dernière connexion -->
        <?php if (isset($_COOKIE['derniere_connexion'])): ?>
          <span style='font-size:0.75rem;color:#888;white-space:nowrap;' title='Dernière connexion'>
            <i class='bi bi-clock-history'></i>
            <?= htmlspecialchars($_COOKIE['derniere_connexion']) ?>
          </span>
        <?php endif; ?>

        <!-- Bouton Admin -->
        <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
          <a href="<?= BASE_URL ?>admin/index.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-cog" aria-hidden="true"></i>
            <span>Admin</span>
          </a>
        <?php endif; ?>

        <!-- Déconnexion -->
        <a href="<?= BASE_URL ?>logout.php"
           class="nav-link text-danger"
           title="Déconnexion"
           aria-label="Se déconnecter">
          <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
        </a>

      <?php else: ?>

        <!-- Connexion -->
        <a href="<?= BASE_URL ?>login.php"
           class="nav-link <?= $current_page === 'login.php' ? 'active' : '' ?>">
          <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
          <span>Connexion</span>
        </a>

        <!-- Inscription -->
        <a href="<?= BASE_URL ?>register.php"
           class="nav-link <?= $current_page === 'register.php' ? 'active' : '' ?>">
          <i class="fas fa-user-plus" aria-hidden="true"></i>
          <span>Inscription</span>
        </a>

      <?php endif; ?>

      <!-- Favoris -->
      <a href="<?= BASE_URL ?>favoris.php"
         class="cart-btn"
         title="Mes favoris (<?= $favorites_count ?>)"
         aria-label="Mes favoris, <?= $favorites_count ?> article(s)">
        <i class="fas fa-heart" aria-hidden="true"></i>
        <?php if ($favorites_count > 0): ?>
          <span class="cart-badge" aria-live="polite"><?= $favorites_count ?></span>
        <?php endif; ?>
      </a>

      <!-- Panier -->
      <a href="<?= BASE_URL ?>panier.php"
         class="cart-btn"
         title="Mon panier (<?= $cart_count ?>)"
         aria-label="Mon panier, <?= $cart_count ?> article(s)">
        <i class="fas fa-shopping-cart" aria-hidden="true"></i>
        <?php if ($cart_count > 0): ?>
          <span class="cart-badge" aria-live="polite"><?= $cart_count ?></span>
        <?php endif; ?>
      </a>

    </div>
  </div>
</nav>

<!-- ========================================
     BARRE CATÉGORIES + MEGA MENU
     ======================================== -->
<nav class="category-bar"
     id="categoryBar"
     aria-label="Navigation par catégories">

  <!-- Entête visible uniquement sur mobile -->
  <div class="category-bar-header">
    <span>
      <i class="fas fa-th-large" aria-hidden="true"></i>
      Catégories
    </span>
    <button class="category-bar-close"
            id="categoryBarClose"
            aria-label="Fermer le menu catégories">
      <i class="fas fa-times" aria-hidden="true"></i>
    </button>
  </div>

  <div class="container">
    <ul class="category-bar-list" role="menubar">

      <?php foreach ($categories as $cat): ?>
        <?php $hasChildren = !empty($cat['children']); ?>

        <li class="category-bar-item <?= $hasChildren ? 'has-children' : '' ?> <?= $active_cat_id === (int)$cat['id'] ? 'current' : '' ?>"
            role="none">

          <!-- Lien catégorie parente -->
          <a href="<?= BASE_URL ?>produits.php?categorie=<?= (int)$cat['id'] ?>"
             class="category-bar-link <?= ($active_cat_id === (int)$cat['id'] && !$active_subcat_id) ? 'active' : '' ?>"
             role="menuitem"
             <?= $hasChildren ? 'aria-haspopup="true" aria-expanded="false"' : '' ?>>
            <span class="category-bar-label"><?= htmlspecialchars($cat['nom']) ?></span>
            <?php if ($hasChildren): ?>
              <i class="fas fa-chevron-down arrow-icon" aria-hidden="true"></i>
            <?php endif; ?>
          </a>

          <!-- Mega Menu dropdown -->
          <?php if ($hasChildren): ?>
            <div class="mega-menu" role="region" aria-label="Sous-catégories de <?= htmlspecialchars($cat['nom']) ?>">
              <div class="mega-menu-inner">

                <!-- Lien "Tout voir" -->
                <div class="mega-menu-header">
                  <a href="<?= BASE_URL ?>produits.php?categorie=<?= (int)$cat['id'] ?>"
                     class="mega-menu-parent-link">
                    <span class="mega-menu-parent-text">Voir tous les produits de <?= htmlspecialchars($cat['nom']) ?></span>
                    <i class="fas fa-arrow-right mega-menu-arrow" aria-hidden="true"></i>
                  </a>
                </div>

                <!-- Liste des sous-catégories -->
                <ul class="mega-menu-list" role="menu">
                  <?php foreach ($cat['children'] as $child): ?>
                    <li role="none">
                      <a href="<?= BASE_URL ?>produits.php?categorie=<?= (int)$cat['id'] ?>&sous_categorie=<?= (int)$child['id'] ?>"
                         class="mega-menu-link <?= $active_subcat_id === (int)$child['id'] ? 'active' : '' ?>"
                         role="menuitem">
                        <span class="mega-menu-link-text"><?= htmlspecialchars($child['nom']) ?></span>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>

              </div>
            </div>
          <?php endif; ?>

        </li>
      <?php endforeach; ?>

    </ul>
  </div>
</nav>

<!-- Overlay fond (mobile) -->
<div class="category-overlay" id="categoryOverlay" aria-hidden="true"></div>
