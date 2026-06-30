<?php
session_start();
require_once 'config/connexion.php';

$erreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? '';
    $login = trim($_POST['login'] ?? '');
    $mdp = $_POST['mdp'] ?? '';

    if (!empty($login) && !empty($mdp)) {

        if ($role === 'etudiant') {
            $stmt = $pdo->prepare("SELECT * FROM etudiant WHERE matricule = ?");
            $stmt->execute([$login]);
            $user = $stmt->fetch();

            if ($user && password_verify($mdp, $user['mot_de_passe'])) {
                $_SESSION['user_id'] = $user['id_etudiant'];
                $_SESSION['matricule'] = $user['matricule'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['prenom'] = $user['prenom'];
                $_SESSION['role'] = 'etudiant';
                header("Location: espace_etudiant/dashboard.php");
                exit();
            } else {
                $erreur = "Matricule ou mot de passe incorrect.";
            }

        } elseif ($role === 'gestionnaire') {
            $stmt = $pdo->prepare("SELECT * FROM gestionnaire WHERE identifiant = ?");
            $stmt->execute([$login]);
            $user = $stmt->fetch();

            if ($user && password_verify($mdp, $user['mot_de_passe'])) {
                $_SESSION['user_id'] = $user['id_gestionnaire'];
                $_SESSION['identifiant'] = $user['identifiant'];
                $_SESSION['role'] = 'gestionnaire';
                header("Location: espace_gestionnaire/dashboard_admin.php");
                exit();
            } else {
                $erreur = "Identifiant ou mot de passe incorrect.";
            }

        } else {
            $erreur = "Veuillez sélectionner un profil valide.";
        }

    } else {
        $erreur = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Gestion RU</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <style>
        .signup-link {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }
        .signup-link a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
        .signup-link a:hover {
            text-decoration: underline;
        }
        .forgot-link {
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
        }
        .forgot-link a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
        .forgot-link a:hover {
            color: #1a4731;
            text-decoration: underline;
        }

    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header" style="margin-bottom:20px;">
            <img src="assets/images/esmia.jpg" alt="logo ESMIA" class="logo-login" style="width:90px; height:90px; margin-bottom:12px;">
            <h2 style="font-size:18px;">Restaurant de l'université (RU)</h2>
        </div>

        <?php if(!empty($erreur)): ?>
            <div class="alert-error"><?php echo $erreur; ?></div>
        <?php endif; ?>

        <p style="text-align:center; font-size:13px; color:#666; margin-bottom:14px;">
             Bienvenue sur le Restaurant Universitaire — connectez-vous pour accéder à votre espace.
        </p>

        <form action="index.php" method="POST">
            <div class="form-group" style="margin-bottom:12px;">
                <label style="font-size:13px; margin-bottom:6px; display:block;">Je suis :</label>
                <div class="radio-group" style="padding:10px 14px;">
                    <input type="radio" name="role" value="etudiant" id="role_etudiant" checked>
                    <label for="role_etudiant" style="font-size:14px;">Etudiant</label>

                    <input type="radio" name="role" value="gestionnaire" id="role_gest">
                    <label for="role_gest" style="font-size:14px;">Administration</label>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:12px;">
                <label for="login" style="font-size:13px; margin-bottom:5px; display:block;">Identifiant ou Matricule :</label>
                <input type="text" name="login" id="login" required style="padding:10px 12px; font-size:14px;">
            </div>

            <div class="form-group" style="margin-bottom:12px;">
                <label for="mdp" style="font-size:13px; margin-bottom:5px; display:block;">Mot de passe :</label>
                <input type="password" name="mdp" id="mdp" required style="padding:10px 12px; font-size:14px;">
            </div>

            <button type="submit" class="btn-login" style="padding:12px; font-size:15px; margin-top:8px;">Se connecter</button>

            <div class="signup-link" style="margin-top:12px;">
                Pas encore de compte ? <a href="inscription.php">Créer un compte étudiant</a>
            </div>

            <div class="forgot-link" style="margin-top:8px;">
                <a href="mot_de_passe_oublie.php">Mot de passe oublié ?</a>
            </div>

        </form>
    </div>
</body>
</html>