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
        $message = "<div class='alert-success'><i class='fas fa-check'></i> Quota ajouté avec succès !</div>";
    }

    if ($_POST['action'] === 'supprimer') {
        $id = $_POST['id_quota'];
        $pdo->prepare("DELETE FROM quota WHERE id_quota = ?")->execute([$id]);
        $message = "<div class='alert-danger'><i class='fas fa-trash'></i> Quota supprimé.</div>";
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
    <link rel="stylesheet" href="../Style/controle_quotas.css">
    
</head>
<body>

<button class="menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="fas fa-bars"></i></button>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img src="../assets/images/esmia.jpg" alt="logo">
        <span>Restaurant<br>Universitaire</span>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section-title">Principal</div>
        <a href="dashboard_admin.php" class="sidebar-link"><span class="icon"><i class="fas fa-chart-bar"></i></span> Tableau de bord</a>
        <a href="gestion_menus.php"   class="sidebar-link"><span class="icon"><i class="fas fa-utensils"></i></span> Gestion des menus</a>
        <a href="reservations.php"    class="sidebar-link"><span class="icon"><i class="fas fa-calendar-alt"></i></span> Réservations</a>
        <div class="nav-section-title">Gestion</div>
        <a href="etudiants.php"       class="sidebar-link"><span class="icon"><i class="fas fa-graduation-cap"></i></span> Étudiants</a>
        <a href="gestion_plats.php"   class="sidebar-link"><span class="icon"><i class="fas fa-bowl-food"></i></span> Plats</a>
        <a href="controle_quotas.php" class="sidebar-link active"><span class="icon"><i class="fas fa-chart-line"></i></span> Quotas</a>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="avatar"><?= strtoupper(substr($_SESSION['identifiant'] ?? 'A', 0, 1)) ?></div>
            <div>
                <div class="name"><?= htmlspecialchars($_SESSION['identifiant'] ?? 'Admin') ?></div>
                <div class="role">Gestionnaire</div>
            </div>
        </div>
        <a href="../logout.php" class="btn-logout-side"><i class="fas fa-right-from-bracket"></i> Déconnexion</a>
    </div>
</aside>

<!-- MAIN -->
<div class="main-wrap">
    <div class="topbar">
        <h2><i class="fas fa-chart-line"></i> Contrôle des Quotas</h2>
        <span class="topbar-date"><i class="fas fa-calendar-alt"></i> <?= date('d/m/Y') ?></span>
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
                <div class="stat-icon" style="background:#e8f4fd;"><i class="fas fa-clipboard-list"></i></div>
                <div>
                    <div class="stat-label">Quotas définis</div>
                    <div class="stat-value" style="color:#0081c9;"><?= count($quotas) ?></div>
                    <div class="stat-sub">au total</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f8f0;"><i class="fas fa-check"></i></div>
                <div>
                    <div class="stat-label">Places totales</div>
                    <div class="stat-value" style="color:#1a6b40;"><?= $total_places ?></div>
                    <div class="stat-sub">disponibles</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fff3e0;"><i class="fas fa-graduation-cap"></i></div>
                <div>
                    <div class="stat-label">Places prises</div>
                    <div class="stat-value" style="color:#e65100;"><?= $places_prises ?></div>
                    <div class="stat-sub">réservations</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fdecea;"><i class="fas fa-circle-check"></i></div>
                <div>
                    <div class="stat-label">Places restantes</div>
                    <div class="stat-value" style="color:#c0392b;"><?= $places_restantes ?></div>
                    <div class="stat-sub">encore disponibles</div>
                </div>
            </div>
        </div>

        <!-- FORMULAIRE -->
        <p class="section-title"><i class="fas fa-plus"></i> Définir un nouveau quota</p>
        <div class="card-form">
            <div class="card-form-header">
                <span style="font-size:18px;"><i class="fas fa-chart-line"></i></span>
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
                            <button type="submit" class="btn-submit"><i class="fas fa-check"></i> Ajouter le quota</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- TABLEAU -->
        <p class="section-title"><i class="fas fa-clipboard-list"></i> Liste des quotas</p>
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
                                    <button type="submit" class="btn-suppr"><i class="fas fa-trash"></i> Supprimer</button>
                                </form>
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
        if (window.innerWidth <= 900 && sidebar && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
            sidebar.classList.remove('open');
        }
    });
</script>
</body>
</html>