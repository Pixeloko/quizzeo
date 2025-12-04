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

// Connexion PDO
$pdo = getDatabase();

// Photo de profil par défaut
$profilePhoto = BASE_URL . "assets/default-profile.png";

if (isset($_SESSION["user_id"])) {
    $stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE id = :id");
    $stmt->execute(['id' => $_SESSION["user_id"]]);
    $userPhoto = $stmt->fetch();
    if ($userPhoto && !empty($userPhoto['profile_photo'])) {
        $profilePhoto = BASE_URL . "uploads/profile_photos/" . $userPhoto['profile_photo'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quizzeo</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/style.css">
    <style>
        .header-profile-img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
            vertical-align: middle;
        }
        .header-right {
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>

<header>
    <nav>
        <div class="nav-container">
            <!-- Logo -->
            <a href="<?= BASE_URL ?>index.php" class="logo">
                <img src="<?= BASE_URL ?>assets/logo.png" alt="Logo Quizzeo">
            </a>

            <div>
                <ul>
                    <li><a href="<?= BASE_URL ?>index.php">Accueil</a></li>

                    <?php if (isset($_SESSION["user_id"])): ?>
                        <?php if (isset($_SESSION["role"]) && $_SESSION["role"] === 'admin'): ?>
                            <li><a href="<?= BASE_URL ?>View/admin.php">Espace Admin</a></li>
                        <?php elseif (isset($_SESSION["role"]) && $_SESSION["role"] === 'ecole'): ?>
                            <li><a href="<?= BASE_URL ?>View/ecole/dashboard.php">Espace École</a></li>
                        <?php elseif (isset($_SESSION["role"]) && $_SESSION["role"] === 'entreprise'): ?>
                            <li><a href="<?= BASE_URL ?>View/entreprise/dashboard.php">Espace Entreprise</a></li>
                        <?php elseif (isset($_SESSION["role"]) && ($_SESSION["role"] === 'user')): ?>
                            <li><a href="<?= BASE_URL ?>View/user/dashboard.php">Votre espace</a></li>
                        <?php endif; ?>
                        <li><a href="<?= BASE_URL ?>View/profile.php">Profil</a></li>
                        <li><a href="<?= BASE_URL ?>index.php?url=logout">Déconnexion</a></li>
                    <?php else: ?>
                        <li><a href="<?= BASE_URL ?>View/login.php">Connexion</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Photo profil + mode sombre -->
            <div class="header-right">
                <?php if (isset($_SESSION["user_id"])): ?>
                    <img src="<?= $profilePhoto ?>" alt="Profil" class="header-profile-img">
                <?php endif; ?>
                <button id="theme-toggle" class="theme-btn">🌙</button>
            </div>
        </div>
    </nav>

    <script>
        const btn = document.getElementById("theme-toggle");

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
