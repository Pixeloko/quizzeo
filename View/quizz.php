<?php
// Empêcher accès direct sans ID
if (!isset($quizz_id)) {
    echo "Aucun quizz indiqué.";
    exit;
}

// Charger les fonctions
require_once __DIR__ . '/../Model/function_quizz.php';
require_once __DIR__ . '/../Model/function_quizz_question.php';
require_once __DIR__ . '/../Model/function_question.php';

// Récupération du quizz
$quizz = getQuizzById($quizz_id);

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

<h1><?= htmlspecialchars($quizz['title']) ?></h1>

<?php if (!empty($quizz['description'])): ?>
    <p><?= htmlspecialchars($quizz['description']) ?></p>
<?php endif; ?>

<form action="index.php?url=submit_quizz" method="POST">

    <!-- ID du quizz -->
    <input type="hidden" name="quizz_id" value="<?= (int)$quizz_id ?>">

    <?php if (!empty($questions)): ?>
        <?php foreach ($questions as $index => $question): ?>

            <div style="margin-bottom:30px;">
                <h3>
                    Question <?= $index + 1 ?> : 
                    <?= htmlspecialchars($question['question_text']) ?>
                </h3>

                <?php 
                // Récupérer les réponses
                $answers = getAnswersByQuestionId($question['question_id']);
                ?>

                <?php if (!empty($answers)): ?>
                    <?php foreach ($answers as $answer): ?>
                        <label style="display:block; margin:5px 0;">
                            <input 
                                type="radio" 
                                name="question_<?= $question['question_id'] ?>"
                                value="<?= $answer['answer_id'] ?>"
                                required
                            >
                            <?= htmlspecialchars($answer['answer_text']) ?>
                        </label>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color:red;">Aucune réponse pour cette question.</p>
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
