<?php
require_once __DIR__ . "/../Model/function_quizz.php";
require_once __DIR__ . "/../Model/function_user.php";

if (!isset($_SESSION["user_id"]) || !in_array($_SESSION["role"], ["ecole", "entreprise"])) {
    header("Location: ../View/login.php");
    exit;
}

$errors = [];
$name = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");

    if (!$name) {
        $errors["name"] = "Titre du quiz requis";
    }

    if (empty($errors)) {
        try {
            $quizz_id = createQuizz($name, $_SESSION["user_id"]);
            $_SESSION["message"] = "Quiz créé avec succès !";
            header("Location: create_question.php?quizz_id=" . $quizz_id);
            exit;
        } catch (PDOException $e) {
            $errors["name"] = "Erreur : " . $e->getMessage();
        }
    }
}
