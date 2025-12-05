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
    if (function_exists('hasUserAnsweredQuiz') && hasUserAnsweredQuiz($_SESSION['user_id'], $quiz_id)) {
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
    <title><?= htmlspecialchars($quiz['name'] ?? 'Quiz') ?> - Quizzeo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    /* (garde ton CSS existant — abrégé ici pour la lisibilité) */
    :root { --primary-color: #8e79b2; }
    body { background-color: #ffffff; font-family: system-ui, sans-serif; }
    .container { max-width: 900px; }
    .card { border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.06); margin-bottom: 1.2rem; }
    .question-card { border: 1px solid var(--primary-color); border-radius: 8px; }
    .answer-option { padding: .9rem; border-radius: 6px; margin-bottom: .6rem; border: 1px solid var(--primary-color); }
    </style>
</head>

<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><?= htmlspecialchars($quiz['name'] ?? '') ?></h1>
            <a href="/quizzeo/?url=home" class="btn btn-outline-secondary">← Retour</a>
        </div>

        <div class="card">
            <div class="card-body">

                <form method="POST" action="/quizzeo/Controller/submit_quiz.php" id="quiz-form">
                    <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">

                    <?php foreach ($questions as $index => $question): ?>
                        <?php
                            // sécurité : s'assurer que $question est bien un tableau
                            if (!is_array($question)) continue;

                            // récupérer les réponses (déjà incluses si getQuestionsByQuizzId le fait,
                            // sinon appeler getAnswersByQuestion)
                            $answers = $question['answers'] ?? [];
                            $qName = 'question_' . (int)$question['id'];
                        ?>
                    <div class="question-card card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                Question <?= $index + 1 ?>
                                <span class="badge bg-primary float-end"><?= (int)($question['point'] ?? 1) ?> point(s)</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="fw-bold mb-3"><?= htmlspecialchars($question['title'] ?? '') ?></p>

                            <?php if (!empty($answers)): ?>
                                <!-- QCM : on met required sur le premier input radio du groupe via JS (voir plus bas) -->
                                <?php foreach ($answers as $i => $answer): ?>
                                    <div class="answer-option">
                                        <div class="form-check">
                                            <input class="form-check-input"
                                                   type="radio"
                                                   name="<?= $qName ?>"
                                                   value="<?= (int)($answer['id'] ?? 0) ?>"
                                                   id="answer_<?= (int)($answer['id'] ?? 0) ?>">
                                            <label class="form-check-label w-100" for="answer_<?= (int)($answer['id'] ?? 0) ?>">
                                                <?= htmlspecialchars($answer['answer_text'] ?? '') ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                            <?php else: ?>
                                <!-- RÉPONSE LIBRE -->
                                <div class="mb-2">
                                    <textarea class="form-control"
                                              name="<?= $qName ?>"
                                              rows="3"
                                              placeholder="Écrivez votre réponse..."
                                              required></textarea>
                                </div>
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

    <!-- Bootstrap Icons & JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // Rendre required le premier radio de chaque groupe QCM afin que le groupe soit obligatoire
    // (HTML ne supporte pas required sur groupe de radio directement via nom)
    (function() {
        const questions = <?= json_encode(array_map(function($q){ return (int)$q['id']; }, $questions)); ?>;
        questions.forEach(function(qid) {
            const radios = document.querySelectorAll('input[name="question_' + qid + '"][type="radio"]');
            if (radios.length > 0) {
                // marquer le premier radio comme required ; si l'utilisateur choisit un autre, c'est ok
                radios[0].required = true;
            }
        });
    })();
    </script>
</body>

</html>
<?php
    exit;
} else {
    // MODE 2 : liste des quiz
    require_once __DIR__ . '/../Model/function_quizz.php';
    $quizzes = getActiveQuizz();
    ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Quiz - Quizzeo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <h1>Liste des Quiz Disponibles</h1>
        <?php foreach ($quizzes as $quiz): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h5><?= htmlspecialchars($quiz['title'] ?? $quiz['name'] ?? '') ?></h5>
                <a href="?url=quiz&id=<?= (int)($quiz['quizz_id'] ?? $quiz['id'] ?? 0) ?>" class="btn btn-primary">
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
