<?php
// Controller/submit_quiz.php

session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "user") {
    header("Location: /quizzeo/?url=login");
    exit;
}

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /quizzeo/?url=user");
    exit;
}

// Récupérer l'ID du quiz
$quiz_id = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : 0;
if ($quiz_id <= 0) {
    $_SESSION['error'] = "Quiz invalide";
    header("Location: /quizzeo/?url=user");
    exit;
}

// Charger les modèles
require_once __DIR__ . '/../Model/function_quizz.php';
require_once __DIR__ . '/../Model/function_question.php';
require_once __DIR__ . '/../Model/function_user.php';

// Vérifier que le quiz existe et est actif
$quiz = getQuizzById($quiz_id);
if (!$quiz || $quiz['status'] !== 'launched' || !$quiz['is_active']) {
    $_SESSION['error'] = "Ce quiz n'est pas disponible";
    header("Location: /quizzeo/?url=user");
    exit;
}

// Récupérer l'utilisateur
$user_id = $_SESSION['user_id'];

// Vérifier si l'utilisateur a déjà répondu à ce quiz
$pdo = getConnexion();
$sql_check = "SELECT id FROM quizz_user WHERE quizz_id = :quiz_id AND user_id = :user_id";
$stmt_check = $pdo->prepare($sql_check);
$stmt_check->execute(['quiz_id' => $quiz_id, 'user_id' => $user_id]);
$existing_attempt = $stmt_check->fetch();

if ($existing_attempt) {
    // L'utilisateur a déjà répondu, on peut soit rediriger, soit permettre de modifier
    $_SESSION['error'] = "Vous avez déjà répondu à ce quiz";
    header("Location: /quizzeo/?url=user&action=quiz_results&id=" . $quiz_id);
    exit;
}

// Traiter les réponses
$total_points = 0;
$earned_points = 0;
$total_questions = 0;
$answers_data = [];

// Récupérer toutes les questions du quiz
$questions = getQuestionsByQuizzId($quiz_id);

foreach ($questions as $question) {
    $question_id = $question['id'];
    $answer_key = "question_" . $question_id;
    
    // Récupérer la réponse de l'utilisateur
    $selected_answer_id = isset($_POST[$answer_key]) ? (int)$_POST[$answer_key] : 0;
    
    // Récupérer toutes les réponses pour cette question
    $answers = getAnswersByQuestion($question_id);
    
    // Trouver la bonne réponse et vérifier si l'utilisateur a bien répondu
    $is_correct = false;
    $selected_answer_text = null;
    $correct_answer_text = null;
    
    foreach ($answers as $answer) {
        if ($answer['id'] == $selected_answer_id) {
            $selected_answer_text = $answer['answer_text'];
        }
        if ($answer['is_correct'] == 1) {
            $correct_answer_text = $answer['answer_text'];
            if ($answer['id'] == $selected_answer_id) {
                $is_correct = true;
                $earned_points += $question['point'];
            }
        }
    }
    
    $total_points += $question['point'];
    $total_questions++;
    
    // Stocker les données de la réponse
    $answers_data[] = [
        'question_id' => $question_id,
        'question_text' => $question['title'],
        'selected_answer_id' => $selected_answer_id,
        'selected_answer_text' => $selected_answer_text,
        'correct_answer_text' => $correct_answer_text,
        'is_correct' => $is_correct,
        'question_points' => $question['point']
    ];
}

// Calculer le score
$score_percentage = $total_points > 0 ? round(($earned_points / $total_points) * 100, 1) : 0;

// Enregistrer l'essai du quiz
try {
    $pdo->beginTransaction();
    
    // 1. Enregistrer l'essai dans quizz_user
    $sql_attempt = "INSERT INTO quizz_user (quizz_id, user_id, score, completed_at) 
                    VALUES (:quiz_id, :user_id, :score, NOW())";
    $stmt_attempt = $pdo->prepare($sql_attempt);
    $stmt_attempt->execute([
        'quiz_id' => $quiz_id,
        'user_id' => $user_id,
        'score' => $earned_points
    ]);
    
    $quizz_user_id = $pdo->lastInsertId();
    
    // 2. Enregistrer chaque réponse dans user_answers
    foreach ($answers_data as $answer) {
        $sql_answer = "INSERT INTO user_answers (quizz_user_id, question_id, answer_id, is_correct) 
                       VALUES (:quizz_user_id, :question_id, :answer_id, :is_correct)";
        $stmt_answer = $pdo->prepare($sql_answer);
        $stmt_answer->execute([
            'quizz_user_id' => $quizz_user_id,
            'question_id' => $answer['question_id'],
            'answer_id' => $answer['selected_answer_id'],
            'is_correct' => $answer['is_correct'] ? 1 : 0
        ]);
    }
    
    $pdo->commit();
    
    // Stocker les résultats en session pour la page de résultats
    $_SESSION['quiz_results'] = [
        'quiz_id' => $quiz_id,
        'quiz_name' => $quiz['name'],
        'total_questions' => $total_questions,
        'total_points' => $total_points,
        'earned_points' => $earned_points,
        'score_percentage' => $score_percentage,
        'answers' => $answers_data,
        'completed_at' => date('Y-m-d H:i:s')
    ];
    
    $_SESSION['success'] = "Quiz terminé ! Votre score est de $earned_points/$total_points points ($score_percentage%)";
    
    // Rediriger vers la page de résultats
    header("Location: /quizzeo/?url=user&action=quiz_results&id=" . $quiz_id);
    exit;
    
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Erreur lors de l'enregistrement des réponses: " . $e->getMessage();
    header("Location: /quizzeo/?url=quiz&id=" . $quiz_id);
    exit;
}