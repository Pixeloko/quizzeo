<?php
// View/user/play_quiz.php

// Démarrer la session SI PAS DÉJÀ FAIT dans index.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Activer les erreurs pour debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérifier l'authentification
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "user") {
    $base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/quizzeo';
    header("Location: $base_url/?url=login");
    exit;
}

// Charger les fonctions avec chemins absolus
require_once __DIR__ . '/../../Model/function_quizz.php';
require_once __DIR__ . '/../../Model/function_quizz_question.php';
require_once __DIR__ . '/../../Model/function_question.php';

// Récupérer l'ID du quiz
$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($quiz_id <= 0) {
    $_SESSION['error'] = "Quiz non trouvé";
    header("Location: " . $baseUrl . "/index.php?url=user");
    exit;
}

// Récupération du quiz
$quizz = getQuizzById($quiz_id);
if (!$quizz || $quizz['status'] !== 'launched') {
    $_SESSION['error'] = "Ce quiz n'est pas disponible";
    header("Location: " . $baseUrl . "/index.php?url=user");
    exit;
}

// Récupération des questions
$questions = getQuestionsByQuizzId($quiz_id);
if (empty($questions)) {
    $_SESSION['error'] = "Ce quiz n'a pas de questions";
    header("Location: " . $baseUrl . "/index.php?url=user");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($quizz['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1><?= htmlspecialchars($quizz['name']) ?></h1>
        
        <!-- FORMULAIRE CORRIGÉ -->
        <form action="?url=submit_quiz" method="POST" id="quiz-form">
            <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">
            
            <?php foreach ($questions as $index => $question): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Question <?= $index + 1 ?>: <?= htmlspecialchars($question['title']) ?></h5>
                    
                    <?php 
                    $answers = getAnswersByQuestion((int)$question['id']);
                    ?>
                    
                    <?php if (!empty($answers)): ?>
                        <?php foreach ($answers as $answer): ?>
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="radio" 
                                   name="question_<?= $question['id'] ?>"
                                   value="<?= $answer['id'] ?>"
                                   id="q<?= $question['id'] ?>_a<?= $answer['id'] ?>"
                                   required>
                            <label class="form-check-label" for="q<?= $question['id'] ?>_a<?= $answer['id'] ?>">
                                <?= htmlspecialchars($answer['answer_text']) ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-warning">Aucune réponse disponible</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            
            <button type="submit" class="btn btn-primary btn-lg">Soumettre mes réponses</button>
        </form>
        
        <div class="mt-3">
            <a href="<?php echo $baseUrl; ?>/index.php?url=user" class="btn btn-secondary">Retour au dashboard</a>
        </div>
    </div>
</body>
</html>