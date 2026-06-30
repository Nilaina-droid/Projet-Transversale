<?php
require_once 'config/connexion.php';

$message = "";
$etape = 1;
$matricule_valide = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['etape1'])) {
    $matricule = trim($_POST['matricule']);
    $email     = trim($_POST['email']);

    $stmt = $pdo->prepare("SELECT * FROM etudiant WHERE matricule = ? AND email = ?");
    $stmt->execute([$matricule, $email]);
    $user = $stmt->fetch();

    if ($user) {
        $etape = 2;
        $matricule_valide = $matricule;
        $message = "<div class='alert-success'>✅ Identité vérifiée ! Choisissez un nouveau mot de passe.</div>";
    } else {
        $message = "<div class='alert-danger'>❌ Matricule ou email incorrect.</div>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['etape2'])) {
    $matricule    = $_POST['matricule_cache'];
    $nouveau_mdp  = $_POST['nouveau_mdp'];
    $confirmation = $_POST['confirmation'];

    if ($nouveau_mdp !== $confirmation) {
        $etape = 2;
        $matricule_valide = $matricule;
        $message = "<div class='alert-danger'>❌ Les mots de passe ne correspondent pas.</div>";
    } elseif (strlen($nouveau_mdp) < 6) {
        $etape = 2;
        $matricule_valide = $matricule;
        $message = "<div class='alert-danger'>❌ Minimum 6 caractères requis.</div>";
    } else {
        $nouveau_hash = password_hash($nouveau_mdp, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE etudiant SET mot_de_passe = ? WHERE matricule = ?");
        $stmt->execute([$nouveau_hash, $matricule]);
        $etape = 3;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié — RU ESMIA</title>
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
            max-width: 420px;
            overflow: hidden;
        }

        /* ── HEADER ── */
        .card-header {
            background: linear-gradient(135deg, #0081c9, #005b94);
            padding: 22px 28px;
            text-align: center;
        }

        .card-header img {
            width: 56px;
            height: 56px;
            object-fit: contain;
            border-radius: 10px;
            margin-bottom: 10px;
            padding: 4px;
            background: rgba(255,255,255,0.15);
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

        /* ── STEPPER ── */
        .stepper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-bottom: 22px;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }

        .step-circle {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            background: #e8e8e8;
            color: #aaa;
            transition: all 0.3s;
        }

        .step-circle.active {
            background: #0081c9;
            color: #ffffff;
            box-shadow: 0 3px 10px rgba(0,129,201,0.35);
        }

        .step-circle.done {
            background: #28a745;
            color: #ffffff;
        }

        .step-label {
            font-size: 10px;
            font-weight: 600;
            color: #aaa;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .step-label.active { color: #0081c9; }
        .step-label.done   { color: #28a745; }

        .step-line {
            flex: 1;
            height: 2px;
            background: #e8e8e8;
            margin: 0 8px;
            margin-bottom: 18px;
            max-width: 50px;
        }

        .step-line.done { background: #28a745; }

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

        /* ── FORM ── */
        .form-group { margin-bottom: 14px; }

        label {
            display: block;
            font-weight: 600;
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 5px;
        }

        input {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 13px;
            background-color: #fafafa;
            color: #333;
            font-family: 'Segoe UI', sans-serif;
            height: 38px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input:focus {
            outline: none;
            border-color: #0081c9;
            box-shadow: 0 0 0 3px rgba(0,129,201,0.10);
            background-color: #fff;
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
            margin-top: 4px;
        }
        .btn-submit:hover { background-color: #005b94; }
        .btn-submit:active { transform: scale(0.99); }

        /* ── BACK LINK ── */
        .back-link {
            text-align: center;
            margin-top: 14px;
            font-size: 13px;
        }
        .back-link a {
            color: #0081c9;
            text-decoration: none;
            font-weight: 600;
        }
        .back-link a:hover { text-decoration: underline; }

        /* ── SUCCESS STATE ── */
        .success-state {
            text-align: center;
            padding: 20px 0 10px;
        }
        .success-icon {
            font-size: 52px;
            margin-bottom: 12px;
        }
        .success-state h3 {
            font-size: 17px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 6px;
        }
        .success-state p {
            font-size: 13px;
            color: #888;
            margin-bottom: 20px;
        }
        .btn-login {
            display: inline-block;
            background: #0081c9;
            color: #fff;
            padding: 10px 28px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn-login:hover { background: #005b94; }

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
        <h2> Mot de passe oublié</h2>
        <p>Réinitialisez votre mot de passe en 2 étapes</p>
    </div>

    <div class="card-body">

        <!-- STEPPER -->
        <div class="stepper">
            <div class="step">
                <div class="step-circle <?= $etape === 1 ? 'active' : 'done' ?>">
                    <?= $etape > 1 ? '✓' : '1' ?>
                </div>
                <span class="step-label <?= $etape === 1 ? 'active' : 'done' ?>">Identité</span>
            </div>
            <div class="step-line <?= $etape > 1 ? 'done' : '' ?>"></div>
            <div class="step">
                <div class="step-circle <?= $etape === 2 ? 'active' : ($etape > 2 ? 'done' : '') ?>">
                    <?= $etape > 2 ? '✓' : '2' ?>
                </div>
                <span class="step-label <?= $etape === 2 ? 'active' : ($etape > 2 ? 'done' : '') ?>">Mot de passe</span>
            </div>
            <div class="step-line <?= $etape > 2 ? 'done' : '' ?>"></div>
            <div class="step">
                <div class="step-circle <?= $etape === 3 ? 'done' : '' ?>">
                    <?= $etape === 3 ? '✓' : '3' ?>
                </div>
                <span class="step-label <?= $etape === 3 ? 'done' : '' ?>">Terminé</span>
            </div>
        </div>

        <?= $message ?>

        <!-- ÉTAPE 1 -->
        <?php if ($etape === 1): ?>
        <form method="POST">
            <input type="hidden" name="etape1" value="1">

            <div class="form-group">
                <label>Votre matricule</label>
                <input type="text" name="matricule" required placeholder="Ex : SE20250001">
            </div>

            <div class="form-group">
                <label>Votre email</label>
                <input type="email" name="email" required placeholder="nom@etudiant.com">
            </div>

            <button type="submit" class="btn-submit"> Vérifier mon identité</button>

            <div class="back-link">
                <a href="index.php">⬅ Retour à la connexion</a>
            </div>
        </form>

        <!-- ÉTAPE 2 -->
        <?php elseif ($etape === 2): ?>
        <form method="POST">
            <input type="hidden" name="etape2" value="1">
            <input type="hidden" name="matricule_cache" value="<?= htmlspecialchars($matricule_valide) ?>">

            <div class="form-group">
                <label>Nouveau mot de passe</label>
                <input type="password" name="nouveau_mdp" required placeholder="Min. 6 caractères">
            </div>

            <div class="form-group">
                <label>Confirmer le mot de passe</label>
                <input type="password" name="confirmation" required placeholder="Répétez le mot de passe">
            </div>

            <button type="submit" class="btn-submit"> Changer le mot de passe</button>
        </form>

        <!-- ÉTAPE 3 : Succès -->
        <?php elseif ($etape === 3): ?>
        <div class="success-state">
            <div class="success-icon"></div>
            <h3>Mot de passe réinitialisé !</h3>
            <p>Votre mot de passe a bien été mis à jour.<br>Vous pouvez maintenant vous connecter.</p>
            <a href="index.php" class="btn-login">➡ Se connecter</a>
        </div>
        <?php endif; ?>

    </div>
</div>
</body>
</html>