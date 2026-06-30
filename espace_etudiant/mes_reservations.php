<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'etudiant') {
    header("Location: ../index.php");
    exit();
}
require_once '../config/connexion.php';

$stmt = $pdo->prepare("
    SELECT * FROM reservation 
    WHERE id_etudiant = ? 
    ORDER BY date_reservation DESC
");
$stmt->execute([$_SESSION['user_id']]);
$reservations = $stmt->fetchAll();

$total     = count($reservations);
$confirmee = count(array_filter($reservations, fn($r) => $r['statut'] === 'CONFIRMEE'));
$attente   = count(array_filter($reservations, fn($r) => $r['statut'] === 'EN_ATTENTE'));
$honoree   = count(array_filter($reservations, fn($r) => $r['statut'] === 'HONOREE'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes réservations — RU ESMIA</title>
    <link rel="stylesheet" href="../Style/mes_reservations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">

</head>
<body>

<div class="navbar">
    <a href="dashboard.php" class="nav-brand">
        <img src="../assets/images/esmia.jpg" alt="logo ESMIA">
        Restaurant Universitaire — ESMIA
    </a>
    <div class="nav-links">
        <a href="dashboard.php"><i class="fa-solid fa-house"></i> Tableau de bord</a>
        <a href="mes_reservations.php" class="active"><i class="fa-solid fa-calendar-days"></i> Réservations</a>
        <a href="menu.php"><i class="fa-solid fa-utensils"></i> Menu</a>
    </div>
    <div class="nav-right">
        <span class="nav-user"><i class="fa-solid fa-user"></i> <span><?= htmlspecialchars($_SESSION['prenom'] ?? 'Étudiant') ?></span></span>
        <a href="../logout.php" class="btn-logout">Se déconnecter</a>
    </div>
</div>

<div class="container">

    <a href="dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Retour au tableau de bord</a>

    <div class="page-header">
        <h1><i class="fa-solid fa-calendar-days"></i> Mes réservations</h1>
        <p>Consultez et suivez l'état de toutes vos réservations au restaurant.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:#f0f0f0; color:#555;"><i class="fa-solid fa-clipboard-list"></i></div>
            <div>
                <div class="stat-label">Total</div>
                <div class="stat-value"><?= $total ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#e8f4fd; color:#0081c9;"><i class="fa-solid fa-circle-check"></i></div>
            <div>
                <div class="stat-label">Confirmées</div>
                <div class="stat-value" style="color:#0081c9;"><?= $confirmee ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff3e0; color:#e65100;"><i class="fa-solid fa-hourglass-half"></i></div>
            <div>
                <div class="stat-label">En attente</div>
                <div class="stat-value" style="color:#e65100;"><?= $attente ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#e8f8f0; color:#1a6b40;"><i class="fa-solid fa-graduation-cap"></i></div>
            <div>
                <div class="stat-label">Honorées</div>
                <div class="stat-value" style="color:#1a6b40;"><?= $honoree ?></div>
            </div>
        </div>
    </div>

    <p class="section-title">Historique des réservations</p>
    <div class="table-card">
        <?php if (empty($reservations)): ?>
            <div class="empty-state">
                <div class="empty-icon" style="color:#bbb;"><i class="fa-solid fa-box-open"></i></div>
                <p>Vous n'avez aucune réservation pour le moment.</p>
                <a href="nouvelle_reservation.php" class="btn-new"><i class="fa-solid fa-plus"></i> Faire une réservation</a>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th><i class="fa-solid fa-calendar"></i> Date</th>
                        <th><i class="fa-solid fa-clock"></i> Heure</th>
                        <th><i class="fa-solid fa-circle-info"></i> Statut</th>
                        <th><i class="fa-solid fa-comment-dots"></i> Commentaire</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $i => $r): ?>
                    <tr>
                        <td class="num"><?= $i + 1 ?></td>
                        <td><?= date('d/m/Y', strtotime($r['date_reservation'])) ?></td>
                        <td><?= date('H:i', strtotime($r['date_reservation'])) ?></td>
                        <td>
                            <?php
                            $badges = [
                                'EN_ATTENTE' => ['class' => 'badge-attente',   'label' => '⏳ En attente',  'icon' => 'fa-solid fa-hourglass-half'],
                                'CONFIRMEE'  => ['class' => 'badge-confirmee', 'label' => '✅ Confirmée', 'icon' => 'fa-solid fa-circle-check'],
                                'ANNULEE'    => ['class' => 'badge-annulee',   'label' => '❌ Annulée',   'icon' => 'fa-solid fa-circle-xmark'],
                                'HONOREE'    => ['class' => 'badge-honoree',   'label' => '🎓 Honorée',   'icon' => 'fa-solid fa-graduation-cap'],
                            ];
                            $b = $badges[$r['statut']] ?? ['class' => '', 'label' => $r['statut'], 'icon' => 'fa-solid fa-question'];
                            
                            // On retire l'émoji du label d'origine pour mettre l'icône Font Awesome à la place
                            $clean_label = str_replace(['⏳ ', '✅ ', '❌ ', '🎓 '], '', $b['label']);
                            ?>
                            <span class="badge <?= $b['class'] ?>">
                                <i class="<?= $b['icon'] ?>"></i> <?= $clean_label ?>
                            </span>
                        </td>
                        <td class="comment">
                            <?= !empty($r['commentaire']) ? htmlspecialchars($r['commentaire']) : '—' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div style="margin-top: 20px; background: linear-gradient(135deg, #0081c9, #005b94); border-radius: 14px; padding: 20px 28px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <p style="color:#fff; font-weight:700; font-size:15px; margin-bottom:4px;"><i class="fa-solid fa-utensils"></i> Voir le menu de la semaine</p>
            <p style="color:rgba(255,255,255,0.75); font-size:13px;">Consultez les plats disponibles avant de faire votre réservation.</p>
        </div>
        <a href="menu.php" style="background:#fff; color:#0081c9; padding:10px 20px; border-radius:8px; font-size:13px; font-weight:700; text-decoration:none; white-space:nowrap;">Voir le menu →</a>
    </div>

</div>

<div class="footer">© <?php echo date('Y'); ?> Restaurant Universitaire ESMIA — Tous droits réservés</div>

</body>
</html>