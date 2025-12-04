<?php
session_start();
require_once __DIR__ . "/../Model/function_question.php";
require_once __DIR__ . "/../Model/function_quizz.php"; 

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['ecole', 'entreprise'])) {
    header("Location: ../View/login.php");
    exit;
}


$quizz_id = (int)($_GET['quizz_id'] ?? 0);
if ($quizz_id <= 0) {
    $_SESSION['error'] = "Quiz invalide.";
    header("Location: ../Controller/dashboard_pro.php");
    exit;
}

$quizz = getQuizzById($quizz_id);
if (!$quizz) {
    $_SESSION['error'] = "Quiz introuvable.";
    header("Location: ../Controller/dashboard_pro.php");
    exit;
}


$errors = [];
$question_text = "";
$point = 1;
$answers = ["", "", "", ""];
$correct_index = null;

// Traitement du POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $question_text = trim($_POST['question_text'] ?? '');
    $point = (int)($_POST['point'] ?? 1);

    for ($i = 0; $i < 4; $i++) {
        $answers[$i] = trim($_POST["answer{$i}"] ?? '');
    }

    $correct_index = isset($_POST['correct']) ? (int)$_POST['correct'] : null;

    if ($title === '') {
        $errors['title'] = "Le texte de la question est requis.";
    }

    foreach ($answers as $i => $ans) {
        if ($ans === '') {
            $errors["answer{$i}"] = "La réponse " . ($i + 1) . " est requise.";
        }
    }

    if ($correct_index === null || !in_array($correct_index, [0,1,2,3], true)) {
        $errors['correct'] = "Veuillez sélectionner la bonne réponse.";
    }

    if (empty($errors)) {
        try {
            $question_id = createQuestion($quizz_id, $title, $point);

            // Ajouter les 4 réponses et marquer la bonne
            for ($i = 0; $i < 4; $i++) {
                $is_correct = ($i === $correct_index);
                addAnswerToQuestion($question_id, $answers[$i], $is_correct);
            }

            $_SESSION['message'] = "Question et réponses ajoutées avec succès !";
            header("Location: /../View/create_question.php?quizz_id={$quizz_id}");
            exit;

        } catch (PDOException $e) {
            $errors['general'] = "Erreur lors de l'ajout : " . $e->getMessage();
        }
    }
}

