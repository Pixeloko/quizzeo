<?php
// Controller/store_entreprise.php

session_start();

// Vérifier l'utilisateur
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "entreprise") {
    $_SESSION["error"] = "Accès non autorisé";
    header('Location: /quizzeo/?url=login');
    exit;
}

// Inclure les modèles
require_once __DIR__ . '/../Model/function_quizz.php';
require_once __DIR__ . '/../Model/function_question.php';
require_once __DIR__ . '/../Model/function_quizz_question.php';

$errors = [];
$formData = $_POST ?? [];

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION["error"] = "Méthode non autorisée";
    header('Location: /quizzeo/?url=entreprise/create');
    exit;
}

// Validation du nom
$name = trim($_POST['name'] ?? '');
if (empty($name)) {
    $errors['name'] = "Le nom du quiz est obligatoire";
} elseif (strlen($name) < 3) {
    $errors['name'] = "Le nom doit contenir au moins 3 caractères";
}

// Validation des questions
$questions = $_POST['questions'] ?? [];
if (empty($questions)) {
    $errors['questions'] = "Ajoutez au moins une question";
} else {
    foreach ($questions as $index => $q) {
        $title = trim($q['title'] ?? '');
        $type  = $q['type'] ?? 'qcm';

        if (empty($title)) {
            $errors["question_$index"] = "La question " . ($index + 1) . " est vide";
        }

        // Pour les QCM, vérifier qu'il y a au moins 2 réponses
        if ($type === 'qcm') {
            $answers = $q['answers'] ?? [];
            $validAnswers = array_filter($answers, fn($a) => !empty(trim($a['text'] ?? '')));
            if (count($validAnswers) < 2) {
                $errors["question_{$index}_answers"] = "Ajoutez au moins deux réponses pour la question " . ($index + 1);
            }
        }
    }
}

// Si erreurs, renvoyer au formulaire
if (!empty($errors)) {
    $_SESSION["form_errors"] = $errors;
    $_SESSION["form_data"] = $_POST;
    header('Location: /quizzeo/?url=entreprise/create');
    exit;
}

try {
    $user_id = $_SESSION['user_id'];

    // Créer le quiz
    $quiz_id = createQuizz($name, $user_id);
    if (!$quiz_id) throw new Exception("Erreur lors de la création du quiz");

    // Créer les questions
    foreach ($questions as $q) {
        $title = trim($q['title'] ?? '');
        $type  = $q['type'] ?? 'qcm';

        $question_id = createQuestionEntreprise($quiz_id, $title, $type);
        if (!$question_id) throw new Exception("Erreur lors de la création de la question");

        // Pour les QCM, créer les réponses possibles
        if ($type === 'qcm') {
            foreach ($q['answers'] as $answer) {
                $text = trim($answer['text'] ?? '');
                if (!empty($text)) {
                    $res = addAnswerToQuestion($question_id, $text, false); // false car pas de bonne réponse
                    if (!$res) throw new Exception("Erreur lors de l'ajout d'une réponse");
                }
            }
        }
    }

    $_SESSION["success"] = "Quiz créé avec succès !";
    header('Location: /quizzeo/?url=entreprise');
    exit;

} catch (Exception $e) {
    $_SESSION["error"] = "Erreur : " . $e->getMessage();
    $_SESSION["form_data"] = $_POST;
    header('Location: /quizzeo/?url=entreprise/create');
    exit;
}
