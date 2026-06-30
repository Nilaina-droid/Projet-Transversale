<nav class="navbar">
    <div class="nav-brand">
        <span class="brand-emoji">🍽</span>
        <span>Restaurant Universitaire</span>
    </div>
    <ul class="nav-links">
        <li><a href="/GESTION_RU/espace_gestionnaire/dashboard_admin.php">Tableau de bord</a></li>
        <li><a href="/GESTION_RU/espace_gestionnaire/menus.php">Menus</a></li>
    </ul>
    <div class="nav-user">
        <span>👋 <?= htmlspecialchars($_SESSION['identifiant'] ?? 'Admin') ?></span>
        <a href="/GESTION_RU/logout.php" class="btn-logout">Déconnexion</a>
    </div>
</nav>