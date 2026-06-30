<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'gestionnaire') {
    header("Location: ../index.php");
    exit();
}
require_once '../config/connexion.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = $_POST['id_reservation'];
    if ($_POST['action'] === 'confirmer') {
        $pdo->prepare("UPDATE reservation SET statut = 'CONFIRMEE' WHERE id_reservation = ?")->execute([$id]);
        $message = "<div class='alert-success'>✅ Réservation confirmée !</div>";
    }
    if ($_POST['action'] === 'annuler') {
        $pdo->prepare("UPDATE reservation SET statut = 'ANNULEE' WHERE id_reservation = ?")->execute([$id]);
        $message = "<div class='alert-danger'>❌ Réservation annulée.</div>";
    }
    if ($_POST['action'] === 'honorer') {
        $pdo->prepare("UPDATE reservation SET statut = 'HONOREE' WHERE id_reservation = ?")->execute([$id]);
        $message = "<div class='alert-success'>🎓 Réservation honorée !</div>";
    }
}

$filtre = $_GET['filtre'] ?? 'TOUS';
if ($filtre === 'TOUS') {
    $reservations = $pdo->query("SELECT r.*, e.nom, e.prenom, e.matricule FROM reservation r JOIN etudiant e ON r.id_etudiant = e.id_etudiant ORDER BY r.date_reservation DESC")->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT r.*, e.nom, e.prenom, e.matricule FROM reservation r JOIN etudiant e ON r.id_etudiant = e.id_etudiant WHERE r.statut = ? ORDER BY r.date_reservation DESC");
    $stmt->execute([$filtre]);
    $reservations = $stmt->fetchAll();
}
$all = $pdo->query("SELECT statut, COUNT(*) as nb FROM reservation GROUP BY statut")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservations — RU ESMIA</title>
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        :root { --sidebar-w: 240px; }
        body { background-color: #efefef; min-height: 100vh; }
        .sidebar { position: fixed; top: 0; left: 0; width: var(--sidebar-w); height: 100vh; background: linear-gradient(180deg, #0081c9 0%, #005b94 100%); display: flex; flex-direction: column; z-index: 200; box-shadow: 4px 0 20px rgba(0,0,0,0.12); transition: transform .3s; }
        .sidebar-brand { padding: 20px 18px; border-bottom: 1px solid rgba(255,255,255,0.12); display: flex; align-items: center; gap: 10px; }
        .sidebar-brand img { width: 38px; height: 38px; object-fit: contain; border-radius: 8px; padding: 3px; background: rgba(255,255,255,0.15); }
        .sidebar-brand span { font-size: 13px; font-weight: 700; color: #fff; line-height: 1.3; }
        .sidebar-nav { flex: 1; padding: 16px 10px; overflow-y: auto; }
        .nav-section-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: rgba(255,255,255,0.4); padding: 10px 10px 5px; margin-top: 6px; }
        .sidebar-link { display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: 8px; color: rgba(255,255,255,0.75); text-decoration: none; font-size: 13px; font-weight: 500; transition: background .18s, color .18s; margin-bottom: 2px; }
        .sidebar-link .icon { font-size: 16px; width: 20px; text-align: center; }
        .sidebar-link:hover { background: rgba(255,255,255,0.12); color: #fff; }
        .sidebar-link.active { background: rgba(255,255,255,0.18); color: #fff; font-weight: 700; }
        .sidebar-footer { padding: 12px 10px; border-top: 1px solid rgba(255,255,255,0.12); }
        .sidebar-user { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px; background: rgba(255,255,255,0.1); margin-bottom: 8px; }
        .sidebar-user .avatar { width: 34px; height: 34px; background: rgba(255,255,255,0.25); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; color: #fff; flex-shrink: 0; }
        .sidebar-user .name { font-size: 13px; font-weight: 600; color: #fff; }
        .sidebar-user .role { font-size: 11px; color: rgba(255,255,255,0.5); }
        .btn-logout-side { display: flex; align-items: center; gap: 8px; width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: transparent; color: rgba(255,255,255,0.7); font-size: 13px; text-decoration: none; transition: background .18s; }
        .btn-logout-side:hover { background: rgba(220,53,69,0.2); color: #ff8a80; border-color: #ff8a80; }
        .main-wrap { margin-left: var(--sidebar-w); min-height: 100vh; display: flex; flex-direction: column; }
        .topbar { background: #fff; border-bottom: 1px solid #e8e8e8; padding: 14px 28px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .topbar h2 { font-size: 17px; font-weight: 700; color: #1a1a2e; margin: 0; }
        .topbar-count { font-size: 12px; color: #aaa; background: #f0f0f0; padding: 5px 12px; border-radius: 20px; font-weight: 600; }
        .content { padding: 28px; flex: 1; }
        .alert-success { background: #e8f4fd; color: #005b94; border-left: 4px solid #0081c9; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; font-size: 13px; }
        .alert-danger  { background: #fdecea; color: #c0392b; border-left: 4px solid #dc3545; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; font-size: 13px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 24px; }
        .stat-card { background: #fff; border-radius: 12px; box-shadow: 0 3px 8px rgba(0,0,0,0.06); padding: 16px 18px; display: flex; align-items: center; gap: 14px; }
        .stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
        .stat-label { font-size: 12px; color: #999; margin-bottom: 2px; }
        .stat-value { font-size: 22px; font-weight: 700; line-height: 1; }
        .section-title { font-size: 13px; font-weight: 700; color: #999; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px; }
        .table-card { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.07); overflow: hidden; }
        .table-header { padding: 16px 20px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
        .table-header h5 { font-size: 14px; font-weight: 700; color: #1a1a2e; margin: 0; }
        .filtres { display: flex; gap: 6px; flex-wrap: wrap; }
        .filtre-btn { display: inline-block; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-decoration: none; color: #666; background: #f4f4f4; border: 1.5px solid #e8e8e8; transition: all .18s; }
        .filtre-btn:hover { border-color: #0081c9; color: #0081c9; background: #f0f7ff; }
        .filtre-btn.active { background: #0081c9; color: #fff; border-color: #0081c9; }
        table { width: 100%; border-collapse: collapse; }
        thead { background: linear-gradient(135deg, #0081c9, #005b94); }
        thead th { color: #fff; font-size: 12px; font-weight: 600; padding: 12px 16px; text-align: left; border: none; }
        tbody td { padding: 12px 16px; font-size: 13px; color: #333; border-bottom: 1px solid #f5f5f5; vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #f8fbff; }
        .td-muted { color: #bbb; font-size: 12px; }
        .td-italic { color: #aaa; font-style: italic; font-size: 12px; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; }
        .badge-attente   { background: #fff3e0; color: #e65100; }
        .badge-confirmee { background: #e8f4fd; color: #005b94; }
        .badge-annulee   { background: #fdecea; color: #c0392b; }
        .badge-honoree   { background: #e8f8f0; color: #1a6b40; }
        .btn-action { display: inline-block; padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; border: none; cursor: pointer; transition: opacity .2s; }
        .btn-action:hover { opacity: 0.8; }
        .btn-confirmer { background: #e8f4fd; color: #005b94; }
        .btn-annuler   { background: #fdecea; color: #c0392b; margin-left: 4px; }
        .btn-honorer   { background: #e8f8f0; color: #1a6b40; }
        .empty-row td { text-align: center; color: #bbb; padding: 50px; font-size: 13px; }
        footer { text-align: center; padding: 20px; font-size: 12px; color: #bbb; border-top: 1px solid #ebebeb; }
        .menu-toggle { display: none; position: fixed; top: 14px; left: 14px; z-index: 300; background: #0081c9; border: none; border-radius: 8px; padding: 8px 11px; color: #fff; font-size: 18px; cursor: pointer; }
        @media (max-width: 900px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-wrap { margin-left: 0; }
            .menu-toggle { display: block; }
            .content { padding: 16px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
<button class="menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img src="../assets/images/esmia.jpg" alt="logo">
        <span>Restaurant<br>Universitaire</span>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section-title">Principal</div>
        <a href="dashboard_admin.php" class="sidebar-link"><span class="icon">📊</span> Tableau de bord</a>
        <a href="gestion_menus.php"   class="sidebar-link"><span class="icon">🍽️</span> Gestion des menus</a>
        <a href="reservations.php"    class="sidebar-link active"><span class="icon">📅</span> Réservations</a>
        <div class="nav-section-title">Gestion</div>
        <a href="etudiants.php"       class="sidebar-link"><span class="icon">🎓</span> Étudiants</a>
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
<div class="main-wrap">
    <div class="topbar">
        <h2>📅 Gestion des Réservations</h2>
        <span class="topbar-count"><?= count($reservations) ?> réservation(s)</span>
    </div>
    <div class="content">
        <?= $message ?>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background:#f0f0f0;">📋</div>
                <div><div class="stat-label">Total</div><div class="stat-value" style="color:#1a1a2e;"><?= array_sum($all) ?></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fff3e0;">⏳</div>
                <div><div class="stat-label">En attente</div><div class="stat-value" style="color:#e65100;"><?= $all['EN_ATTENTE'] ?? 0 ?></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f4fd;">✅</div>
                <div><div class="stat-label">Confirmées</div><div class="stat-value" style="color:#0081c9;"><?= $all['CONFIRMEE'] ?? 0 ?></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f8f0;">🎓</div>
                <div><div class="stat-label">Honorées</div><div class="stat-value" style="color:#1a6b40;"><?= $all['HONOREE'] ?? 0 ?></div></div>
            </div>
        </div>
        <p class="section-title">Liste des réservations</p>
        <div class="table-card">
            <div class="table-header">
                <h5>📋 Réservations</h5>
                <div class="filtres">
                    <a href="?filtre=TOUS"      class="filtre-btn <?= $filtre==='TOUS'      ?'active':'' ?>">Tous</a>
                    <a href="?filtre=EN_ATTENTE" class="filtre-btn <?= $filtre==='EN_ATTENTE'?'active':'' ?>">⏳ En attente</a>
                    <a href="?filtre=CONFIRMEE"  class="filtre-btn <?= $filtre==='CONFIRMEE' ?'active':'' ?>">✅ Confirmées</a>
                    <a href="?filtre=HONOREE"    class="filtre-btn <?= $filtre==='HONOREE'   ?'active':'' ?>">🎓 Honorées</a>
                    <a href="?filtre=ANNULEE"    class="filtre-btn <?= $filtre==='ANNULEE'   ?'active':'' ?>">❌ Annulées</a>
                </div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>#</th><th>Étudiant</th><th>Matricule</th><th>Date réservation</th><th>Commentaire</th><th>Statut</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reservations)): ?>
                        <tr class="empty-row"><td colspan="7">Aucune réservation trouvée pour ce filtre</td></tr>
                    <?php else: ?>
                        <?php foreach ($reservations as $r):
                            $badges = ['EN_ATTENTE'=>['badge-attente','⏳ En attente'],'CONFIRMEE'=>['badge-confirmee','✅ Confirmée'],'ANNULEE'=>['badge-annulee','❌ Annulée'],'HONOREE'=>['badge-honoree','🎓 Honorée']];
                            $b = $badges[$r['statut']] ?? ['',''];
                        ?>
                        <tr>
                            <td class="td-muted"><?= $r['id_reservation'] ?></td>
                            <td><strong><?= htmlspecialchars($r['prenom'] . ' ' . $r['nom']) ?></strong></td>
                            <td class="td-muted"><?= htmlspecialchars($r['matricule']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($r['date_reservation'])) ?></td>
                            <td class="td-italic"><?= $r['commentaire'] ? htmlspecialchars($r['commentaire']) : '—' ?></td>
                            <td><span class="badge <?= $b[0] ?>"><?= $b[1] ?></span></td>
                            <td>
                                <?php if ($r['statut'] === 'EN_ATTENTE'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="confirmer">
                                        <input type="hidden" name="id_reservation" value="<?= $r['id_reservation'] ?>">
                                        <button type="submit" class="btn-action btn-confirmer">✅ Confirmer</button>
                                    </form>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="annuler">
                                        <input type="hidden" name="id_reservation" value="<?= $r['id_reservation'] ?>">
                                        <button type="submit" class="btn-action btn-annuler">❌ Annuler</button>
                                    </form>
                                <?php elseif ($r['statut'] === 'CONFIRMEE'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="honorer">
                                        <input type="hidden" name="id_reservation" value="<?= $r['id_reservation'] ?>">
                                        <button type="submit" class="btn-action btn-honorer">🎓 Honorer</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color:#bbb;font-size:12px;">—</span>
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
        const toggle = document.querySelector('.menu-toggle');
        if (window.innerWidth <= 900 && sidebar && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
            sidebar.classList.remove('open');
        }
    });
</script>
</body>
</html>