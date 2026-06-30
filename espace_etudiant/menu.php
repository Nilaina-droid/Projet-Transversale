<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'etudiant') {
    header("Location: ../index.php");
    exit();
}
require_once '../config/connexion.php';

$repas = ($_GET['repas'] ?? 'dejeuner') === 'diner' ? 'diner' : 'dejeuner';
$type_service = $repas === 'diner' ? 'DINER' : 'DEJEUNER';

$menus = $pdo->query("SELECT * FROM menu WHERE statut = 'PUBLIE' ORDER BY semaine_du DESC")->fetchAll();

// On récupère uniquement les plats reliés (via la table quota) au service correspondant (DEJEUNER ou DINER)
$stmt = $pdo->prepare("
    SELECT DISTINCT p.*
    FROM plat p
    JOIN quota q ON q.id_plat = p.id_plat
    JOIN service s ON s.id_service = q.id_service
    WHERE s.Type_service = :type_service
    ORDER BY p.type_plat ASC
");
$stmt->execute(['type_service' => $type_service]);
$plats_bdd = $stmt->fetchAll();

$type_icons = [
    'Viande'     => '<i class="fas fa-drumstick-bite"></i>',
    'Poisson'    => '<i class="fas fa-fish"></i>',
    'Végétarien' => '<i class="fas fa-seedling"></i>',
    'Légumes'    => '<i class="fas fa-seedling"></i>',
    'Dessert'    => '<i class="fas fa-ice-cream"></i>',
    'Boisson'    => '<i class="fas fa-glass-martini-alt"></i>',
    'Entrée'     => '<i class="fas fa-utensils"></i>',
    'Féculent'   => '<i class="fas fa-bowl-rice"></i>',
];

// Données fictives de secours — utilisées uniquement si aucun plat n'est encore relié à ce service via la table quota
$plats_fictifs = [
    'dejeuner' => [
        ['emoji'=>'<i class="fas fa-utensils"></i>','type'=>'Plat principal', 'nom'=>'Riz au poulet rôti',       'desc'=>'Riz parfumé servi avec du poulet rôti aux herbes et sauce tomate maison.', 'calories'=>'650', 'allergens'=>''],
        ['emoji'=>'<i class="fas fa-leaf"></i>','type'=>'Accompagnement',  'nom'=>'Salade de légumes frais',  'desc'=>'Mélange de carottes, concombres et tomates avec vinaigrette légère.',       'calories'=>'120', 'allergens'=>''],
        ['emoji'=>'<i class="fas fa-apple-alt"></i>','type'=>'Dessert',          'nom'=>'Fruit de saison',          'desc'=>'Banane ou fruit frais selon arrivage du marché.',                           'calories'=>'90',  'allergens'=>''],
        ['emoji'=>'<i class="fas fa-tint"></i>','type'=>'Boisson',          'nom'=>'Eau minérale',             'desc'=>'Bouteille d\'eau minérale 50cl.',                                           'calories'=>'0',   'allergens'=>''],
    ],
    'diner' => [
        ['emoji'=>'<i class="fas fa-soup"></i>','type'=>'Plat principal', 'nom'=>'Soupe de légumes',    'desc'=>'Soupe chaude maison avec carottes, pommes de terre et poireaux.', 'calories'=>'280', 'allergens'=>''],
        ['emoji'=>'<i class="fas fa-egg"></i>','type'=>'Accompagnement', 'nom'=>'Omelette aux herbes', 'desc'=>'Omelette légère aux fines herbes fraîches.',                      'calories'=>'210', 'allergens'=>'Œufs'],
        ['emoji'=>'<i class="fas fa-bread-slice"></i>','type'=>'Pain',            'nom'=>'Pain maison',         'desc'=>'Petit pain cuit le matin même.',                                 'calories'=>'150', 'allergens'=>'Gluten'],
    ],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu du jour — RU ESMIA</title>
    <link rel="stylesheet" href="../Style/menu.css">
    <link rel="stylesheet" href="../assets/fontawasome/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<div class="navbar">
    <a href="dashboard.php" class="nav-brand">
        <img src="../assets/images/esmia.jpg" alt="logo ESMIA">
        Restaurant Universitaire — ESMIA
    </a>
    <div class="nav-links">
        <a href="dashboard.php"><i class="fas fa-home"></i> Tableau de bord</a>
        <a href="mes_reservations.php"><i class="fas fa-calendar-alt"></i> Réservations</a>
        <a href="menu.php" class="active"><i class="fas fa-utensils"></i> Menu</a>
    </div>
    <div class="nav-right">
        <span class="nav-user"><i class="fas fa-user"></i> <span><?= htmlspecialchars($_SESSION['prenom'] ?? 'Étudiant') ?></span></span>
        <a href="../logout.php" class="btn-logout">Se déconnecter</a>
    </div>
</div>

<div class="container">

    <a href="dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour au tableau de bord</a>

    <div class="page-header">
        <div>
            <h1><i class="fas fa-utensils"></i> Menu du jour</h1>
            <p>Découvrez ce qui est servi aujourd'hui au restaurant universitaire.</p>
        </div>
        <div class="date-badge"><i class="fas fa-calendar-alt"></i> <?= date('d/m/Y') ?></div>
    </div>

    <div class="quota-banner">
        <i class="fas fa-exclamation-triangle"></i> Les places sont limitées — réservez tôt pour être sûr d'avoir votre repas !
    </div>

    <div class="repas-tabs">
        <a href="?repas=dejeuner" class="repas-tab <?= $repas === 'dejeuner' ? 'active' : '' ?>"><i class="fas fa-sun"></i> Déjeuner</a>
        <a href="?repas=diner"    class="repas-tab <?= $repas === 'diner'    ? 'active' : '' ?>"><i class="fas fa-moon"></i> Dîner</a>
    </div>

    <?php
    // Si des plats sont reliés en BDD à ce service précis, on les utilise ; sinon données fictives
    $afficher_bdd = !empty($plats_bdd);
    ?>

    <?php if ($afficher_bdd): ?>

        <?php if (!empty($menus)): ?>
            <?php foreach ($menus as $menu): ?>
                <p class="section-title">
                    Semaine du <?= date('d/m/Y', strtotime($menu['semaine_du'])) ?>
                    au <?= date('d/m/Y', strtotime($menu['semaine_au'])) ?>
                </p>
                <?php break; // On s'arrête au premier menu pour éviter les répétitions de titres ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="section-title">Semaine du <?= date('d/m/Y') ?></p>
        <?php endif; ?>

        <div class="semaine-card">
            <div class="semaine-header">
                <h3><i class="fas fa-list-alt"></i> Plats disponibles — <?= $repas === 'dejeuner' ? 'Déjeuner' : 'Dîner' ?></h3>
                <div style="display:flex;align-items:center;gap:10px;">
                    <?php if (!empty($menus)): ?>
                        <span style="font-size:12px;color:#888;">Publié le <?= date('d/m/Y', strtotime($menus[0]['date_publication'])) ?></span>
                    <?php endif; ?>
                    <span class="badge-publie"><i class="fas fa-check-circle"></i> Disponible</span>
                </div>
            </div>

            <div class="plats-grid">
                <?php foreach ($plats_bdd as $plat): ?>
                    <?php $icon = $type_icons[$plat['type_plat']] ?? '<i class="fas fa-utensils"></i>'; ?>
                    <div class="plat-card">
                        <div class="plat-emoji-box"><?= $icon ?></div>
                        <div class="plat-body">
                            <div class="plat-type"><?= htmlspecialchars($plat['type_plat']) ?></div>
                            <div class="plat-nom"><?= htmlspecialchars($plat['nom_plat']) ?></div>
                            <?php if (!empty($plat['descriptions'])): ?>
                                <p class="plat-desc"><?= htmlspecialchars($plat['descriptions']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="plat-footer">
                            <?php if (!empty($plat['calories'])): ?>
                                <span class="tag tag-calories"><i class="fas fa-fire"></i> <?= htmlspecialchars($plat['calories']) ?> kcal</span>
                            <?php endif; ?>
                            <?php if (!empty($plat['allergens'])): ?>
                                <span class="tag tag-allergen"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($plat['allergens']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="btn-reserver-wrap">
                <a href="nouvelle_reservation.php" class="btn-reserver-main"><i class="fas fa-calendar-plus"></i> Réservez ce repas</a>
            </div>
        </div>

    <?php else: ?>
        <p class="section-title">Menu du <?= date('d/m/Y') ?></p>
        <div class="semaine-card">
            <div class="semaine-header">
                <h3><i class="fas fa-list-alt"></i> <?= $repas === 'dejeuner' ? '<i class="fas fa-sun"></i> Déjeuner' : '<i class="fas fa-moon"></i> Dîner' ?></h3>
                <span class="badge-publie"><i class="fas fa-check-circle"></i> Disponible</span>
            </div>

            <?php $plats_affich = $plats_fictifs[$repas]; ?>
            <div class="plats-grid">
                <?php foreach ($plats_affich as $plat): ?>
                    <div class="plat-card">
                        <div class="plat-emoji-box"><?= $plat['emoji'] ?></div>
                        <div class="plat-body">
                            <div class="plat-type"><?= htmlspecialchars($plat['type']) ?></div>
                            <div class="plat-nom"><?= htmlspecialchars($plat['nom']) ?></div>
                            <p class="plat-desc"><?= htmlspecialchars($plat['desc']) ?></p>
                        </div>
                        <div class="plat-footer">
                            <?php if ($plat['calories'] > 0): ?>
                                <span class="tag tag-calories"><i class="fas fa-fire"></i> <?= $plat['calories'] ?> kcal</span>
                            <?php endif; ?>
                            <?php if (!empty($plat['allergens'])): ?>
                                <span class="tag tag-allergen"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($plat['allergens']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="btn-reserver-wrap">
                <a href="nouvelle_reservation.php" class="btn-reserver-main"><i class="fas fa-calendar-plus"></i> Réservez ce repas</a>
            </div>
        </div>
    <?php endif; ?>

</div>

<div class="footer">© <?= date('Y') ?> Restaurant Universitaire ESMIA — Tous droits réservés</div>

</body>
</html>