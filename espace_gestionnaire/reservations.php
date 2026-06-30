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
    <link rel="stylesheet" href="../Style/resevation.css">
    
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