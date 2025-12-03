<?php
session_start();
require_once __DIR__ . "/../Model/function_user.php";
require_once __DIR__ . "/../Model/function_quizz.php";

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION["user_id"];

$user = getUserById($userId);

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Vérifier le rôle ➜ accès réservé aux entreprises
if ($user["role"] !== "entreprise" || $user["role"] !== "ecole") {
    $_SESSION["error"] = "Accès interdit.";
    header("Location: index.php");
    exit;
}

// Récupération des quizz créés par le pro (à adapter quand tes fonctions seront prêtes)
$quizzes = getQuizzesByUserId($userId) ?? [];
