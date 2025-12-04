<?php
session_start();
require_once __DIR__ . "/../Model/function_quizz.php";
require_once __DIR__ . "/../Model/function_question.php";

// Vérifier si la requête est POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /quizzeo/?url=home");
    exit;
}

// Récupérer l'ID du quiz
$quiz_id = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : 0;
if ($quiz_id <= 0) {
    $_SESSION['error'] = "Quiz invalide";
    header("Location: /quizzeo/?url=home");
    exit;
}

// Récupérer le quiz
$quiz = getQuizzById($quiz_id);
if (!$quiz) {
    $_SESSION['error'] = "Quiz non trouvé";
    header("Location: /quizzeo/?url=home");
    exit;
}

// Vérifier si le quiz est actif ou si l'utilisateur est le propriétaire
$can_play = $quiz['is_active'];
if (!$can_play && isset($_SESSION['user_id']) && $_SESSION['role'] === 'ecole') {
    if ($quiz['user_id'] == $_SESSION['user_id']) {
        $can_play = true; // Le propriétaire peut tester son quiz même s'il n'est pas public
    }
}

if (!$can_play) {
    $_SESSION['error'] = "Ce quiz n'est pas disponible pour le moment";
    header("Location: /quizzeo/?url=home");
    exit;
}

// Récupérer les questions du quiz
$questions = getQuestionsByQuizzId($quiz_id);
if (empty($questions)) {
    $_SESSION['error'] = "Ce quiz n'a pas encore de questions";
    header("Location: /quizzeo/View/quiz/start_quiz.php?id=" . $quiz_id);
    exit;
}

// Mélanger les questions si le quiz le permet
if ($quiz['shuffle_questions']) {
    shuffle($questions);
}

// Initialiser la session du quiz
$_SESSION['quiz_session'] = [
    'quiz_id' => $quiz_id,
    'quiz_name' => $quiz['name'],
    'player_name' => isset($_POST['player_name']) ? trim($_POST['player_name']) : '',
    'start_time' => time(),
    'questions' => [],
    'current_question' => 0,
    'answers' => [],
    'score' => 0,
    'total_points' => 0,
    'completed' => false
];

// Préparer les questions pour la session
foreach ($questions as $question) {
    $_SESSION['quiz_session']['questions'][] = [
        'id' => $question['id'],
        'title' => $question['title'],
        'point' => $question['point'],
        'answers' => $question['answers']
    ];
    $_SESSION['quiz_session']['total_points'] += $question['point'];
}

// Rediriger vers la première question
header("Location: /quizzeo/View/quiz/play_quiz.php");
exit;