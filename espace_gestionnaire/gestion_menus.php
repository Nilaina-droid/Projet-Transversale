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
        $semaine_du       = $_POST['semaine_du'];
        $semaine_au       = $_POST['semaine_au'];
        $statut           = $_POST['statut'];
        $id_gestionnaire  = $_SESSION['user_id'];
        $date_publication = date('Y-m-d H:i:s');

        $id_service       = !empty($_POST['id_service']) ? $_POST['id_service'] : null;

        if (!empty($_POST['plats'])) {
            if (is_null($id_service)) {
                $message = "<div class='alert-danger'><i class='fa-solid fa-triangle-exclamation'></i> Erreur : Vous devez sélectionner un service valide pour y associer des plats.</div>";
            } else {
                $stmt = $pdo->prepare("INSERT INTO menu (semaine_du, semaine_au, date_publication, statut, id_gestionnaire) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$semaine_du, $semaine_au, $date_publication, $statut, $id_gestionnaire]);
                $id_menu = $pdo->lastInsertId();

                $jour         = $_POST['semaine_du'];
                $quantite_max = $_POST['quantite_max'];

                foreach ($_POST['plats'] as $id_plat) {
                    $stmt2 = $pdo->prepare("INSERT INTO quota (quantite_max, quantite_restante, jour, id_plat, id_service, id_menu) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt2->execute([$quantite_max, $quantite_max, $jour, $id_plat, $id_service, $id_menu]);
                }
                $message = "<div class='alert-success'><i class='fa-solid fa-circle-check'></i> Menu publié avec succès !</div>";
            }
        } else {
            $stmt = $pdo->prepare("INSERT INTO menu (semaine_du, semaine_au, date_publication, statut, id_gestionnaire) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$semaine_du, $semaine_au, $date_publication, $statut, $id_gestionnaire]);
            $message = "<div class='alert-success'><i class='fa-solid fa-circle-check'></i> Menu (sans plats) publié avec succès !</div>";
        }
    }

    if ($_POST['action'] === 'supprimer') {
        $id = $_POST['id_menu'];
        $pdo->prepare("DELETE FROM quota WHERE id_menu = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM menu WHERE id_menu = ?")->execute([$id]);
        $message = "<div class='alert-danger'><i class='fa-solid fa-trash'></i> Menu supprimé.</div>";
    }

    if ($_POST['action'] === 'changer_statut') {
        $id     = $_POST['id_menu'];
        $statut = $_POST['statut'];
        $pdo->prepare("UPDATE menu SET statut = ? WHERE id_menu = ?")->execute([$statut, $id]);
        $message = "<div class='alert-success'><i class='fa-solid fa-circle-check'></i> Statut mis à jour !</div>";
    }
}

$menus    = $pdo->query("SELECT * FROM menu ORDER BY semaine_du DESC")->fetchAll();
$plats    = $pdo->query("SELECT * FROM plat ORDER BY type_plat, nom_plat")->fetchAll();
$services = $pdo->query("SELECT * FROM service")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Menus — RU ESMIA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
        .sidebar-link .icon { font-size: 15px; width: 20px; text-align: center; }
        .sidebar-link:hover  { background: rgba(255,255,255,0.12); color: #fff; }
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
        .topbar h2 { font-size: 17px; font-weight: 700; color: #1a1a2e; margin: 0; display: flex; align-items: center; gap: 10px; }
        .btn-top { display: inline-flex; align-items: center; gap: 8px; background: #0081c9; color: #fff; padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 700; text-decoration: none; transition: background 0.2s; }
        .btn-top:hover { background: #005b94; }
        .content { padding: 28px; flex: 1; }
        .alert-success { background: #e8f4fd; color: #005b94; border-left: 4px solid #0081c9; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; font-size: 13px; display: flex; align-items: center; gap: 8px; }
        .alert-danger  { background: #fdecea; color: #c0392b; border-left: 4px solid #dc3545; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; font-size: 13px; display: flex; align-items: center; gap: 8px; }
        .card-form { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.07); margin-bottom: 24px; overflow: hidden; }
        .card-form-header { background: linear-gradient(135deg, #0081c9, #005b94); padding: 16px 24px; display: flex; align-items: center; gap: 10px; }
        .card-form-header h5 { color: #fff; font-size: 15px; font-weight: 700; margin: 0; }
        .card-form-body { padding: 24px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 0 16px; }
        .form-group { margin-bottom: 16px; }
        .form-group.full { grid-column: 1 / -1; }
        .form-group.half { grid-column: span 2; }
        label { display: flex; align-items: center; gap: 6px; font-weight: 600; font-size: 11px; color: #666; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 5px; }
        input[type="date"], input[type="number"], select { width: 100%; padding: 9px 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 13px; background-color: #fafafa; color: #333; font-family: 'Segoe UI', sans-serif; height: 38px; transition: border-color 0.2s, box-shadow 0.2s; }
        input:focus, select:focus { outline: none; border-color: #0081c9; box-shadow: 0 0 0 3px rgba(0,129,201,0.10); background: #fff; }
        .plats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
        .plat-checkbox { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 8px; border: 1.5px solid #e8e8e8; background: #fafafa; cursor: pointer; transition: all .18s; }
        .plat-checkbox:hover { border-color: #0081c9; background: #f0f7ff; }
        .plat-checkbox input[type="checkbox"] { accent-color: #0081c9; width: 15px; height: 15px; flex-shrink: 0; }
        .plat-checkbox label { display: inline-flex; cursor: pointer; font-size: 13px; font-weight: 500; color: #333; text-transform: none; letter-spacing: 0; margin: 0; gap: 8px; }
        .plat-checkbox label i { color: #0081c9; width: 14px; }
        .no-plat-warning { background: #fff8e1; border-left: 4px solid #ffa000; color: #e65100; padding: 12px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .no-plat-warning a { color: #0081c9; font-weight: 700; }
        .btn-submit { display: inline-flex; align-items: center; gap: 8px; background: #0081c9; color: #fff; padding: 11px 24px; border-radius: 8px; border: none; cursor: pointer; font-size: 14px; font-weight: 700; transition: background 0.2s; margin-top: 6px; }
        .btn-submit:hover { background: #005b94; }
        .table-card { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.07); overflow: hidden; }
        .table-header { padding: 16px 20px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; justify-content: space-between; }
        .table-header h5 { font-size: 14px; font-weight: 700; color: #1a1a2e; margin: 0; display: flex; align-items: center; gap: 8px; }
        .table-count { font-size: 12px; color: #aaa; }
        table { width: 100%; border-collapse: collapse; }
        thead { background: linear-gradient(135deg, #0081c9, #005b94); }
        thead th { color: #fff; font-size: 12px; font-weight: 600; padding: 12px 16px; text-align: left; border: none; }
        tbody td { padding: 12px 16px; font-size: 13px; color: #333; border-bottom: 1px solid #f5f5f5; vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #f8fbff; }
        .td-muted { color: #bbb; font-size: 12px; }
        .badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; }
        .badge-publie    { background: #e8f8f0; color: #1a6b40; }
        .badge-brouillon { background: #fff3e0; color: #e65100; }
        .badge-archive   { background: #f0f0f0; color: #777; }
        .select-statut { padding: 5px 8px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 12px; background: #fafafa; color: #333; cursor: pointer; height: auto; }
        .select-statut:focus { outline: none; border-color: #0081c9; }
        .btn-suppr { display: inline-flex; align-items: center; gap: 5px; background: #fdecea; color: #c0392b; padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; border: none; cursor: pointer; transition: opacity 0.2s; margin-left: 6px; }
        .btn-suppr:hover { opacity: 0.8; }
        .empty-row td { text-align: center; color: #bbb; padding: 40px; font-size: 13px; }
        footer { text-align: center; padding: 20px; font-size: 12px; color: #bbb; border-top: 1px solid #ebebeb; }
        .menu-toggle { display: none; position: fixed; top: 14px; left: 14px; z-index: 300; background: #0081c9; border: none; border-radius: 8px; padding: 8px 11px; color: #fff; font-size: 18px; cursor: pointer; }
        @media (max-width: 900px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-wrap { margin-left: 0; }
            .menu-toggle { display: block; }
            .content { padding: 16px; }
            .form-grid { grid-template-columns: 1fr 1fr; }
            .plats-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>

<button class="menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="fa-solid fa-bars"></i></button>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img src="../assets/images/esmia.jpg" alt="logo">
        <span>Restaurant<br>Universitaire</span>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section-title">Principal</div>
        <a href="dashboard_admin.php" class="sidebar-link"><span class="icon"><i class="fa-solid fa-chart-line"></i></span> Tableau de bord</a>
        <a href="gestion_menus.php"   class="sidebar-link active"><span class="icon"><i class="fa-solid fa-utensils"></i></span> Gestion des menus</a>
        <a href="reservations.php"    class="sidebar-link"><span class="icon"><i class="fa-solid fa-calendar-check"></i></span> Réservations</a>
        <div class="nav-section-title">Gestion</div>
        <a href="etudiants.php"       class="sidebar-link"><span class="icon"><i class="fa-solid fa-graduation-cap"></i></span> Étudiants</a>
        <a href="gestion_plats.php"   class="sidebar-link"><span class="icon"><i class="fa-solid fa-bowl-food"></i></span> Plats</a>
        <a href="controle_quotas.php" class="sidebar-link"><span class="icon"><i class="fa-solid fa-chart-pie"></i></span> Quotas</a>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="avatar"><?= strtoupper(substr($_SESSION['identifiant'] ?? 'A', 0, 1)) ?></div>
            <div>
                <div class="name"><?= htmlspecialchars($_SESSION['identifiant'] ?? 'Admin') ?></div>
                <div class="role">Gestionnaire</div>
            </div>
        </div>
        <a href="../logout.php" class="btn-logout-side"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
    </div>
</aside>

<div class="main-wrap">
    <div class="topbar">
        <h2><i class="fa-solid fa-utensils" style="color:#0081c9;"></i> Gestion des Menus</h2>
        <a href="gestion_plats.php" class="btn-top"><i class="fa-solid fa-bowl-food"></i> Gérer les plats</a>
    </div>

    <div class="content">

        <?= $message ?>

        <div class="card-form">
            <div class="card-form-header">
                <i class="fa-solid fa-plus" style="color:#fff;font-size:16px;"></i>
                <h5>Publier un nouveau menu</h5>
            </div>
            <div class="card-form-body">
                <form method="POST">
                    <input type="hidden" name="action" value="ajouter">
                    <div class="form-grid">

                        <div class="form-group">
                            <label><i class="fa-regular fa-calendar"></i> Semaine du</label>
                            <input type="date" name="semaine_du" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fa-regular fa-calendar"></i> Semaine au</label>
                            <input type="date" name="semaine_au" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fa-solid fa-chair"></i> Places max / plat</label>
                            <input type="number" name="quantite_max" min="1" placeholder="Ex : 50" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fa-solid fa-flag"></i> Statut</label>
                            <select name="statut" required>
                                <option value="PUBLIE">Publié</option>
                                <option value="BROUILLON">Brouillon</option>
                                <option value="ARCHIVE">Archivé</option>
                            </select>
                        </div>

                        <div class="form-group half">
                            <label><i class="fa-solid fa-bell-concierge"></i> Service</label>
                            <select name="id_service" required>
                                <option value="" disabled selected> Choisir un service </option>
                                <?php if (!empty($services)): ?>
                                    <?php foreach ($services as $s): ?>
                                        <option value="<?= $s['id_service'] ?>">
                                            <?= htmlspecialchars($s['Type_service']) ?>
                                            (<?= $s['heure_debut'] ?> - <?= $s['heure_fin'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group full">
                            <label><i class="fa-solid fa-utensils"></i> Plats du menu <span style="font-weight:400;color:#aaa;text-transform:none;">(cochez les plats disponibles)</span></label>
                            <?php if (empty($plats)): ?>
                                <div class="no-plat-warning">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Aucun plat disponible. <a href="gestion_plats.php">Ajoutez des plats d'abord !</a>
                                </div>
                            <?php else: ?>
                                <div class="plats-grid">
                                    <?php
                                    $icon_types = [
                                        'Viande'   => 'fa-solid fa-drumstick-bite',
                                        'Poisson'  => 'fa-solid fa-fish',
                                        'Légumes'  => 'fa-solid fa-carrot',
                                        'Dessert'  => 'fa-solid fa-ice-cream',
                                        'Boisson'  => 'fa-solid fa-mug-saucer',
                                        'Entrée'   => 'fa-solid fa-bowl-rice',
                                        'Féculent' => 'fa-solid fa-wheat-awn',
                                    ];
                                    foreach ($plats as $p):
                                        $icon = $icon_types[$p['type_plat']] ?? 'fa-solid fa-utensils';
                                    ?>
                                    <div class="plat-checkbox">
                                        <input type="checkbox" name="plats[]" value="<?= $p['id_plat'] ?>" id="plat_<?= $p['id_plat'] ?>">
                                        <label for="plat_<?= $p['id_plat'] ?>"><i class="<?= $icon ?>"></i> <?= htmlspecialchars($p['nom_plat']) ?></label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                    <button type="submit" class="btn-submit"><i class="fa-solid fa-paper-plane"></i> Publier le menu</button>
                </form>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h5><i class="fa-solid fa-list"></i> Menus publiés</h5>
                <span class="table-count"><?= count($menus) ?> menu(s)</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Semaine du</th>
                        <th>Semaine au</th>
                        <th>Publication</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($menus)): ?>
                        <tr class="empty-row"><td colspan="6">Aucun menu publié pour le moment</td></tr>
                    <?php else: ?>
                        <?php foreach ($menus as $m): ?>
                        <tr>
                            <td class="td-muted"><?= $m['id_menu'] ?></td>
                            <td><?= date('d/m/Y', strtotime($m['semaine_du'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($m['semaine_au'])) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($m['date_publication'])) ?></td>
                            <td>
                                <?php
                                $badges = [
                                    'PUBLIE'    => ['badge-publie',    'fa-solid fa-circle-check', 'Publié'],
                                    'BROUILLON' => ['badge-brouillon', 'fa-solid fa-pen',          'Brouillon'],
                                    'ARCHIVE'   => ['badge-archive',   'fa-solid fa-box-archive',  'Archivé'],
                                ];
                                $b = $badges[$m['statut']] ?? ['', '', $m['statut']];
                                ?>
                                <span class="badge <?= $b[0] ?>"><i class="<?= $b[1] ?>"></i> <?= $b[2] ?></span>
                            </td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="changer_statut">
                                    <input type="hidden" name="id_menu" value="<?= $m['id_menu'] ?>">
                                    <select name="statut" class="select-statut" onchange="this.form.submit()">
                                        <option value="BROUILLON" <?= $m['statut']==='BROUILLON'?'selected':'' ?>>Brouillon</option>
                                        <option value="PUBLIE"    <?= $m['statut']==='PUBLIE'?'selected':'' ?>>Publié</option>
                                        <option value="ARCHIVE"   <?= $m['statut']==='ARCHIVE'?'selected':'' ?>>Archivé</option>
                                    </select>
                                </form>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ce menu ?')">
                                    <input type="hidden" name="action" value="supprimer">
                                    <input type="hidden" name="id_menu" value="<?= $m['id_menu'] ?>">
                                    <button type="submit" class="btn-suppr"><i class="fa-solid fa-trash"></i> Supprimer</button>
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