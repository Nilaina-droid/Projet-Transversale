<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'etudiant') {
    header("Location: ../index.php");
    exit();
}

$nom = $_SESSION['nom'];
$prenom = $_SESSION['prenom'];
$matricule = $_SESSION['matricule'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Étudiant</title>
    <link rel="stylesheet" href="../Style/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
</head>
<body>
    <!-- NAVBAR -->
    <div class="navbar">
        <div class="nav-brand">
            <span class="icon"></span>
            Restaurant Universitaire — ESMIA
        </div>
        <div class="nav-right">
           <span class="nav-user">
    <i class="fa-solid fa-user"></i>
    <span><?= htmlspecialchars($prenom . ' ' . $nom); ?></span></span>
            <a href="../index.php" class="btn-logout">Se déconnecter</a>
        </div>
    </div>

    <!-- CONTENU -->
    <div class="container">

        <!-- WELCOME -->
        <div class="welcome-card">
            <div class="welcome-text">
                <h2>Bonjour, <?php echo htmlspecialchars($prenom); ?> </h2>
                <p>Matricule : <strong><?php echo htmlspecialchars($matricule); ?></strong> &nbsp;•&nbsp; Que souhaitez-vous faire aujourd'hui ?</p>
            </div>
            <div class="welcome-badge">Espace Étudiant</div>
        </div>

        <!-- ACTIONS -->
        <p class="section-title">Mes actions</p>
        <div class="menu-cards">

            <div class="card">
                <a href="mes_reservations.php">
                    
                    <div class="card-body">
                        <h3>Mes Réservations</h3>
                        <p>Consulter et gérer mes réservations de repas</p>
                    </div>
                </a>
            </div>

            <div class="card">
                <a href="nouvelle_reservation.php">
                    
                    <div class="card-body">
                        <h3>Nouvelle Réservation</h3>
                        <p>Réserver un repas au restaurant universitaire</p>
                    </div>
                </a>
            </div>

            <div class="card">
                <a href="mon_profil.php">
                    
                    <div class="card-body">
                        <h3>Mon Profil</h3>
                        <p>Voir et modifier mes informations personnelles</p>
                    </div>
                </a>
            </div>

            <div class="card">
                <a href="changer_mot_de_passe.php">
                    
                    <div class="card-body">
                        <h3>Changer mot de passe</h3>
                        <p>Modifier mon mot de passe de connexion</p>
                    </div>
                </a>
            </div>

           

        </div>
    </div>

    <div class="footer">© <?php echo date('Y'); ?> Restaurant Universitaire ESMIA — Tous droits réservés</div>

</body>
</html>