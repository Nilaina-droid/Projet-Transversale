<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'gestionnaire') {
    header("Location: ../index.php");
    exit();
}
require_once '../config/connexion.php';

$stmt = $pdo->query("SELECT COUNT(*) FROM etudiant");
$nb_etudiants = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM reservation WHERE DATE(date_reservation) = CURDATE()");
$stmt->execute();
$nb_reservations = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM menu WHERE MONTH(semaine_du) = MONTH(CURDATE())");
$nb_menus = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(quantite_restante) FROM quota");
$quota_restant = $stmt->fetchColumn();

$stmt = $pdo->query("
    SELECT r.*, e.nom, e.prenom, e.matricule 
    FROM reservation r 
    JOIN etudiant e ON r.id_etudiant = e.id_etudiant 
    ORDER BY r.date_reservation DESC 
    LIMIT 5
");
$reservations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — RU ESMIA</title>
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../Style/dashboard_admin.css">
    
</head>
<body>

<button class="menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="fas fa-bars"></i></button>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img src="../assets/images/esmia.jpg" alt="logo">
        <span>Restaurant<br>Universitaire</span>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-title">Principal</div>
        <a href="dashboard_admin.php" class="sidebar-link active">
            <span class="icon"><i class="fas fa-chart-bar"></i></span> Tableau de bord
        </a>
        <a href="gestion_menus.php" class="sidebar-link">
            <span class="icon"><i class="fas fa-utensils"></i></span> Gestion des menus
        </a>
        <a href="reservations.php" class="sidebar-link">
            <span class="icon"><i class="fas fa-calendar-alt"></i></span> Réservations
        </a>

        <div class="nav-section-title">Gestion</div>
        <a href="etudiants.php" class="sidebar-link">
            <span class="icon"><i class="fas fa-user-graduate"></i></span> Étudiants
        </a>
        <a href="controle_quotas.php" class="sidebar-link">
            <span class="icon"><i class="fas fa-chart-line"></i></span> Quotas
        </a>

        <div class="nav-section-title">Système</div>
        <a href="parametres.php" class="sidebar-link">
            <span class="icon"><i class="fas fa-cog"></i></span> Paramètres
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="avatar"><?= strtoupper(substr($_SESSION['identifiant'] ?? 'A', 0, 1)) ?></div>
            <div>
                <div class="name"><?= htmlspecialchars($_SESSION['identifiant'] ?? 'Admin') ?></div>
                <div class="role">Gestionnaire</div>
            </div>
        </div>
        <a href="../logout.php" class="btn-logout-side"><i class="fas fa-door-open"></i> Déconnexion</a>
    </div>
</aside>

<div class="main-wrap">

    <div class="topbar">
        <div>
            <h2>Tableau de bord</h2>
            <div class="date-info"><i class="fas fa-calendar-day"></i> <?= date('d/m/Y') ?> — Bonjour, <?= htmlspecialchars($_SESSION['identifiant'] ?? 'Admin') ?> <i class="fas fa-hand-wave" style="color: #ffcb4c;"></i></div>
        </div>
        <a href="gestion_menus.php" class="btn-nouveau"><i class="fas fa-plus"></i> Nouveau menu</a>
    </div>

    <div class="content">

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f4fd; color:#0081c9;"><i class="fas fa-user-graduate"></i></div>
                <div>
                    <div class="stat-label">Étudiants</div>
                    <div class="stat-value" style="color:#0081c9;"><?= $nb_etudiants ?></div>
                    <div class="stat-sub">inscrits au RU</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fff3e0; color:#e65100;"><i class="fas fa-calendar-check"></i></div>
                <div>
                    <div class="stat-label">Réservations aujourd'hui</div>
                    <div class="stat-value" style="color:#e65100;"><?= $nb_reservations ?></div>
                    <div class="stat-sub">confirmées / en attente</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f8f0; color:#1a6b40;"><i class="fas fa-utensils"></i></div>
                <div>
                    <div class="stat-label">Menus ce mois</div>
                    <div class="stat-value" style="color:#1a6b40;"><?= $nb_menus ?></div>
                    <div class="stat-sub">publiés</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fdecea; color:#c0392b;"><i class="fas fa-chart-pie"></i></div>
                <div>
                    <div class="stat-label">Quota restant</div>
                    <div class="stat-value" style="color:#c0392b;"><?= $quota_restant ?? 0 ?></div>
                    <div class="stat-sub">places disponibles</div>
                </div>
            </div>
        </div>

        <p class="section-title"><i class="fas fa-bolt"></i> Actions rapides</p>
        <div class="actions-grid">
            <a href="gestion_menus.php" class="action-card">
                <div class="ac-icon" style="background:#e8f4fd; color:#0081c9;"><i class="fas fa-utensils"></i></div>
                <h5>Ajouter un menu</h5>
                <p>Publier le menu du déjeuner ou dîner pour la semaine.</p>
                <span class="ac-link">Créer un menu &rarr;</span>
            </a>
            <a href="reservations.php" class="action-card">
                <div class="ac-icon" style="background:#fff3e0; color:#e65100;"><i class="fas fa-hourglass-half"></i></div>
                <h5>Valider réservations</h5>
                <p>Confirmer ou annuler les réservations en attente.</p>
                <span class="ac-link">Voir les demandes &rarr;</span>
            </a>
            <a href="etudiants.php" class="action-card">
                <div class="ac-icon" style="background:#e8f8f0; color:#1a6b40;"><i class="fas fa-user-graduate"></i></div>
                <h5>Gérer les étudiants</h5>
                <p>Consulter la liste des étudiants inscrits au restaurant.</p>
                <span class="ac-link">Voir la liste &rarr;</span>
            </a>
            <a href="controle_quotas.php" class="action-card">
                <div class="ac-icon" style="background:#fdecea; color:#c0392b;"><i class="fas fa-chart-line"></i></div>
                <h5>Gérer les quotas</h5>
                <p>Définir le nombre de places disponibles par service.</p>
                <span class="ac-link">Configurer &rarr;</span>
            </a>
        </div>

        <p class="section-title"><i class="fas fa-history"></i> Dernières réservations</p>
        <div class="table-card">
            <div class="table-header">
                <h5>Réservations récentes</h5>
                <a href="reservations.php" class="btn-voir-tout">Voir tout</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Étudiant</th>
                        <th>Date réservation</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reservations)): ?>
                        <tr class="empty-row"><td colspan="5">Aucune réservation pour le moment</td></tr>
                    <?php else: ?>
                        <?php foreach ($reservations as $r): ?>
                        <tr>
                            <td class="td-muted"><?= $r['id_reservation'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($r['nom'] . ' ' . $r['prenom']) ?></strong><br>
                                <span class="td-small"><?= htmlspecialchars($r['matricule']) ?></span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($r['date_reservation'])) ?></td>
                            <td>
                                <?php
                                $badges = [
                                    'EN_ATTENTE' => ['badge-attente',   '<i class="fas fa-hourglass-half"></i> En attente'],
                                    'CONFIRMEE'  => ['badge-confirmee', '<i class="fas fa-check"></i> Confirmée'],
                                    'ANNULEE'    => ['badge-annulee',   '<i class="fas fa-times"></i> Annulée'],
                                    'HONOREE'    => ['badge-honoree',   '<i class="fas fa-graduation-cap"></i> Honorée'],
                                ];
                                $b = $badges[$r['statut']] ?? ['', $r['statut']];
                                ?>
                                <span class="badge <?= $b[0] ?>"><?= $b[1] ?></span>
                            </td>
                            <td>
                                <?php if ($r['statut'] === 'EN_ATTENTE'): ?>
                                    <a href="reservations.php?action=confirmer&id=<?= $r['id_reservation'] ?>" class="btn-action btn-confirmer"><i class="fas fa-check"></i> Confirmer</a>
                                    <a href="reservations.php?action=annuler&id=<?= $r['id_reservation'] ?>"  class="btn-action btn-annuler" style="margin-left:4px;"><i class="fas fa-times"></i> Annuler</a>
                                <?php elseif ($r['statut'] === 'CONFIRMEE'): ?>
                                    <a href="reservations.php?action=honorer&id=<?= $r['id_reservation'] ?>"  class="btn-action btn-honorer"><i class="fas fa-graduation-cap"></i> Honorer</a>
                                <?php else: ?>
                                    <span class="td-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
    document.addEventListener('click', function(e) {
        const sidebar = document.getElementById('sidebar');
        const toggle  = document.querySelector('.menu-toggle');
        if (window.innerWidth <= 900 && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
            sidebar.classList.remove('open');
        }
    });
</script>
</body>
</html>