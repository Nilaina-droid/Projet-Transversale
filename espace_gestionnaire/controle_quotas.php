<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'gestionnaire') {
    header("Location: ../index.php");
    exit();
}
require_once '../config/connexion.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'ajouter') {
        $quantite_max      = $_POST['quantite_max'];
        $quantite_restante = $_POST['quantite_max'];
        $jour              = $_POST['jour'];
        $id_plat           = $_POST['id_plat'];
        $id_service        = $_POST['id_service'];
        $id_menu           = $_POST['id_menu'];

        $stmt = $pdo->prepare("INSERT INTO quota (quantite_max, quantite_restante, jour, id_plat, id_service, id_menu) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$quantite_max, $quantite_restante, $jour, $id_plat, $id_service, $id_menu]);
        $message = "<div class='alert-success'>✅ Quota ajouté avec succès !</div>";
    }

    if ($_POST['action'] === 'supprimer') {
        $id = $_POST['id_quota'];
        $pdo->prepare("DELETE FROM quota WHERE id_quota = ?")->execute([$id]);
        $message = "<div class='alert-danger'>🗑️ Quota supprimé.</div>";
    }
}

$quotas = $pdo->query("
    SELECT q.*, p.nom_plat, s.Type_service, s.heure_debut, s.heure_fin, m.semaine_du
    FROM quota q
    JOIN plat p ON q.id_plat = p.id_plat
    JOIN service s ON q.id_service = s.id_service
    JOIN menu m ON q.id_menu = m.id_menu
    ORDER BY q.jour DESC
")->fetchAll();

$plats    = $pdo->query("SELECT * FROM plat")->fetchAll();
$services = $pdo->query("SELECT * FROM service")->fetchAll();
$menus    = $pdo->query("SELECT * FROM menu WHERE statut = 'PUBLIE'")->fetchAll();

// Stats globales
$total_places    = array_sum(array_column($quotas, 'quantite_max'));
$places_restantes = array_sum(array_column($quotas, 'quantite_restante'));
$places_prises   = $total_places - $places_restantes;
$taux_global     = $total_places > 0 ? round($places_prises / $total_places * 100) : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrôle des Quotas — RU ESMIA</title>
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
        .topbar-date { font-size: 12px; color: #aaa; background: #f0f0f0; padding: 5px 12px; border-radius: 20px; font-weight: 600; }
        .content { padding: 28px; flex: 1; }

        /* ── ALERTS ── */
        .alert-success { background: #e8f4fd; color: #005b94; border-left: 4px solid #0081c9; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; font-size: 13px; }
        .alert-danger  { background: #fdecea; color: #c0392b; border-left: 4px solid #dc3545; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; font-size: 13px; }

        /* ── STATS ── */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 24px; }
        .stat-card { background: #fff; border-radius: 12px; box-shadow: 0 3px 8px rgba(0,0,0,0.06); padding: 16px 18px; display: flex; align-items: center; gap: 14px; }
        .stat-icon { width: 46px; height: 46px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
        .stat-label { font-size: 12px; color: #999; margin-bottom: 2px; }
        .stat-value { font-size: 22px; font-weight: 700; line-height: 1; }
        .stat-sub   { font-size: 11px; color: #aaa; margin-top: 3px; }

        /* ── SECTION TITLE ── */
        .section-title { font-size: 13px; font-weight: 700; color: #999; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px; }

        /* ── FORM CARD ── */
        .card-form { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.07); overflow: hidden; margin-bottom: 24px; }
        .card-form-header { background: linear-gradient(135deg, #0081c9, #005b94); padding: 16px 22px; display: flex; align-items: center; gap: 10px; }
        .card-form-header h5 { color: #fff; font-size: 15px; font-weight: 700; margin: 0; }
        .card-form-body { padding: 22px; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr 1fr; gap: 0 14px; }
        .form-group { margin-bottom: 14px; }
        .form-group.full { grid-column: 1 / -1; }

        label { display: block; font-weight: 600; font-size: 11px; color: #666; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 5px; }

        input[type="date"], input[type="number"], select {
            width: 100%; padding: 9px 12px; border: 1px solid #e0e0e0; border-radius: 8px;
            font-size: 13px; background-color: #fafafa; color: #333;
            font-family: 'Segoe UI', sans-serif; height: 38px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        input:focus, select:focus { outline: none; border-color: #0081c9; box-shadow: 0 0 0 3px rgba(0,129,201,0.10); background: #fff; }

        .btn-submit { display: inline-flex; align-items: center; gap: 8px; background: #0081c9; color: #fff; padding: 10px 22px; border-radius: 8px; border: none; cursor: pointer; font-size: 14px; font-weight: 700; transition: background 0.2s; }
        .btn-submit:hover { background: #005b94; }

        /* ── TABLE CARD ── */
        .table-card { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.07); overflow: hidden; }
        .table-header { padding: 16px 20px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; justify-content: space-between; }
        .table-header h5 { font-size: 14px; font-weight: 700; color: #1a1a2e; margin: 0; }
        .table-count { font-size: 12px; color: #aaa; }

        table { width: 100%; border-collapse: collapse; }
        thead { background: linear-gradient(135deg, #0081c9, #005b94); }
        thead th { color: #fff; font-size: 12px; font-weight: 600; padding: 12px 16px; text-align: left; border: none; }
        tbody td { padding: 12px 16px; font-size: 13px; color: #333; border-bottom: 1px solid #f5f5f5; vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #f8fbff; }
        .td-muted { color: #aaa; font-size: 11px; margin-top: 2px; }

        /* ── PROGRESS BAR ── */
        .progress-wrap { min-width: 130px; }
        .progress-bar-bg { height: 8px; background: #f0f0f0; border-radius: 999px; overflow: hidden; margin-bottom: 4px; }
        .progress-bar-fill { height: 8px; border-radius: 999px; transition: width 0.3s; }
        .bar-ok      { background: #0081c9; }
        .bar-warning { background: #ffa000; }
        .bar-danger  { background: #dc3545; }
        .progress-label { font-size: 11px; font-weight: 600; color: #888; }

        /* ── BADGE PLACES ── */
        .badge-places { display: inline-block; padding: 3px 9px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .badge-ok      { background: #e8f4fd; color: #0081c9; }
        .badge-warning { background: #fff3e0; color: #e65100; }
        .badge-danger  { background: #fdecea; color: #c0392b; }

        .btn-suppr { display: inline-block; background: #fdecea; color: #c0392b; padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; border: none; cursor: pointer; transition: opacity 0.2s; }
        .btn-suppr:hover { opacity: 0.8; }

        .empty-row td { text-align: center; color: #bbb; padding: 50px; font-size: 13px; }

        /* ── TAUX GLOBAL ── */
        .taux-card { background: linear-gradient(135deg, #0081c9, #005b94); border-radius: 14px; padding: 20px 24px; margin-bottom: 24px; color: #fff; display: flex; align-items: center; justify-content: space-between; gap: 20px; }
        .taux-card h3 { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
        .taux-card p  { font-size: 13px; opacity: 0.8; }
        .taux-circle { width: 70px; height: 70px; border-radius: 50%; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700; flex-shrink: 0; border: 3px solid rgba(255,255,255,0.3); }

        footer { text-align: center; padding: 20px; font-size: 12px; color: #bbb; border-top: 1px solid #ebebeb; }
        .menu-toggle { display: none; position: fixed; top: 14px; left: 14px; z-index: 300; background: #0081c9; border: none; border-radius: 8px; padding: 8px 11px; color: #fff; font-size: 18px; cursor: pointer; }

        @media (max-width: 900px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-wrap { margin-left: 0; }
            .menu-toggle { display: block; }
            .content { padding: 16px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .form-grid { grid-template-columns: 1fr 1fr; }
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
        <a href="etudiants.php"       class="sidebar-link"><span class="icon">🎓</span> Étudiants</a>
        <a href="gestion_plats.php"   class="sidebar-link"><span class="icon">🥘</span> Plats</a>
        <a href="controle_quotas.php" class="sidebar-link active"><span class="icon">📈</span> Quotas</a>
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
        <h2>📈 Contrôle des Quotas</h2>
        <span class="topbar-date">📅 <?= date('d/m/Y') ?></span>
    </div>

    <div class="content">

        <?= $message ?>

        <!-- TAUX GLOBAL -->
        <?php if (!empty($quotas)): ?>
        <div class="taux-card">
            <div>
                <h3>Taux de remplissage global</h3>
                <p><?= $places_prises ?> places prises sur <?= $total_places ?> disponibles — <?= $places_restantes ?> restantes</p>
                <div style="margin-top:12px; background:rgba(255,255,255,0.2); border-radius:999px; height:8px; width:300px; max-width:100%;">
                    <div style="height:8px; border-radius:999px; background:#fff; width:<?= $taux_global ?>%;"></div>
                </div>
            </div>
            <div class="taux-circle"><?= $taux_global ?>%</div>
        </div>
        <?php endif; ?>

        <!-- STATS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f4fd;">📋</div>
                <div>
                    <div class="stat-label">Quotas définis</div>
                    <div class="stat-value" style="color:#0081c9;"><?= count($quotas) ?></div>
                    <div class="stat-sub">au total</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f8f0;">✅</div>
                <div>
                    <div class="stat-label">Places totales</div>
                    <div class="stat-value" style="color:#1a6b40;"><?= $total_places ?></div>
                    <div class="stat-sub">disponibles</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fff3e0;">🎓</div>
                <div>
                    <div class="stat-label">Places prises</div>
                    <div class="stat-value" style="color:#e65100;"><?= $places_prises ?></div>
                    <div class="stat-sub">réservations</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fdecea;">🆓</div>
                <div>
                    <div class="stat-label">Places restantes</div>
                    <div class="stat-value" style="color:#c0392b;"><?= $places_restantes ?></div>
                    <div class="stat-sub">encore disponibles</div>
                </div>
            </div>
        </div>

        <!-- FORMULAIRE -->
        <p class="section-title">➕ Définir un nouveau quota</p>
        <div class="card-form">
            <div class="card-form-header">
                <span style="font-size:18px;">📈</span>
                <h5>Nouveau quota</h5>
            </div>
            <div class="card-form-body">
                <form method="POST">
                    <input type="hidden" name="action" value="ajouter">
                    <div class="form-grid">

                        <div class="form-group">
                            <label>Plat</label>
                            <select name="id_plat" required>
                                <option value="">— Choisir —</option>
                                <?php foreach ($plats as $p): ?>
                                    <option value="<?= $p['id_plat'] ?>"><?= htmlspecialchars($p['nom_plat']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Service</label>
                            <select name="id_service" required>
                                <option value="">— Choisir —</option>
                                <?php foreach ($services as $s): ?>
                                    <option value="<?= $s['id_service'] ?>"><?= $s['Type_service'] ?> (<?= $s['heure_debut'] ?> - <?= $s['heure_fin'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Menu</label>
                            <select name="id_menu" required>
                                <option value="">— Choisir —</option>
                                <?php foreach ($menus as $m): ?>
                                    <option value="<?= $m['id_menu'] ?>">Sem. du <?= date('d/m', strtotime($m['semaine_du'])) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Jour</label>
                            <input type="date" name="jour" required>
                        </div>

                        <div class="form-group">
                            <label>Quantité max</label>
                            <input type="number" name="quantite_max" min="1" placeholder="Ex : 50" required>
                        </div>

                        <div class="form-group full">
                            <button type="submit" class="btn-submit">✅ Ajouter le quota</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- TABLEAU -->
        <p class="section-title">📋 Liste des quotas</p>
        <div class="table-card">
            <div class="table-header">
                <h5>Quotas définis</h5>
                <span class="table-count"><?= count($quotas) ?> quota(s)</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Plat</th>
                        <th>Service</th>
                        <th>Jour</th>
                        <th>Semaine</th>
                        <th>Max</th>
                        <th>Restantes</th>
                        <th>Remplissage</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($quotas)): ?>
                        <tr class="empty-row"><td colspan="8">Aucun quota défini pour le moment</td></tr>
                    <?php else: ?>
                        <?php foreach ($quotas as $q):
                            $pct = $q['quantite_max'] > 0 ? round((($q['quantite_max'] - $q['quantite_restante']) / $q['quantite_max']) * 100) : 0;
                            $bar_class   = $pct >= 90 ? 'bar-danger'  : ($pct >= 60 ? 'bar-warning' : 'bar-ok');
                            $badge_class = $q['quantite_restante'] <= 5 ? 'badge-danger' : ($q['quantite_restante'] <= 15 ? 'badge-warning' : 'badge-ok');
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($q['nom_plat']) ?></strong></td>
                            <td>
                                <?= htmlspecialchars($q['Type_service']) ?>
                                <div class="td-muted"><?= $q['heure_debut'] ?> – <?= $q['heure_fin'] ?></div>
                            </td>
                            <td><?= date('d/m/Y', strtotime($q['jour'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($q['semaine_du'])) ?></td>
                            <td><strong><?= $q['quantite_max'] ?></strong></td>
                            <td>
                                <span class="badge-places <?= $badge_class ?>"><?= $q['quantite_restante'] ?></span>
                            </td>
                            <td>
                                <div class="progress-wrap">
                                    <div class="progress-bar-bg">
                                        <div class="progress-bar-fill <?= $bar_class ?>" style="width:<?= $pct ?>%;"></div>
                                    </div>
                                    <span class="progress-label"><?= $pct ?>% rempli</span>
                                </div>
                            </td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Supprimer ce quota ?')">
                                    <input type="hidden" name="action" value="supprimer">
                                    <input type="hidden" name="id_quota" value="<?= $q['id_quota'] ?>">
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
    <footer>© <?= date('Y') ?> Restaurant Universitaire ESMIA — Tous droits réservés</footer>
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