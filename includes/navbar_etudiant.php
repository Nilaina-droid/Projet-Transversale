<nav class="navbar">
    <div class="nav-brand">
        <span class="brand-emoji">🍽</span>
        <span>Restaurant Universitaire - Etudiant</span>
    </div>
    <ul class="nav-links">
        <li><a href="/GESTION_RU/espace_etudiant/dashboard.php">Tableau de bord</a></li>
        <li><a href="/GESTION_RU/espace_etudiant/mes_reservations.php">Réservations</a></li>
    </ul>
    <div class="nav-user">
        <span class="user-name">👋 <?= htmlspecialchars($_SESSION['prenom'] ?? 'Etudiant') ?></span>
        <a href="/GESTION_RU/logout.php" class="btn-logout">Déconnexion</a>
    </div>
</nav>