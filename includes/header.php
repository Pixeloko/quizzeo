<?php
// Démarre la session si nécessaire
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure la configuration et fonctions
require_once("./config/config.php");
require_once("./includes/functions.php");

?>

<link rel="stylesheet" href="assets/css/styles.css">

<nav>
    <div class="nav-container">
        <!-- Menu -->
        <ul class="header-menu"> 
            <li><a href="./index.php">Accueil</a></li>

            <?php if (isset($_SESSION["user_id"])): ?>
                <li><a href="./dashboard.php" >Dashboard</a></li>
                <li><a href="./profile.php" >Profil</a></li>
                <li><a href="./logout.php">Déconnexion</a></li>
            <?php else: ?>
                <li><a href="./login.php" >Connexion</a></li>
            <?php endif; ?>
            </ul>
    </div>
</nav>
