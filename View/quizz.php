<?php
// View/quizz.php
require_once __DIR__ . '/../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// MODE 1 : Jouer à un quiz spécifique (quand il y a ?id=...)
if (isset($_GET['id'])) {
    // Vérifier l'authentification
    if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "user") {
        header("Location: /quizzeo/?url=login");
        exit;
    }
    
    $quiz_id = (int)$_GET['id'];
    
    // Vérifier si l'utilisateur a déjà répondu
    require_once __DIR__ . '/../Model/function_user.php';
    if (hasUserAnsweredQuiz($_SESSION['user_id'], $quiz_id)) {
        $_SESSION['error'] = "Vous avez déjà répondu à ce quiz";
        header("Location: /quizzeo/?url=home");
        exit;
    }
    
    // Récupérer le quiz
    require_once __DIR__ . '/../Model/function_quizz.php';
    $quiz = getQuizzById($quiz_id);
    if (!$quiz) {
        $_SESSION['error'] = "Quiz non trouvé";
        header("Location: /quizzeo/?url=home");
        exit;
    }
    
    // Récupérer les questions
    require_once __DIR__ . '/../Model/function_question.php';
    $questions = getQuestionsByQuizzId($quiz_id);
    
    // Si pas de questions
    if (empty($questions)) {
        $_SESSION['error'] = "Ce quiz n'a pas encore de questions";
        header("Location: /quizzeo/?url=home");
        exit;
    }
    
    // AFFICHER LE QUIZ À JOUER
    ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($quiz['name']) ?> - Quizzeo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    :root {
        --primary-color: #8e79b2;
        --secondary-color: #e76667;
        --accent-color: #fddea7;
        --light-color: #ffffff;
    }

    body {
        background-color: #ffffff;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .container {
        max-width: 800px;
    }

    h1 {
        color: #8e79b2;
        font-weight: 700;
    }

    .card {
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }

    .card-header {
        background-color: #8e79b2;
        color: #ffffff;
        border-bottom: none;
        padding: 1rem 1.5rem;
        border-radius: 8px 8px 0 0;
    }

    .card-header h5 {
        color: #ffffff;
        margin: 0;
        font-weight: 600;
    }

    .card-body {
        background-color: #ffffff;
        padding: 1.5rem;
    }

    .question-card {
        margin-bottom: 2rem;
        border: 1px solid #8e79b2;
        border-radius: 8px;
    }

    .badge.bg-primary {
        background-color: #8e79b2 !important;
    }

    .answer-option {
        margin-bottom: 1rem;
        padding: 1rem;
        border: 1px solid #8e79b2;
        border-radius: 5px;
        background-color: #ffffff;
        transition: all 0.3s;
    }

    .answer-option:hover {
        background-color: rgba(142, 121, 178, 0.05);
        border-color: #7a68a0;
    }

    .form-check-input {
        border: 2px solid #8e79b2;
    }

    .form-check-input:checked {
        background-color: #8e79b2;
        border-color: #8e79b2;
    }

    .form-check-input:focus {
        border-color: #8e79b2;
        box-shadow: 0 0 0 0.2rem rgba(142, 121, 178, 0.25);
    }

    .btn {
        border-radius: 5px;
        font-weight: 500;
        transition: all 0.3s;
    }

    .btn-outline-secondary {
        border-color: #cccccc;
        color: #666666;
    }

    .btn-outline-secondary:hover {
        background-color: #f5f5f5;
        border-color: #bbbbbb;
        color: #666666;
    }

    .btn-primary {
        background-color: #8e79b2;
        border-color: #8e79b2;
        color: #ffffff;
    }

    .btn-primary:hover {
        background-color: #7a68a0;
        border-color: #7a68a0;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(142, 121, 178, 0.3);
    }

    .btn-lg {
        padding: 0.75rem 2rem;
        font-size: 1.1rem;
    }

    .text-muted {
        color: #666666 !important;
    }

    .bi-info-circle {
        color: #8e79b2;
    }

    .fw-bold {
        color: #333333;
    }

    .form-check-label {
        color: #333333;
        cursor: pointer;
    }

    /* Pour la liste des quiz (mode 2) */
    .card.mb-3 {
        border-left: 4px solid #8e79b2;
    }

    .card.mb-3 .card-body h5 {
        color: #8e79b2;
    }

    .card.mb-3 .btn-primary {
        background-color: #8e79b2;
        border-color: #8e79b2;
    }

    .card.mb-3 .btn-primary:hover {
        background-color: #7a68a0;
        border-color: #7a68a0;
    }
    </style>
</head>

<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><?= htmlspecialchars($quiz['name']) ?></h1>
            <a href="/quizzeo/?url=home" class="btn btn-outline-secondary">← Retour</a>
        </div>

        <div class="card">
            <div class="card-body">

                <form method="POST" action="/quizzeo/Controller/submit_quiz.php">
                    <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">

                    <?php foreach ($questions as $index => $question): ?>
                    <div class="question-card card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                Question <?= $index + 1 ?>
                                <span class="badge bg-primary float-end"><?= $question['point'] ?> point(s)</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="fw-bold mb-3"><?= htmlspecialchars($question['title']) ?></p>

                            <?php if (!empty($question['answers'])): ?>
                            <?php foreach ($question['answers'] as $answer): ?>
                            <div class="answer-option">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="question_<?= $question['id'] ?>"
                                        value="<?= $answer['id'] ?>" id="answer_<?= $answer['id'] ?>">
                                    <label class="form-check-label w-100" for="answer_<?= $answer['id'] ?>">
                                        <?= htmlspecialchars($answer['answer_text']) ?>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <p class="text-muted">Aucune réponse disponible</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-send"></i> Soumettre mes réponses
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php
    exit; 
    

} else {
    require_once __DIR__ . '/../Model/function_quizz.php';
    $quizzes = getActiveQuizz();
    ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Quiz - Quizzeo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container py-4">
        <h1>Liste des Quiz Disponibles</h1>
        <?php foreach ($quizzes as $quiz): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h5><?= htmlspecialchars($quiz['title']) ?></h5>
                <a href="?url=quiz&id=<?= $quiz['quizz_id'] ?>" class="btn btn-primary">
                    Jouer ce quiz
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</body>

</html>
<?php
}