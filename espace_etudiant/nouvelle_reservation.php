<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'etudiant') {
    header("Location: ../index.php");
    exit();
}
require_once '../config/connexion.php';

$message = "";
$id_etudiant = $_SESSION['user_id'];
$prenom = $_SESSION['prenom'];
$nom = $_SESSION['nom'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_reservation = $_POST['date_reservation'];
    $commentaire = htmlspecialchars($_POST['commentaire'] ?? '');

    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM reservation 
        WHERE id_etudiant = ? AND DATE(date_reservation) = ?
    ");
    $stmt->execute([$id_etudiant, $date_reservation]);
    $existe = $stmt->fetchColumn();

    if ($existe > 0) {
        $message = "<div class='alert-danger'><i class='fas fa-times-circle'></i> Vous avez déjà une réservation ce jour !</div>";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO reservation (date_reservation, statut, commentaire, id_etudiant) 
            VALUES (?, 'EN_ATTENTE', ?, ?)
        ");
        $stmt->execute([$date_reservation, $commentaire, $id_etudiant]);
        $message = "<div class='alert-success'><i class='fas fa-check-circle'></i> Réservation effectuée avec succès ! En attente de confirmation.</div>";
    }
}

$menus = $pdo->query("SELECT * FROM menu WHERE statut = 'PUBLIE' ORDER BY semaine_du ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Réservation — RU ESMIA</title>
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../Style/nouvelle_reservation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    
</head>
<body>

<div class="navbar">
    <div class="nav-brand">
        <img src="../assets/images/esmia.jpg" alt="logo ESMIA">
        Restaurant Universitaire — ESMIA
    </div>
    <div class="nav-right">
        <span class="nav-user"><i class="fas fa-user"></i> <span><?php echo htmlspecialchars($prenom . ' ' . $nom); ?></span></span>
        <a href="../logout.php" class="btn-logout">Se déconnecter</a>
    </div>
</div>

<div class="container">

    <a href="dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour au tableau de bord</a>

    <?= $message ?>

    <div class="card-form">
        <div class="card-form-header">
            <span class="header-icon"></span>
            <h2>Nouvelle Réservation</h2>
        </div>
        <div class="card-form-body">
            <form method="POST">
                <label>Date souhaitée :</label>
                <input type="date" name="date_reservation"
                       min="<?= date('Y-m-d') ?>" required>

                <label>Commentaire <span style="font-weight:400; color:#aaa;">(optionnel)</span> :</label>
                <textarea name="commentaire"
                          placeholder="Ex : Repas sans gluten, allergie aux noix..."></textarea>

                <button type="submit" class="btn-reserver">
                     Confirmer ma réservation
                </button>
            </form>
        </div>
    </div>

    <p class="section-title"><i class="fas fa-utensils"></i> Menus disponibles</p>

    <?php if (!empty($menus)): ?>
        <?php foreach ($menus as $m): ?>
            <div class="menu-card">
                <div class="menu-card-icon"></div>
                <div class="menu-card-body">
                    <h6>Semaine du <?= date('d/m/Y', strtotime($m['semaine_du'])) ?>
                        au <?= date('d/m/Y', strtotime($m['semaine_au'])) ?></h6>
                    <p>Publié le <?= date('d/m/Y', strtotime($m['date_publication'])) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="no-menu">
             Aucun menu publié pour le moment.
        </div>
    <?php endif; ?>

</div>
</body>
</html>