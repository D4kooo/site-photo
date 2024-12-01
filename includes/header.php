<header>
    <!-- Navigation -->
    <section id="header-section">
        <img src="/site-photo/assets/img/logo-CPN.png" id="logo" alt="Logo">

        <nav class="navigation desktop-navigation">
            <ul class="nav-list">
                <li><a href="/site-photo/index.php" class="nav-link current">Accueil</a></li>
                <li><a href="/site-photo/pages/events.php" class="nav-link">Événements</a></li>
                <li><a href="#" class="nav-link">Forum</a></li>
                <li><a href="/site-photo/pages/messagerie.php" class="nav-link">Messagerie</a></li>
            </ul>
        </nav>

        <div id="profile-container">
            <?php if (isset($_SESSION['user_id'])): ?>
                <button id="profile-button">
                    <img src="/site-photo/assets/img/defaultpp.svg" alt="Profil" id="profile-img">
                </button>
                <div id="profile-menu-content">
                    <a href="/site-photo/pages/<?php echo htmlspecialchars($_SESSION['user_pseudo']); ?>">Votre profil</a> 
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <a href="/site-photo/views/admin/dashboard.php">Dashboard Admin</a>
                    <?php endif; ?>
                    <a href="#">Paramètres</a>
                    <a href="/site-photo/auth/logout.php">Se déconnecter</a>
                </div>
            <?php else: ?>
                <button id="profile-button">
                    <img src="/site-photo/assets/img/defaultpp.svg" alt="Profil" id="profile-img">
                </button>
                <div id="profile-menu-content">
                    <a href="/site-photo/auth/login.php">Se connecter</a>
                    <a href="#">Paramètres</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="icon" id="menu-icon">
            <div class="bar bar1"></div>
            <div class="bar bar2"></div>
            <div class="bar bar3"></div>
        </div>
    </section>

    <!-- Navigation pour mobile -->
    <nav class="navigation mobile-navigation">
        <ul class="nav-list-mobile">
            <li><a href="/site-photo/index.php" class="nav-link current">Accueil</a></li>
            <li><a href="/site-photo/pages/events.php" class="nav-link">Événements</a></li>
            <li><a href="#" class="nav-link">Posts</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="/site-photo/auth/logout.php" class="nav-link">Se déconnecter</a></li>
            <?php else: ?>
                <li><a href="/site-photo/auth/login.php" class="nav-link">Se connecter</a></li>
                <li><a href="/site-photo/auth/register.php" class="nav-link">S'enregistrer</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>