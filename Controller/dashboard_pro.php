<?php
require_once __DIR__ . "/../Model/function_user.php";
require_once __DIR__ . "/../Model/function_quizz.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../View/login.php");
    exit;
}

$userId = $_SESSION["user_id"];
$user = getUserById($userId);

if (!in_array($user["role"], ['ecole', 'entreprise'])) {
    $_SESSION["error"] = "Accès interdit.";
    header("Location: ../View/home.php");
    exit;
}

try {
    $quizzes = getQuizzByUserId($userId);
} catch (PDOException $e) {
    $quizzes = [];
    $_SESSION["error"] = "Impossible de récupérer vos quizz :" . $e;
}
