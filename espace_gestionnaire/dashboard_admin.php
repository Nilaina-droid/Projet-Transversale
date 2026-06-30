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
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        :root { --sidebar-w: 240px; }

        body { background-color: #efefef; min-height: 100vh; }

        /* ── SIDEBAR ── */
        .sidebar {
            position: fixed; top: 0; left: 0;
            width: var(--sidebar-w); height: 100vh;
            background: linear-gradient(180deg, #0081c9 0%, #005b94 100%);
            display: flex; flex-direction: column;
            z-index: 200; transition: transform .3s;
            box-shadow: 4px 0 20px rgba(0,0,0,0.12);
        }

        .sidebar-brand {
            padding: 20px 18px;
            border-bottom: 1px solid rgba(255,255,255,0.12);
            display: flex; align-items: center; gap: 10px;
        }

        .sidebar-brand img {
            width: 38px; height: 38px;
            object-fit: contain; border-radius: 8px;
            background: rgba(255,255,255,0.15); padding: 3px;
        }

        .sidebar-brand span {
            font-size: 13px; font-weight: 700;
            color: #fff; line-height: 1.3;
        }

        .sidebar-nav { flex: 1; padding: 16px 10px; overflow-y: auto; }

        .nav-section-title {
            font-size: 10px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1px;
            color: rgba(255,255,255,0.4);
            padding: 10px 10px 5px; margin-top: 6px;
        }

        .sidebar-link {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 12px; border-radius: 8px;
            color: rgba(255,255,255,0.75); text-decoration: none;
            font-size: 13px; font-weight: 500;
            transition: background .18s, color .18s;
            margin-bottom: 2px;
        }

        .sidebar-link .icon { font-size: 14px; width: 20px; text-align: center; }
        .sidebar-link:hover  { background: rgba(255,255,255,0.12); color: #fff; }
        .sidebar-link.active { background: rgba(255,255,255,0.18); color: #fff; font-weight: 700; }

        .sidebar-footer {
            padding: 12px 10px;
            border-top: 1px solid rgba(255,255,255,0.12);
        }

        .sidebar-user {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 8px;
            background: rgba(255,255,255,0.1); margin-bottom: 8px;
        }

        .sidebar-user .avatar {
            width: 34px; height: 34px;
            background: rgba(255,255,255,0.25); border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 700; color: #fff; flex-shrink: 0;
        }

        .sidebar-user .name { font-size: 13px; font-weight: 600; color: #fff; }
        .sidebar-user .role { font-size: 11px; color: rgba(255,255,255,0.5); }

        .btn-logout-side {
            display: flex; align-items: center; gap: 8px;
            width: 100%; padding: 8px 12px; border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.2);
            background: transparent; color: rgba(255,255,255,0.7);
            font-size: 13px; text-decoration: none;
            transition: background .18s, color .18s;
        }
        .btn-logout-side:hover { background: rgba(220,53,69,0.2); color: #ff8a80; border-color: #ff8a80; }

        /* ── MAIN ── */
        .main-wrap { margin-left: var(--sidebar-w); min-height: 100vh; display: flex; flex-direction: column; }

        /* ── TOPBAR ── */
        .topbar {
            background: #fff; border-bottom: 1px solid #e8e8e8;
            padding: 14px 28px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .topbar h2 { font-size: 17px; font-weight: 700; color: #1a1a2e; margin: 0; }
        .topbar .date-info { font-size: 12px; color: #999; margin-top: 2px; }

        .btn-nouveau {
            display: inline-flex; align-items: center; gap: 6px;
            background: #0081c9; color: #fff;
            padding: 9px 18px; border-radius: 8px;
            font-size: 13px; font-weight: 700;
            text-decoration: none; transition: background 0.2s;
        }
        .btn-nouveau:hover { background: #005b94; }

        /* ── CONTENT ── */
        .content { padding: 28px; flex: 1; }

        /* ── STATS ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: #fff; border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.06);
            padding: 18px 20px;
            display: flex; align-items: center; gap: 14px;
            transition: transform .2s, box-shadow .2s;
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,129,201,0.12); }

        .stat-icon {
            width: 50px; height: 50px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; flex-shrink: 0;
        }

        .stat-label { font-size: 12px; color: #999; margin-bottom: 3px; }
        .stat-value { font-size: 26px; font-weight: 700; color: #1a1a2e; line-height: 1; }
        .stat-sub   { font-size: 11px; color: #aaa; margin-top: 3px; }

        /* ── SECTION TITLE ── */
        .section-title {
            font-size: 13px; font-weight: 700; color: #999;
            text-transform: uppercase; letter-spacing: 1px;
            margin-bottom: 14px;
        }

        /* ── ACTION CARDS ── */
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 28px;
        }

        .action-card {
            background: #fff; border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.06);
            padding: 18px;
            display: flex; flex-direction: column; gap: 10px;
            text-decoration: none; color: inherit;
            border-top: 3px solid #0081c9;
            transition: transform .2s, box-shadow .2s;
        }
        .action-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,129,201,0.12); }

        .ac-icon {
            width: 44px; height: 44px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
        }

        .action-card h5 { font-size: 14px; font-weight: 700; color: #1a1a2e; margin: 0; }
        .action-card p  { font-size: 12px; color: #888; margin: 0; flex: 1; line-height: 1.5; }
        .ac-link { font-size: 12px; font-weight: 700; color: #0081c9; }

        /* ── TABLE CARD ── */
        .table-card {
            background: #fff; border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.06);
            overflow: hidden;
        }

        .table-header {
            padding: 16px 20px; border-bottom: 1px solid #f0f0f0;
            display: flex; align-items: center; justify-content: space-between;
        }
        .table-header h5 { font-size: 14px; font-weight: 700; color: #1a1a2e; margin: 0; }

        .btn-voir-tout {
            font-size: 12px; font-weight: 600; color: #0081c9;
            text-decoration: none; padding: 5px 12px;
            border-radius: 6px; border: 1px solid #d9d9d9;
            transition: background .18s;
        }
        .btn-voir-tout:hover { background: #f0f7ff; border-color: #0081c9; }

        table { width: 100%; border-collapse: collapse; }

        thead { background: linear-gradient(135deg, #0081c9, #005b94); }
        thead th {
            color: #fff; font-size: 12px; font-weight: 600;
            padding: 12px 16px; text-align: left; border: none;
        }

        tbody td {
            padding: 12px 16px; font-size: 13px;
            color: #333; border-bottom: 1px solid #f5f5f5;
            vertical-align: middle;
        }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #f8fbff; }

        .td-muted { color: #bbb; font-size: 12px; }
        .td-small { font-size: 11px; color: #aaa; }

        /* ── BADGES ── */
        .badge {
            display: inline-block; padding: 4px 10px;
            border-radius: 999px; font-size: 11px; font-weight: 600;
        }
        .badge-attente  { background: #fff3e0; color: #e65100; }
        .badge-confirmee { background: #e8f4fd; color: #005b94; }
        .badge-annulee  { background: #fdecea; color: #c0392b; }
        .badge-honoree  { background: #e8f8f0; color: #1a6b40; }

        /* ── ACTION BTNS ── */
        .btn-action {
            display: inline-block; padding: 4px 10px;
            border-radius: 6px; font-size: 11px; font-weight: 600;
            text-decoration: none; transition: opacity .2s;
        }
        .btn-action:hover { opacity: 0.8; }
        .btn-confirmer { background: #e8f4fd; color: #005b94; }
        .btn-annuler   { background: #fdecea; color: #c0392b; }
        .btn-honorer   { background: #e8f8f0; color: #1a6b40; }

        /* ── EMPTY ── */
        .empty-row td { text-align: center; color: #bbb; padding: 40px; font-size: 13px; }

        /* ── FOOTER ── */
        footer { text-align: center; padding: 20px; font-size: 12px; color: #bbb; border-top: 1px solid #ebebeb; }

        /* ── TOGGLE MOBILE ── */
        .menu-toggle {
            display: none; position: fixed; top: 14px; left: 14px;
            z-index: 300; background: #0081c9;
            border: none; border-radius: 8px; padding: 8px 11px;
            color: #fff; font-size: 18px; cursor: pointer;
        }

        @media (max-width: 900px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-wrap { margin-left: 0; }
            .menu-toggle { display: block; }
            .content { padding: 16px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .actions-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
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

    <footer>© <?= date('Y') ?> Restaurant Universitaire ESMIA — Tous droits réservés</footer>
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