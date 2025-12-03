<?php
// Démarre la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure la configuration
require_once __DIR__ . '/../../config/config.php';

// Définir la base URL
define('BASE_URL', '/quizzeo'); // adapte selon le nom de ton dossier

$pdo = getDatabase();

// Récupérer la photo de profil si l'utilisateur est connecté
$profilePhoto = null;
if (isset($_SESSION["user_id"])) {
    $stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE id = :id");
    $stmt->execute(['id' => $_SESSION["user_id"]]);
    $userPhoto = $stmt->fetch();
    if ($userPhoto && !empty($userPhoto['profile_photo'])) {
        $profilePhoto = BASE_URL . "/uploads/" . $userPhoto['profile_photo'];
    } else {
        // photo par défaut si l'utilisateur n'a pas de photo
        $profilePhoto = BASE_URL . "/assets/default-profile.png";
    }
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
                        <?php elseif (isset($_SESSION["role"]) && ($_SESSION["role"] === 'ecole' || $_SESSION["role"] === 'entreprise')): ?>
                            <li><a href="<?= BASE_URL ?>/View/dashboard_pro.php">Espace École</a></li>
                        <?php endif; ?>
                        
                        <li><a href="<?= BASE_URL ?>/View/profile.php">Profil</a></li>
                        <li><a href="<?= BASE_URL ?>/index.php?url=logout">Déconnexion</a></li>
                    <?php else: ?>
                        <li><a href="<?= BASE_URL ?>/View/login.php">Connexion</a></li>
                    <?php endif; ?>

                    <!-- Bouton mode sombre -->
                    <button id="theme-toggle" class="theme-btn">🌙</button>

                    <!-- Photo de profil -->
                    <?php if ($profilePhoto): ?>
                        <img src="<?= $profilePhoto ?>" alt="Photo de profil" class="header-profile-photo">
                    <?php endif; ?>
                </ul>
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
