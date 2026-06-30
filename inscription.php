<?php
include 'config/connexion.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matricule    = htmlspecialchars($_POST['matricule']);
    $nom          = htmlspecialchars($_POST['nom']);
    $prenom       = htmlspecialchars($_POST['prenom']);
    $filiere      = htmlspecialchars($_POST['filiere']);
    $email        = htmlspecialchars($_POST['email']);
    $telephone    = htmlspecialchars($_POST['telephone']);
    $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_BCRYPT);

    try {
        $sql = "INSERT INTO etudiant (matricule, nom, prenom, filiere, email, telephone, mot_de_passe) 
                VALUES (:matricule, :nom, :prenom, :filiere, :email, :telephone, :mot_de_passe)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':matricule'    => $matricule,
            ':nom'          => $nom,
            ':prenom'       => $prenom,
            ':filiere'      => $filiere,
            ':email'        => $email,
            ':telephone'    => $telephone,
            ':mot_de_passe' => $mot_de_passe
        ]);
        $message = "<div class='alert-success'>✅ Inscription réussie ! Vous pouvez maintenant vous connecter.</div>";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $message = "<div class='alert-danger'>❌ Le matricule ou l'email existe déjà.</div>";
        } else {
            $message = "<div class='alert-danger'>❌ Erreur : " . $e->getMessage() . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — Restaurant Universitaire</title>
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        body {
            background-color: #efefef;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px 20px;
        }

        /* ── CARD ── */
        .card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.09);
            width: 100%;
            max-width: 480px;
            overflow: hidden;
        }

        /* ── HEADER ── */
        .card-header {
            background: linear-gradient(135deg, #0081c9, #005b94);
            padding: 24px 28px;
            text-align: center;
        }

        .card-header img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            border-radius: 10px;
            margin-bottom: 10px;
            background: rgba(255,255,255,0.15);
            padding: 4px;
        }

        .card-header h2 {
            color: #ffffff;
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .card-header p {
            color: rgba(255,255,255,0.75);
            font-size: 12px;
        }

        /* ── BODY ── */
        .card-body { padding: 24px 28px; }

        /* ── ALERTS ── */
        .alert-success {
            background: #e8f4fd;
            color: #005b94;
            border-left: 4px solid #0081c9;
            padding: 11px 14px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-weight: 600;
            font-size: 13px;
        }
        .alert-danger {
            background: #fdecea;
            color: #c0392b;
            border-left: 4px solid #dc3545;
            padding: 11px 14px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-weight: 600;
            font-size: 13px;
        }

        /* ── FORM GRID ── */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0 14px;
        }

        .form-group { margin-bottom: 13px; }
        .form-group.full { grid-column: 1 / -1; }

        label {
            display: block;
            font-weight: 600;
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 5px;
        }

        input, select {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 13px;
            background-color: #fafafa;
            color: #333;
            font-family: 'Segoe UI', sans-serif;
            transition: border-color 0.2s, box-shadow 0.2s;
            height: 38px;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #0081c9;
            box-shadow: 0 0 0 3px rgba(0,129,201,0.10);
            background-color: #fff;
        }

        /* ── DIVIDER ── */
        .section-divider {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 4px 0 12px;
        }

        .section-divider span {
            font-size: 11px;
            font-weight: 700;
            color: #aaa;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .section-divider::before,
        .section-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #ebebeb;
        }

        /* ── BUTTON ── */
        .btn-submit {
            width: 100%;
            padding: 11px;
            background-color: #0081c9;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.4px;
            transition: background-color 0.2s, transform 0.1s;
            margin-top: 6px;
        }
        .btn-submit:hover { background-color: #005b94; }
        .btn-submit:active { transform: scale(0.99); }

        /* ── LOGIN LINK ── */
        .login-link {
            text-align: center;
            margin-top: 16px;
            font-size: 13px;
            color: #888;
        }
        .login-link a {
            color: #0081c9;
            text-decoration: none;
            font-weight: 700;
        }
        .login-link a:hover { text-decoration: underline; }

        /* ── FOOTER ── */
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #bbb;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="card">

    <!-- HEADER -->
    <div class="card-header">
        <img src="assets/images/esmia.jpg" alt="logo ESMIA">
        <h2>Créer un compte Étudiant</h2>
        <p>Remplissez les informations ci-dessous pour vous inscrire</p>
    </div>

    <!-- BODY -->
    <div class="card-body">

        <?= $message ?>

        <form method="POST">
            <div class="form-grid">

                <!-- Identité -->
                <div class="section-divider"><span>Identité</span></div>

                <div class="form-group full">
                    <label>Matricule</label>
                    <input type="text" name="matricule" required placeholder="Ex : SE20250001">
                </div>

                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom" required>
                </div>

                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="prenom" required>
                </div>

                <div class="form-group full">
                    <label>Filière</label>
                    <select name="filiere" required>
                        <option value="">— Choisir votre filière —</option>
                        <option value="Informatique">Services Informatique aux Organisations</option>
                        <option value="Gestion">Tronc Commun</option>
                        <option value="Marketing">Communication et Marketing digital</option>
                        <option value="Environnement">Management de l'environnement et gestion de projets</option>
                        <option value="Ingenierie">Ingénierie et management de projets</option>
                        <option value="IAGE">Informatique appliquée à la gestion d'entreprise</option>
                        <option value="Finance">Master en Finance</option>
                        <option value="Qualite">Qualité, agronomie et développement durable</option>
                        <option value="Réseaux">Banques et Finances</option>
                    </select>
                </div>

                <!-- Contact -->
                <div class="section-divider"><span>Contact</span></div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="nom@etudiant.com">
                </div>

                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="text" name="telephone" required placeholder="Ex : 034 00 000 00">
                </div>

                <!-- Sécurité -->
                <div class="section-divider"><span>Sécurité</span></div>

                <div class="form-group full">
                    <label>Mot de passe</label>
                    <input type="password" name="mot_de_passe" required placeholder="Min. 6 caractères">
                </div>

            </div>

            <button type="submit" class="btn-submit"> Créer mon compte</button>

            <div class="login-link">
                Déjà un compte ? <a href="index.php">Se connecter</a>
            </div>
        </form>
    </div>
</div>

<div class="footer">© <?= date('Y') ?> Restaurant Universitaire ESMIA — Tous droits réservés</div>

</body>
</html>