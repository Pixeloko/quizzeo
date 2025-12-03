<?php
// Démarre la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure la configuration si nécessaire
require_once __DIR__ . '/../../config/config.php';

// Définir la base URL du projet
define('BASE_URL', '/quizzeo'); // adapte selon le nom de ton dossier

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quizzeo</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/View/assets/css/style.css">
</head>
<body>

<header>
    <nav>
        <div>
            <ul>
                <!-- Lien vers l'accueil -->
                <li><a href="<?= BASE_URL ?>/index.php">Accueil</a></li>

                <?php if (isset($_SESSION["user_id"])): ?>

                    <?php if (isset($_SESSION["role"]) && $_SESSION["role"] === 'admin'): ?>
                        <li><a href="<?= BASE_URL ?>/View/admin.php">Espace Admin</a></li>
                    <?php elseif (isset($_SESSION["role"]) && ($_SESSION["role"] === 'ecole' || $_SESSION["role"] === 'entreprise')): ?>
                        <li><a href="<?= BASE_URL ?>/View/dashboard_e.php">Espace École</a></li>
                    <?php endif; ?>
                    
                    <li><a href="<?= BASE_URL ?>/View/profile.php">Profil</a></li>
                    <li><a href="<?= BASE_URL ?>/index.php?url=logout">Déconnexion</a></li>


                <?php else: ?>
                    <li><a href="<?= BASE_URL ?>/View/login.php">Connexion</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
</header>
