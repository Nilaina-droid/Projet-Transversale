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
        $nom_plat     = htmlspecialchars($_POST['nom_plat']);
        $descriptions = htmlspecialchars($_POST['descriptions']);
        $type_plat    = $_POST['type_plat'];
        $calories     = $_POST['calories'] ?: NULL;
        $allergens    = htmlspecialchars($_POST['allergens']) ?: NULL;

        $stmt = $pdo->prepare("INSERT INTO plat (nom_plat, descriptions, type_plat, calories, allergens) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nom_plat, $descriptions, $type_plat, $calories, $allergens]);
        $message = "<div class='alert-success'>✅ Plat ajouté avec succès !</div>";
    }

    if ($_POST['action'] === 'supprimer') {
        $id = $_POST['id_plat'];
        $pdo->prepare("DELETE FROM plat WHERE id_plat = ?")->execute([$id]);
        $message = "<div class='alert-danger'>🗑️ Plat supprimé.</div>";
    }
}

$plats = $pdo->query("SELECT * FROM plat ORDER BY type_plat, nom_plat")->fetchAll();

// Stats par type
$par_type = [];
foreach ($plats as $p) {
    $par_type[$p['type_plat']] = ($par_type[$p['type_plat']] ?? 0) + 1;
}

$type_config = [
    'Viande'     => ['icon' => '🥩', 'bg' => '#fdecea', 'color' => '#c0392b'],
    'Poisson'    => ['icon' => '🐟', 'bg' => '#e8f4fd', 'color' => '#005b94'],
    'Légumes'    => ['icon' => '🥦', 'bg' => '#e8f8f0', 'color' => '#1a6b40'],
    'Végétarien' => ['icon' => '🥦', 'bg' => '#e8f8f0', 'color' => '#1a6b40'],
    'Dessert'    => ['icon' => '🍮', 'bg' => '#fff3e0', 'color' => '#e65100'],
    'Boisson'    => ['icon' => '🥤', 'bg' => '#f3e8ff', 'color' => '#7b2d8b'],
    'Entrée'     => ['icon' => '🥗', 'bg' => '#e8f4fd', 'color' => '#0081c9'],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Plats — RU ESMIA</title>
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../Style/gestion_plats.css">
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
        <a href="gestion_plats.php"   class="sidebar-link active"><span class="icon">🥘</span> Plats</a>
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
        <h2>🥘 Gestion des Plats</h2>
        <span class="topbar-count"><?= count($plats) ?> plat(s) enregistré(s)</span>
    </div>

    <div class="content">

        <?= $message ?>

        <!-- STATS PAR TYPE -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f4fd;">🍽️</div>
                <div>
                    <div class="stat-label">Total plats</div>
                    <div class="stat-value" style="color:#0081c9;"><?= count($plats) ?></div>
                </div>
            </div>
            <?php foreach (array_slice($par_type, 0, 3, true) as $type => $nb):
                $cfg = $type_config[$type] ?? ['icon'=>'🍽️','bg'=>'#f0f0f0','color'=>'#333'];
            ?>
            <div class="stat-card">
                <div class="stat-icon" style="background:<?= $cfg['bg'] ?>;"><?= $cfg['icon'] ?></div>
                <div>
                    <div class="stat-label"><?= htmlspecialchars($type) ?></div>
                    <div class="stat-value" style="color:<?= $cfg['color'] ?>;"><?= $nb ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="layout">

            <!-- FORMULAIRE -->
            <div>
                <p class="section-title">➕ Ajouter un plat</p>
                <div class="card-form">
                    <div class="card-form-header">
                        <span style="font-size:18px;">🥘</span>
                        <h5>Nouveau plat</h5>
                    </div>
                    <div class="card-form-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="ajouter">

                            <div class="form-group">
                                <label>Nom du plat</label>
                                <input type="text" name="nom_plat" required placeholder="Ex : Poulet frit">
                            </div>

                            <div class="form-grid2">
                                <div class="form-group">
                                    <label>Type</label>
                                    <select name="type_plat" required>
                                        <option value="Viande">🥩 Viande</option>
                                        <option value="Poisson">🐟 Poisson</option>
                                        <option value="Légumes">🥦 Légumes</option>
                                        <option value="Végétarien">🌿 Végétarien</option>
                                        <option value="Entrée">🥗 Entrée</option>
                                        <option value="Féculent">🍚 Féculent</option>
                                        <option value="Dessert">🍮 Dessert</option>
                                        <option value="Boisson">🥤 Boisson</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Calories <span style="font-weight:400;color:#aaa;text-transform:none;">(optionnel)</span></label>
                                    <input type="number" name="calories" placeholder="Ex : 450">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="descriptions" placeholder="Ex : Poulet frit croustillant avec sauce tomate maison..."></textarea>
                            </div>

                            <div class="form-group">
                                <label>Allergènes <span style="font-weight:400;color:#aaa;text-transform:none;">(optionnel)</span></label>
                                <input type="text" name="allergens" placeholder="Ex : Gluten, Arachides, Lactose">
                            </div>

                            <button type="submit" class="btn-submit">✅ Ajouter le plat</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- TABLEAU -->
            <div>
                <p class="section-title">📋 Liste des plats</p>
                <div class="table-card">
                    <div class="table-header">
                        <h5>Plats disponibles</h5>
                        <span class="table-count"><?= count($plats) ?> plat(s)</span>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Plat</th>
                                <th>Type</th>
                                <th>Infos</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($plats)): ?>
                                <tr class="empty-row"><td colspan="4">Aucun plat enregistré pour le moment</td></tr>
                            <?php else: ?>
                                <?php foreach ($plats as $p):
                                    $cfg  = $type_config[$p['type_plat']] ?? ['icon'=>'🍽️','bg'=>'#f0f0f0','color'=>'#333'];
                                ?>
                                <tr>
                                    <td>
                                        <div class="plat-nom-row">
                                            <div class="plat-mini-icon" style="background:<?= $cfg['bg'] ?>;"><?= $cfg['icon'] ?></div>
                                            <div>
                                                <strong><?= htmlspecialchars($p['nom_plat']) ?></strong>
                                                <?php if (!empty($p['descriptions'])): ?>
                                                    <div class="td-muted"><?= htmlspecialchars(mb_strimwidth($p['descriptions'], 0, 45, '…')) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-type" style="background:<?= $cfg['bg'] ?>;color:<?= $cfg['color'] ?>;">
                                            <?= $cfg['icon'] ?> <?= htmlspecialchars($p['type_plat']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display:flex;gap:5px;flex-wrap:wrap;">
                                            <?php if (!empty($p['calories'])): ?>
                                                <span class="tag tag-cal">🔥 <?= $p['calories'] ?> kcal</span>
                                            <?php endif; ?>
                                            <?php if (!empty($p['allergens'])): ?>
                                                <span class="tag tag-alrg">⚠️ <?= htmlspecialchars($p['allergens']) ?></span>
                                            <?php endif; ?>
                                            <?php if (empty($p['calories']) && empty($p['allergens'])): ?>
                                                <span style="color:#ccc;font-size:12px;">—</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <form method="POST" onsubmit="return confirm('Supprimer ce plat ?')">
                                            <input type="hidden" name="action" value="supprimer">
                                            <input type="hidden" name="id_plat" value="<?= $p['id_plat'] ?>">
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