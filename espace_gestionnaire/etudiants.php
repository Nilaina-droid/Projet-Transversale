<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'gestionnaire') {
    header("Location: ../index.php");
    exit();
}
require_once '../config/connexion.php';

$message = "";

// Suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'supprimer') {
        $id = $_POST['id_etudiant'];
        $pdo->prepare("DELETE FROM reservation WHERE id_etudiant = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM etudiant WHERE id_etudiant = ?")->execute([$id]);
        $message = "<div class='alert-danger'>🗑️ Étudiant supprimé.</div>";
    }
}

// Recherche
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT * FROM etudiant 
        WHERE nom LIKE ? OR prenom LIKE ? OR matricule LIKE ? OR email LIKE ?
        ORDER BY date_inscription DESC
    ");
    $like = "%$search%";
    $stmt->execute([$like, $like, $like, $like]);
    $etudiants = $stmt->fetchAll();
} else {
    $etudiants = $pdo->query("SELECT * FROM etudiant ORDER BY date_inscription DESC")->fetchAll();
}

// Stats
$total       = $pdo->query("SELECT COUNT(*) FROM etudiant")->fetchColumn();
$par_filiere = $pdo->query("SELECT filiere, COUNT(*) as nb FROM etudiant GROUP BY filiere ORDER BY nb DESC")->fetchAll();
$ce_mois     = $pdo->query("SELECT COUNT(*) FROM etudiant WHERE MONTH(date_inscription) = MONTH(CURDATE()) AND YEAR(date_inscription) = YEAR(CURDATE())")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Étudiants — RU ESMIA</title>
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        :root { --sidebar-w: 240px; }
        body { background-color: #efefef; min-height: 100vh; }

        /* ── SIDEBAR ── */
        .sidebar { position: fixed; top: 0; left: 0; width: var(--sidebar-w); height: 100vh; background: linear-gradient(180deg, #0081c9 0%, #005b94 100%); display: flex; flex-direction: column; z-index: 200; box-shadow: 4px 0 20px rgba(0,0,0,0.12); transition: transform .3s; }
        .sidebar-brand { padding: 20px 18px; border-bottom: 1px solid rgba(255,255,255,0.12); display: flex; align-items: center; gap: 10px; }
        .sidebar-brand img { width: 38px; height: 38px; object-fit: contain; border-radius: 8px; padding: 3px; background: rgba(255,255,255,0.15); }
        .sidebar-brand span { font-size: 13px; font-weight: 700; color: #fff; line-height: 1.3; }
        .sidebar-nav { flex: 1; padding: 16px 10px; overflow-y: auto; }
        .nav-section-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: rgba(255,255,255,0.4); padding: 10px 10px 5px; margin-top: 6px; }
        .sidebar-link { display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: 8px; color: rgba(255,255,255,0.75); text-decoration: none; font-size: 13px; font-weight: 500; transition: background .18s, color .18s; margin-bottom: 2px; }
        .sidebar-link .icon { font-size: 16px; width: 20px; text-align: center; }
        .sidebar-link:hover  { background: rgba(255,255,255,0.12); color: #fff; }
        .sidebar-link.active { background: rgba(255,255,255,0.18); color: #fff; font-weight: 700; }
        .sidebar-footer { padding: 12px 10px; border-top: 1px solid rgba(255,255,255,0.12); }
        .sidebar-user { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px; background: rgba(255,255,255,0.1); margin-bottom: 8px; }
        .sidebar-user .avatar { width: 34px; height: 34px; background: rgba(255,255,255,0.25); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; color: #fff; flex-shrink: 0; }
        .sidebar-user .name { font-size: 13px; font-weight: 600; color: #fff; }
        .sidebar-user .role { font-size: 11px; color: rgba(255,255,255,0.5); }
        .btn-logout-side { display: flex; align-items: center; gap: 8px; width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: transparent; color: rgba(255,255,255,0.7); font-size: 13px; text-decoration: none; transition: background .18s; }
        .btn-logout-side:hover { background: rgba(220,53,69,0.2); color: #ff8a80; border-color: #ff8a80; }

        /* ── MAIN ── */
        .main-wrap { margin-left: var(--sidebar-w); min-height: 100vh; display: flex; flex-direction: column; }
        .topbar { background: #fff; border-bottom: 1px solid #e8e8e8; padding: 14px 28px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .topbar h2 { font-size: 17px; font-weight: 700; color: #1a1a2e; margin: 0; }
        .topbar-count { font-size: 12px; color: #aaa; background: #f0f0f0; padding: 5px 12px; border-radius: 20px; font-weight: 600; }
        .content { padding: 28px; flex: 1; }

        /* ── ALERTS ── */
        .alert-success { background: #e8f4fd; color: #005b94; border-left: 4px solid #0081c9; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; font-size: 13px; }
        .alert-danger  { background: #fdecea; color: #c0392b; border-left: 4px solid #dc3545; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; font-size: 13px; }

        /* ── STATS ── */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 24px; }
        .stat-card { background: #fff; border-radius: 12px; box-shadow: 0 3px 8px rgba(0,0,0,0.06); padding: 18px 20px; display: flex; align-items: center; gap: 14px; }
        .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
        .stat-label { font-size: 12px; color: #999; margin-bottom: 3px; }
        .stat-value { font-size: 26px; font-weight: 700; line-height: 1; }
        .stat-sub   { font-size: 11px; color: #aaa; margin-top: 3px; }

        /* ── LAYOUT ── */
        .layout { display: grid; grid-template-columns: 1fr 280px; gap: 20px; align-items: start; }

        /* ── SECTION TITLE ── */
        .section-title { font-size: 13px; font-weight: 700; color: #999; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px; }

        /* ── SEARCH ── */
        .search-bar { display: flex; gap: 8px; margin-bottom: 16px; }
        .search-input { flex: 1; padding: 9px 14px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 13px; background: #fafafa; color: #333; transition: border-color 0.2s, box-shadow 0.2s; }
        .search-input:focus { outline: none; border-color: #0081c9; box-shadow: 0 0 0 3px rgba(0,129,201,0.10); background: #fff; }
        .btn-search { padding: 9px 18px; background: #0081c9; color: #fff; border: none; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; transition: background 0.2s; }
        .btn-search:hover { background: #005b94; }
        .btn-reset { padding: 9px 14px; background: #f0f0f0; color: #666; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; }
        .btn-reset:hover { background: #e0e0e0; }

        /* ── TABLE CARD ── */
        .table-card { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.07); overflow: hidden; }
        .table-header { padding: 16px 20px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; justify-content: space-between; }
        .table-header h5 { font-size: 14px; font-weight: 700; color: #1a1a2e; margin: 0; }
        .table-count { font-size: 12px; color: #aaa; }

        table { width: 100%; border-collapse: collapse; }
        thead { background: linear-gradient(135deg, #0081c9, #005b94); }
        thead th { color: #fff; font-size: 12px; font-weight: 600; padding: 12px 16px; text-align: left; border: none; }
        tbody td { padding: 11px 16px; font-size: 13px; color: #333; border-bottom: 1px solid #f5f5f5; vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #f8fbff; }
        .td-muted { color: #bbb; font-size: 12px; }
        .td-small { font-size: 11px; color: #aaa; margin-top: 2px; }

        /* ── AVATAR ── */
        .etudiant-avatar { display: flex; align-items: center; gap: 10px; }
        .mini-avatar { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #0081c9, #005b94); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0; }

        /* ── BADGE FILIERE ── */
        .badge-filiere { display: inline-block; padding: 3px 9px; border-radius: 999px; font-size: 11px; font-weight: 600; background: #e8f4fd; color: #0081c9; }

        /* ── BTN SUPPR ── */
        .btn-suppr { display: inline-block; background: #fdecea; color: #c0392b; padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; border: none; cursor: pointer; transition: opacity 0.2s; }
        .btn-suppr:hover { opacity: 0.8; }

        .empty-row td { text-align: center; color: #bbb; padding: 50px; font-size: 13px; }

        /* ── FILIÈRE CARD ── */
        .filiere-card { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.07); overflow: hidden; }
        .filiere-header { padding: 16px 20px; border-bottom: 1px solid #f0f0f0; }
        .filiere-header h5 { font-size: 14px; font-weight: 700; color: #1a1a2e; margin: 0; }
        .filiere-list { padding: 14px 20px; }
        .filiere-item { display: flex; align-items: center; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f5f5f5; }
        .filiere-item:last-child { border-bottom: none; }
        .filiere-nom { font-size: 13px; color: #333; font-weight: 500; }
        .filiere-nb { font-size: 13px; font-weight: 700; color: #0081c9; }
        .filiere-bar-wrap { height: 4px; background: #f0f0f0; border-radius: 2px; margin-top: 4px; }
        .filiere-bar { height: 4px; background: #0081c9; border-radius: 2px; }

        footer { text-align: center; padding: 20px; font-size: 12px; color: #bbb; border-top: 1px solid #ebebeb; }
        .menu-toggle { display: none; position: fixed; top: 14px; left: 14px; z-index: 300; background: #0081c9; border: none; border-radius: 8px; padding: 8px 11px; color: #fff; font-size: 18px; cursor: pointer; }

        @media (max-width: 900px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-wrap { margin-left: 0; }
            .menu-toggle { display: block; }
            .content { padding: 16px; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .layout { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<button class="menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img src="../assets/images/esmia.jpg" alt="logo">
        <span>Restaurant<br>Universitaire</span>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section-title">Principal</div>
        <a href="dashboard_admin.php" class="sidebar-link"><span class="icon">📊</span> Tableau de bord</a>
        <a href="gestion_menus.php"   class="sidebar-link"><span class="icon">🍽️</span> Gestion des menus</a>
        <a href="reservations.php"    class="sidebar-link"><span class="icon">📅</span> Réservations</a>
        <div class="nav-section-title">Gestion</div>
        <a href="etudiants.php"       class="sidebar-link active"><span class="icon">🎓</span> Étudiants</a>
        <a href="gestion_plats.php"   class="sidebar-link"><span class="icon">🥘</span> Plats</a>
        <a href="controle_quotas.php" class="sidebar-link"><span class="icon">📈</span> Quotas</a>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="avatar"><?= strtoupper(substr($_SESSION['identifiant'] ?? 'A', 0, 1)) ?></div>
            <div>
                <div class="name"><?= htmlspecialchars($_SESSION['identifiant'] ?? 'Admin') ?></div>
                <div class="role">Gestionnaire</div>
            </div>
        </div>
        <a href="../logout.php" class="btn-logout-side">🚪 Déconnexion</a>
    </div>
</aside>

<!-- MAIN -->
<div class="main-wrap">
    <div class="topbar">
        <h2>🎓 Gestion des Étudiants</h2>
        <span class="topbar-count"><?= $total ?> étudiant(s) inscrit(s)</span>
    </div>

    <div class="content">

        <?= $message ?>

        <!-- STATS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f4fd;">🎓</div>
                <div>
                    <div class="stat-label">Total étudiants</div>
                    <div class="stat-value" style="color:#0081c9;"><?= $total ?></div>
                    <div class="stat-sub">inscrits au RU</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f8f0;">📅</div>
                <div>
                    <div class="stat-label">Inscrits ce mois</div>
                    <div class="stat-value" style="color:#1a6b40;"><?= $ce_mois ?></div>
                    <div class="stat-sub">nouveaux comptes</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fff3e0;">📚</div>
                <div>
                    <div class="stat-label">Filières</div>
                    <div class="stat-value" style="color:#e65100;"><?= count($par_filiere) ?></div>
                    <div class="stat-sub">représentées</div>
                </div>
            </div>
        </div>

        <div class="layout">
            <!-- TABLEAU -->
            <div>
                <p class="section-title">Liste des étudiants</p>

                <!-- RECHERCHE -->
                <form method="GET" class="search-bar">
                    <input type="text" name="search" class="search-input"
                           placeholder="🔍 Rechercher par nom, prénom, matricule ou email..."
                           value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn-search">Rechercher</button>
                    <?php if ($search): ?>
                        <a href="etudiants.php" class="btn-reset">✕ Reset</a>
                    <?php endif; ?>
                </form>

                <div class="table-card">
                    <div class="table-header">
                        <h5>📋 Étudiants <?= $search ? '— résultats pour "'.htmlspecialchars($search).'"' : '' ?></h5>
                        <span class="table-count"><?= count($etudiants) ?> résultat(s)</span>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Étudiant</th>
                                <th>Matricule</th>
                                <th>Filière</th>
                                <th>Contact</th>
                                <th>Inscription</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($etudiants)): ?>
                                <tr class="empty-row"><td colspan="6">Aucun étudiant trouvé</td></tr>
                            <?php else: ?>
                                <?php foreach ($etudiants as $e):
                                    $initiales = strtoupper(substr($e['prenom'],0,1) . substr($e['nom'],0,1));
                                ?>
                                <tr>
                                    <td>
                                        <div class="etudiant-avatar">
                                            <div class="mini-avatar"><?= $initiales ?></div>
                                            <div>
                                                <strong><?= htmlspecialchars($e['prenom'] . ' ' . $e['nom']) ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="td-muted"><?= htmlspecialchars($e['matricule']) ?></td>
                                    <td><span class="badge-filiere"><?= htmlspecialchars($e['filiere']) ?></span></td>
                                    <td>
                                        <div><?= htmlspecialchars($e['email']) ?></div>
                                        <div class="td-small"><?= htmlspecialchars($e['telephone']) ?></div>
                                    </td>
                                    <td class="td-muted"><?= date('d/m/Y', strtotime($e['date_inscription'])) ?></td>
                                    <td>
                                        <form method="POST" onsubmit="return confirm('Supprimer cet étudiant et toutes ses réservations ?')">
                                            <input type="hidden" name="action" value="supprimer">
                                            <input type="hidden" name="id_etudiant" value="<?= $e['id_etudiant'] ?>">
                                            <button type="submit" class="btn-suppr">🗑️ Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SIDEBAR FILIÈRES -->
            <div>
                <p class="section-title">Répartition par filière</p>
                <div class="filiere-card">
                    <div class="filiere-header">
                        <h5>📚 Filières</h5>
                    </div>
                    <div class="filiere-list">
                        <?php if (empty($par_filiere)): ?>
                            <p style="color:#bbb;font-size:13px;text-align:center;padding:20px 0;">Aucune donnée</p>
                        <?php else: ?>
                            <?php foreach ($par_filiere as $f):
                                $pct = $total > 0 ? round($f['nb'] / $total * 100) : 0;
                            ?>
                            <div class="filiere-item">
                                <div style="flex:1;">
                                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                                        <span class="filiere-nom"><?= htmlspecialchars($f['filiere']) ?></span>
                                        <span class="filiere-nb"><?= $f['nb'] ?></span>
                                    </div>
                                    <div class="filiere-bar-wrap">
                                        <div class="filiere-bar" style="width:<?= $pct ?>%;"></div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('click', function(e) {
        const sidebar = document.getElementById('sidebar');
        const toggle  = document.querySelector('.menu-toggle');
        if (window.innerWidth <= 900 && sidebar && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
            sidebar.classList.remove('open');
        }
    });
</script>
</body>
</html>