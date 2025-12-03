<?php
require_once __DIR__ . '/../includes/header.php';


// Vérification du role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ecole') {
    header('Location: /quizzeo/index.php?url=login');
    exit;
}

// Récupérer les questions déjà existantes
require_once __DIR__ . '/../../Model/function_question.php';
$questions = getAllQuestions(); 

$errors = $errors ?? []; 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un Nouveau Quiz</title>
</head>
<body>
    <h1>Créer un Nouveau Quiz</h1>

    <form action="/quizzeo/index.php?url=ecole/store" method="POST">
        <label for="name">Nom du Quiz :</label>
        <input type="text" id="name" name="name" required>
        <?php if (isset($errors['name'])): ?>
            <span style="color:red;"><?= htmlspecialchars($errors['name']) ?></span>
        <?php endif; ?>

        <h2>Questions existantes</h2>
        <?php if (!empty($questions)): ?>
            <?php foreach ($questions as $qIndex => $question): ?>
                <fieldset style="margin-bottom: 20px;">
                    <legend>Question <?= $qIndex + 1 ?> : <?= htmlspecialchars($question['title']) ?></legend>
                    <label>Points :</label>
                    <input type="number" name="questions[<?= $question['id'] ?>][point]" value="<?= $question['point'] ?? 1 ?>" min="1" required>

                    <h3>Réponses (QCM)</h3>
                    <?php foreach ($question['answers'] as $aIndex => $answer): ?>
                        <div>
                            <input type="text" name="questions[<?= $question['id'] ?>][answers][<?= $answer['id'] ?>][text]" value="<?= htmlspecialchars($answer['answer_text']) ?>" required>
                            <label>
                                Correcte ?
                                <input type="radio" name="questions[<?= $question['id'] ?>][correct_answer]" value="<?= $answer['id'] ?>" <?= $answer['is_correct'] ? 'checked' : '' ?>>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </fieldset>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucune question existante pour le moment.</p>
        <?php endif; ?>

        <button type="submit">Créer le Quiz</button>
    </form>
</body>
</html>
