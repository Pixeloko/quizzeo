<?php
// View/quizz.php - CORRECTION

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si un ID est passé
if (!isset($_GET['id'])) {
    // Si pas d'ID, vérifier si c'est une soumission (url=submit_quiz)
    if (isset($_GET['url']) && $_GET['url'] === 'submit_quiz') {
        // C'est une soumission, rediriger vers le contrôleur via index.php
        header("Location: /quizzeo/?url=submit_quiz");
        exit;
    } else {
        echo "Aucun quizz indiqué.";
        exit;
    }
}

// Charger les fonctions
require_once __DIR__ . '/../Model/function_quizz.php';
require_once __DIR__ . '/../Model/function_quizz_question.php';
require_once __DIR__ . '/../Model/function_question.php';

// Récupérer l'ID
$quizz_id = (int)$_GET['id'];

// Récupérer le quiz
$quizz = getQuizzById($quizz_id);
if (!$quizz) {
    echo "<h2>Quizz introuvable.</h2>";
    exit;
}

// Récupérer les questions
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

<!-- LIGNE ~60 - DOIT ÊTRE : -->
<form action="/quizzeo/?url=submit_quiz" method="POST" id="quiz-form">
    <!-- Notez : /quizzeo/?url=submit_quiz -->
    <input type="hidden" name="quiz_id" value="<?= $quizz_id ?>">


    <form action="?url=submit_quiz" method="POST" id="quiz-form">
    <input type="hidden" name="quizz_id" value="<?= $quizz_id ?>">

    <?php if (!empty($questions)): ?>
        <?php foreach ($questions as $index => $question): ?>
            <div class="question">
                <h3>Question <?= $index + 1 ?> : <?= htmlspecialchars($question['title']) ?></h3>

                <?php 
                $answers = getAnswersByQuestion((int)$question['id']);
                ?>

                <?php if ($question['type'] === 'qcm' && !empty($answers)): ?>
                    <?php foreach ($answers as $answer): ?>
                        <label>
                            <input 
                                type="radio" 
                                name="question_<?= $question['id'] ?>"
                                value="<?= $answer['id'] ?>"
                                required
                            >
                            <?= htmlspecialchars($answer['answer_text']) ?>
                        </label><br>
                    <?php endforeach; ?>

                <?php elseif ($question['type'] === 'free'): ?>
                    <!-- Réponse libre -->
                    <textarea 
                        name="question_<?= $question['id'] ?>" 
                        rows="3" 
                        cols="50" 
                        placeholder="Tapez votre réponse ici..."
                        required
                    ></textarea>

                <?php else: ?>
                    <p class="no-answers">Aucune réponse disponible pour cette question.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucune question dans ce quiz.</p>
    <?php endif; ?>

    <button type="submit">Valider mes réponses</button>
</form>

</body>
</html>
