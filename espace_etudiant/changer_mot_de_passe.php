<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'etudiant') {
    header("Location: ../index.php");
    exit();
}

require_once '../config/connexion.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ancien_mdp  = $_POST['ancien_mdp'];
    $nouveau_mdp = $_POST['nouveau_mdp'];
    $confirmation = $_POST['confirmation'];

    if ($nouveau_mdp !== $confirmation) {
        $message = "<div class='alert-danger'>❌ Les deux nouveaux mots de passe ne correspondent pas.</div>";
    } elseif (strlen($nouveau_mdp) < 6) {
        $message = "<div class='alert-danger'>❌ Le mot de passe doit contenir au moins 6 caractères.</div>";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM etudiant WHERE id_etudiant = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if ($user && password_verify($ancien_mdp, $user['mot_de_passe'])) {
            $nouveau_hash = password_hash($nouveau_mdp, PASSWORD_BCRYPT);
            $stmt2 = $pdo->prepare("UPDATE etudiant SET mot_de_passe = ? WHERE id_etudiant = ?");
            $stmt2->execute([$nouveau_hash, $_SESSION['user_id']]);
            $message = "<div class='alert-success'>✅ Mot de passe changé avec succès !</div>";
        } else {
            $message = "<div class='alert-danger'>❌ L'ancien mot de passe est incorrect.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changer mot de passe — RU ESMIA</title>
   <link rel="stylesheet" href="../Style/changermdp.css">
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
        <span class="nav-user">👤 <span><?= htmlspecialchars($_SESSION['prenom'] ?? '') ?></span></span>
        <a href="../logout.php" class="btn-logout">Se déconnecter</a>
    </div>
</div>

<!-- CONTENU -->
<div class="container">

    <a href="dashboard.php" class="back-link">⬅ Retour au tableau de bord</a>

    <div class="card">
        <div class="card-header">
            <span style="font-size:20px;"></span>
            <h2>Changer mon mot de passe</h2>
        </div>
        <div class="card-body">

            <?= $message ?>

            <form method="POST">

                <div class="form-group">
                    <label>Ancien mot de passe :</label>
                    <input type="password" name="ancien_mdp" required placeholder="Votre mot de passe actuel">
                </div>

                <hr class="divider">

                <div class="form-group">
                    <label>Nouveau mot de passe :</label>
                    <input type="password" name="nouveau_mdp" required placeholder="Min. 6 caractères">
                </div>

                <div class="form-group">
                    <label>Confirmer le nouveau mot de passe :</label>
                    <input type="password" name="confirmation" required placeholder="Répétez le nouveau mot de passe">
                </div>

                <button type="submit" class="btn-submit"> Changer le mot de passe</button>

            </form>
        </div>
    </div>

</div>

<div class="footer">© <?= date('Y') ?> Restaurant Universitaire ESMIA — Tous droits réservés</div>

</body>
</html>