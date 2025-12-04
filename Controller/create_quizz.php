<?php
if(session_status()==PHP_SESSION_NONE){
    session_start();
}

// Vérifier la session
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "ecole") {
    header("Location: ../View/login.php");
    exit;
}

// Inclure les modèles nécessaires
require_once __DIR__ . "/../Model/function_quizz.php";
require_once __DIR__ . "/../Model/function_question.php";
require_once __DIR__ . "/../Model/function_user.php";

// Initialiser les variables
$errors = [];
$success = false;
$quizData = [
    'name' => '',
    'questions' => []
];

// Si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Récupérer et valider le nom du quiz
    $name = trim($_POST["name"] ?? "");
    
    // Validation du nom
    if (empty($name)) {
        $errors["name"] = "Le nom du quiz est obligatoire";
    } elseif (strlen($name) < 3) {
        $errors["name"] = "Le nom doit contenir au moins 3 caractères";
    } elseif (strlen($name) > 100) {
        $errors["name"] = "Le nom ne peut pas dépasser 100 caractères";
    }
    
    // Récupérer et valider les questions
    $questions = $_POST["questions"] ?? [];
    
    if (empty($questions)) {
        $errors["questions"] = "Au moins une question est requise";
    } else {
        foreach ($questions as $index => $question) {
            $questionText = trim($question['title'] ?? '');
            $point = (int)($question['point'] ?? 1);
            $correctAnswer = (int)($question['correct_answer'] ?? -1);
            $answers = $question['answers'] ?? [];
            
            // Validation de la question
            if (empty($questionText)) {
                $errors["question_{$index}"] = "La question " . ($index + 1) . " est requise";
            }
            
            // Validation des points
            if ($point < 1 || $point > 10) {
                $errors["point_{$index}"] = "Les points doivent être entre 1 et 10 pour la question " . ($index + 1);
            }
            
            // Validation des réponses
            $hasAnswers = false;
            foreach ($answers as $ansIndex => $answer) {
                if (!empty(trim($answer['text'] ?? ''))) {
                    $hasAnswers = true;
                    break;
                }
            }
            
            if (!$hasAnswers) {
                $errors["answers_{$index}"] = "Au moins une réponse est requise pour la question " . ($index + 1);
            }
            
            // Validation de la bonne réponse
            if ($correctAnswer < 0 || $correctAnswer >= count($answers)) {
                $errors["correct_{$index}"] = "Veuillez sélectionner une bonne réponse pour la question " . ($index + 1);
            }
            
            // Stocker les données pour réaffichage
            $quizData['questions'][$index] = [
                'title' => $questionText,
                'point' => $point,
                'correct_answer' => $correctAnswer,
                'answers' => $answers
            ];
        }
    }
    
    // Si pas d'erreurs, créer le quiz
    if (empty($errors)) {
        try {
            // Créer le quiz
            $quiz_id = createQuizz($name, $_SESSION["user_id"]);
            
            // Créer les questions et réponses
            foreach ($questions as $question) {
                $questionText = trim($question['title']);
                $point = (int)$question['point'];
                $correctAnswer = (int)$question['correct_answer'];
                $answers = $question['answers'];
                
                $question_id = createQuestion($quiz_id, $questionText, $point);
                
                foreach ($answers as $index => $answer) {
                    $answerText = trim($answer['text']);
                    if (!empty($answerText)) {
                        $is_correct = ($index == $correctAnswer);
                        addAnswerToQuestion($question_id, $answerText, $is_correct);
                    }
                }
            }
            
            $_SESSION["success_message"] = "Quiz créé avec succès !";
            header("Location: /quizzeo/?url=ecole");
            exit;
            
        } catch (Exception $e) {
            $errors["general"] = "Erreur lors de la création du quiz : " . $e->getMessage();
        }
    } else {
        // Stocker les données pour réaffichage
        $quizData['name'] = $name;
    }
}

require_once __DIR__ . "/../View/ecole/create_quizz.php";