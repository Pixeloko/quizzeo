<?php
// Démarre la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure la configuration
require_once __DIR__ . '/../../config/config.php';

// Définir la base URL
if (!defined('BASE_URL')) {
    define('BASE_URL', '/quizzeo/');
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quizzeo</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>

<header>
    <nav>
        <div class="nav-container">
            <!-- Logo -->
            <a href="<?= BASE_URL ?>/index.php" class="logo">
                <img src="<?= BASE_URL ?>/assets/logo.png" alt="Logo Quizzeo">
            </a>
            
            <div>
                <ul>
                    <li><a href="<?= BASE_URL ?>/index.php">Accueil</a></li>

                    <?php if (isset($_SESSION["user_id"])): ?>
                        <?php if (isset($_SESSION["role"]) && $_SESSION["role"] === 'admin'): ?>
                            <li><a href="<?= BASE_URL ?>/View/admin.php">Espace Admin</a></li>
                        <?php elseif (isset($_SESSION["role"]) && ($_SESSION["role"] === 'ecole')): ?>
                            <li><a href="<?= BASE_URL ?>View/ecole/dashboard.php">Espace École</a></li>
                        <?php elseif (isset($_SESSION["role"]) && ($_SESSION["role"] === 'entreprise')): ?>
                            <li><a href="<?= BASE_URL ?>View/entreprise/dashboard.php">Espace Entreprise</a></li>
                        <?php elseif (isset($_SESSION["role"]) && ($_SESSION["role"] === 'user')): ?>
                            <li><a href="<?= BASE_URL ?>View/user/dashboard.php">Votre espace</a></li>
                        <?php endif; ?>
                        
                        <li><a href="<?= BASE_URL ?>/View/profile.php">Profil</a></li>
                        <li><a href="<?= BASE_URL ?>/index.php?url=logout">Déconnexion</a></li>
                    <?php else: ?>
                        <li><a href="<?= BASE_URL ?>/View/login.php">Connexion</a></li>
                    <?php endif; ?>

                    <!-- Bouton mode sombre -->
                    <button id="theme-toggle" class="theme-btn">🌙</button>
            </div>
        </div>
    </nav>

    <script>
    const btn = document.getElementById("theme-toggle");

    // Charger le thème sauvegardé
    if (localStorage.getItem("theme") === "dark") {
        document.body.classList.add("dark-theme");
        btn.textContent = "☀️";
    }

    btn.addEventListener("click", () => {
        document.body.classList.toggle("dark-theme");
        if (document.body.classList.contains("dark-theme")) {
            localStorage.setItem("theme", "dark");
            btn.textContent = "☀️";
        } else {
            localStorage.setItem("theme", "light");
            btn.textContent = "🌙";
        }
    });
    </script>

</header>
