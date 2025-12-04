<?php
// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Charger les fonctions
require_once __DIR__ . '/../Model/function_quizz.php';
require_once __DIR__ . '/../Model/function_quizz_question.php';
require_once __DIR__ . '/../Model/function_question.php';

// Récupérer l'ID du quizz depuis l'URL
if (isset($_GET['id'])) {
    $quizz_id = (int) $_GET['id'];
} else {
    echo "Aucun quizz indiqué.";
    exit;
}

// Récupération du quizz
$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($quiz_id <= 0) {
    $_SESSION['error'] = "Quiz non trouvé";
    header("Location: /quizzeo/?url=user");
    exit;
}

$quizz = getQuizzById($quiz_id);

if (!$quizz) {
    echo "<h2>Quizz introuvable.</h2>";
    exit;
}

// Récupération des questions
$questions = getQuestionsByQuizzId($quizz_id);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($quizz['title']) ?></title>
    
</head>
<body>

<?php if (!empty($quizz['description'])): ?>
    <p><?= htmlspecialchars($quizz['description']) ?></p>
<?php endif; ?>

<form action="?url=submit_quiz" method="POST" id="quiz-form">
    <!-- ID du quizz -->
    <input type="hidden" name="quizz_id" value="<?= $quizz_id ?>">

    <?php if (!empty($questions)): ?>
        <?php foreach ($questions as $index => $question): ?>
            <div class="question">
                <h3>Question <?= $index + 1 ?> : <?= htmlspecialchars($question['title']) ?></h3>

                <?php 
                $answers = getAnswersByQuestion((int)$question['id']);
                ?>

                <?php if (!empty($answers)): ?>
                    <?php foreach ($answers as $answer): ?>
                        <label>
                            <input 
                                type="radio" 
                                name="question_<?= $question['id'] ?>"
                                value="<?= $answer['id'] ?>"
                                required
                            >
                            <?= htmlspecialchars($answer['answer_text']) ?>
                        </label>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-answers">Aucune réponse pour cette question.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucune question dans ce quizz.</p>
    <?php endif; ?>

    <button type="submit">Valider mes réponses</button>
</form>

</body>
</html>
