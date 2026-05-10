<?php
require_once __DIR__ . '/includes/db.php';
$pageTitle = "À Propos - PharmaShop";
require_once __DIR__ . '/includes/header.php';


$brands_stmt = $pdo->query("SELECT nom FROM brands ORDER BY nom LIMIT 8");
$brands_ap   = $brands_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="hero" style="background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%); padding: 60px 20px;">
    <h1>Notre Histoire</h1>
    <p>Découvrez l'engagement de PharmaShop pour votre santé et votre bien-être.</p>
</div>

<div class="container">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 60px; margin: 80px 0; align-items: center;">
        <div>
            <h2 class="section-title">Qui sommes-nous ?</h2>
            <p style="font-size: 1.1rem; line-height: 1.8; color: var(--color-text-light); margin-bottom: 20px;">
                PharmaShop est votre parapharmacie en ligne de confiance. Depuis notre création, nous nous efforçons de rendre les produits de santé, de beauté et de bien-être accessibles à tous, partout en Tunisie.
            </p>
            <p style="font-size: 1.1rem; line-height: 1.8; color: var(--color-text-light);">
                Notre équipe est composée de professionnels passionnés qui sélectionnent rigoureusement chaque produit pour vous garantir une qualité irréprochable et des conseils d'experts.
            </p>
        </div>
        <div>
            <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" 
                 alt="Notre équipe" 
                 style="border-radius: var(--radius-xl); box-shadow: var(--shadow-lg);">
        </div>
    </div>

    <div style="background: var(--color-primary-xlight); padding: 80px 40px; border-radius: var(--radius-xl); margin-bottom: 80px;">
        <h2 class="text-center" style="margin-bottom: 50px;">Nos Valeurs</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px;">
            <div style="text-align: center;">
                <div style="font-size: 40px; color: var(--color-primary-dark); margin-bottom: 20px;"><i class="fas fa-heart"></i></div>
                <h3 style="margin-bottom: 15px;">Engagement</h3>
                <p>Votre santé est notre priorité absolue. Nous nous engageons à vous offrir le meilleur service.</p>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 40px; color: var(--color-primary-dark); margin-bottom: 20px;"><i class="fas fa-shield-alt"></i></div>
                <h3 style="margin-bottom: 15px;">Qualité</h3>
                <p>Nous ne travaillons qu'avec des marques reconnues pour leur sérieux et leur efficacité.</p>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 40px; color: var(--color-primary-dark); margin-bottom: 20px;"><i class="fas fa-leaf"></i></div>
                <h3 style="margin-bottom: 15px;">Nature</h3>
                <p>Nous privilégions les solutions naturelles et respectueuses de votre corps et de l'environnement.</p>
            </div>
        </div>
    </div>

    <?php if (!empty($brands_ap)): ?>
    <div style="margin-bottom: 80px;">
        <h2 class="section-title">Nos Marques Partenaires</h2>
        <div style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; margin-top: 40px;">
            <?php foreach ($brands_ap as $b): ?>
                <div style="padding: 15px 30px; background: white; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--color-border-light);">
                    <span style="font-weight: 600; color: var(--color-secondary);"><?= htmlspecialchars($b['nom']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
