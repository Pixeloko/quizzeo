<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("./config/config.php");
require_once("./includes/functions.php");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>   
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

<header>
    <nav>
        <div>
            <ul>
                <li><a href="./index.php">Accueil</a></li>

                <?php if (isset($_SESSION["user_id"])): ?>
                    <?php if ($_SESSION["role"] === 'admin'): ?>
                        <li><a href="./admin.php">Espace Admin</a></li>
                    <?php elseif ($_SESSION["role"] === 'ecole'): ?>
                        <li><a href="./ecole.php">Espace École</a></li>
                    <?php elseif ($_SESSION["role"] === 'entreprise'): ?>
                        <li><a href="./entreprise.php">Espace Entreprise</a></li>
                    <?php else: ?>
                        <li><a href="./dashboard.php">Dashboard</a></li>
                    <?php endif; ?>

                    <li><a href="./profile.php">Profil</a></li>
                    <li><a href="./logout.php">Déconnexion</a></li>

                <?php else: ?>
                    <li><a href="./login.php">Connexion</a></li>
                <?php endif; ?>
                
            </ul>
        </div>
    </nav>
</header>

