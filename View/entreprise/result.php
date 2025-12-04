<?php
session_start();
require_once __DIR__ . "/../../Model/function_quizz.php";

// Vérifier l'authentification et rôle
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'entreprise') {
    header("Location: /quizzeo/?url=login");
    exit;
}

// Récupérer l'ID du quiz
$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($quiz_id <= 0) {
    $_SESSION['error'] = "Quiz invalide.";
    header("Location: /quizzeo/?url=entreprise");
    exit;
}

// Récupérer le quiz et les résultats
$quiz = getQuizzById($quiz_id);
$results = getQuizzResults($quiz_id) ?: [];

// Vérifier que l'utilisateur est le propriétaire
if (!$quiz || $quiz['user_id'] != $_SESSION['user_id']) {
    $_SESSION['error'] = "Accès non autorisé.";
    header("Location: /quizzeo/?url=entreprise");
    exit;
}

// Récupérer les questions avec les réponses
$questions = getQuestionsWithAnswersByQuizId($quiz_id);

// Nombre de participants
$participants = count($results);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Résultats du Quiz - <?= htmlspecialchars($quiz['name']); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body>
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Résultats: <?= htmlspecialchars($quiz['name']); ?></h1>
        <a href="/quizzeo/View/entreprise/edit_quiz.php?id=<?= $quiz_id ?>" class="btn btn-outline-secondary">← Retour au Quiz</a>
    </div>

    <div class="mb-4">
        <h5>Nombre de participants: <strong><?= $participants; ?></strong></h5>
    </div>

    <?php foreach ($questions as $question): ?>
        <div class="card mb-3">
            <div class="card-header">
                <strong>Question:</strong> <?= htmlspecialchars($question['title']); ?>
                (<?= strtoupper($question['type']); ?>)
            </div>
            <div class="card-body">
                <?php if ($question['type'] === 'qcm'): ?>
                    <ul class="list-group">
                        <?php
                        // Compter les réponses
                        $counts = [];
                        foreach ($question['answers'] as $answer) {
                            $counts[$answer['id']] = 0;
                        }

                        foreach ($results as $result) {
                            if (isset($result['answers'][$question['id']])) {
                                $answerId = $result['answers'][$question['id']];
                                if (isset($counts[$answerId])) $counts[$answerId]++;
                            }
                        }

                        foreach ($question['answers'] as $answer):
                            $percentage = $participants > 0 ? round($counts[$answer['id']] / $participants * 100, 1) : 0;
                        ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= htmlspecialchars($answer['answer_text']); ?>
                            <span class="badge bg-primary rounded-pill"><?= $percentage ?>%</span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($results as $result):
                            $text = $result['answers'][$question['id']] ?? '';
                            if (!empty($text)):
                        ?>
                        <li class="list-group-item">
                            <strong><?= htmlspecialchars($result['name']); ?>:</strong> <?= htmlspecialchars($text); ?>
                        </li>
                        <?php endif; endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
