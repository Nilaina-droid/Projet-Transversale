<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'etudiant') {
    header("Location: ../index.php");
    exit();
}
require_once '../config/connexion.php';

$message = "";
$id_etudiant = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM etudiant WHERE id_etudiant = ?");
$stmt->execute([$id_etudiant]);
$etudiant = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom       = htmlspecialchars($_POST['nom']);
    $prenom    = htmlspecialchars($_POST['prenom']);
    $email     = htmlspecialchars($_POST['email']);
    $telephone = htmlspecialchars($_POST['telephone']);
    $filiere   = htmlspecialchars($_POST['filiere']);

    $stmt = $pdo->prepare("
        UPDATE etudiant 
        SET nom = ?, prenom = ?, email = ?, telephone = ?, filiere = ?
        WHERE id_etudiant = ?
    ");
    $stmt->execute([$nom, $prenom, $email, $telephone, $filiere, $id_etudiant]);

    $_SESSION['nom']    = $nom;
    $_SESSION['prenom'] = $prenom;

    $message = "<div class='alert-success'>✅ Profil mis à jour avec succès !</div>";

    $stmt = $pdo->prepare("SELECT * FROM etudiant WHERE id_etudiant = ?");
    $stmt->execute([$id_etudiant]);
    $etudiant = $stmt->fetch();
}

$initiales = strtoupper(substr($etudiant['prenom'], 0, 1) . substr($etudiant['nom'], 0, 1));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Style/profil.css">
    <title>Mon Profil — RU ESMIA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
    <a href="dashboard.php" class="nav-brand">
        <img src="../assets/images/esmia.jpg" alt="logo ESMIA">
        Restaurant Universitaire — ESMIA
    </a>
    <div class="nav-right">
        <span class="nav-user">👤 <span><?= htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']) ?></span></span>
        <a href="../logout.php" class="btn-logout">Se déconnecter</a>
    </div>
</div>

<!-- CONTENU -->
<div class="container">

    <a href="dashboard.php" class="back-link">⬅ Retour au tableau de bord</a>

    <?= $message ?>

    <!-- CARTE PROFIL -->
    <div class="profil-header">
        <div class="avatar"><?= $initiales ?></div>
        <div class="profil-details">
            <h3><?= htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']) ?></h3>
            <p><?= htmlspecialchars($etudiant['filiere']) ?> &nbsp;•&nbsp; <?= htmlspecialchars($etudiant['email']) ?></p>
            <div class="badges">
                <span class="badge-matricule">📋 <?= htmlspecialchars($etudiant['matricule']) ?></span>
                <span class="badge-date">🗓 Inscrit le <?= date('d/m/Y', strtotime($etudiant['date_inscription'])) ?></span>
            </div>
        </div>
    </div>

    <!-- FORMULAIRE -->
    <div class="card-form">
        <div class="card-form-header">
            <span style="font-size:18px;"></span>
            <h2>Modifier mes informations</h2>
        </div>
        <div class="card-form-body">
            <form method="POST">
                <div class="form-grid">

                    <div class="form-group full">
                        <label>Matricule <span style="font-weight:400;color:#aaa;">(non modifiable)</span></label>
                        <input type="text" value="<?= htmlspecialchars($etudiant['matricule']) ?>" readonly>
                    </div>

                    <hr class="divider">

                    <div class="form-group">
                        <label>Nom :</label>
                        <input type="text" name="nom" value="<?= htmlspecialchars($etudiant['nom']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Prénom :</label>
                        <input type="text" name="prenom" value="<?= htmlspecialchars($etudiant['prenom']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Email :</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($etudiant['email']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Téléphone :</label>
                        <input type="text" name="telephone" value="<?= htmlspecialchars($etudiant['telephone']) ?>" required>
                    </div>

                    <div class="form-group full">
                        <label>Filière :</label>
                        <select name="filiere" required>
                            <?php
                            $filieres = [
                                'Informatique' => 'Services Informatique aux Organisations',
                                'Gestion'      => 'Tronc Commun',
                                'Marketing'    => 'Communication et Marketing digital',
                                'Environnement'=> 'Management de l\'environnement et gestion de projets',
                                'Ingenierie'   => 'Ingénierie et management de projets',
                                'IAGE'         => 'Informatique appliquée à la gestion d\'entreprise',
                                'Finance'      => 'Master en Finance',
                                'Qualite'      => 'Qualité, agronomie et développement durable',
                                'Réseaux'      => 'Banques et Finances',
                            ];
                            foreach ($filieres as $val => $label):
                                $selected = ($etudiant['filiere'] === $val) ? 'selected' : '';
                            ?>
                                <option value="<?= $val ?>" <?= $selected ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>

                <button type="submit" class="btn-save"> Sauvegarder les modifications</button>

                <div class="link-mdp">
                    <a href="changer_mot_de_passe.php"> Changer mon mot de passe</a>
                </div>

            </form>
        </div>
    </div>

</div>

<div class="footer">© <?= date('Y') ?> Restaurant Universitaire ESMIA — Tous droits réservés</div>

</body>
</html>