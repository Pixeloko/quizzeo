<?php
// --- SESSION ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Vérification du rôle entreprise ---
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "entreprise") {
    header("Location: /quizzeo/?url=login");
    exit;
}

require_once __DIR__ . "/../../Model/function_quizz.php";
require_once __DIR__ . "/../../Model/function_question.php";
require_once __DIR__ . "/../../Model/function_quizz_question.php";

// Récupérer l'ID du quiz
$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($quiz_id <= 0) {
    $_SESSION['error'] = "Quiz non trouvé";
    header("Location: /quizzeo/?url=entreprise");
    exit;
}

// Récupérer le quiz
$quiz = getQuizzById($quiz_id);
if (!$quiz) {
    $_SESSION['error'] = "Quiz non trouvé";
    header("Location: /quizzeo/?url=entreprise");
    exit;
}

// Vérifier que l'utilisateur est le propriétaire
if ($quiz['user_id'] != $_SESSION['user_id']) {
    $_SESSION['error'] = "Accès non autorisé";
    header("Location: /quizzeo/?url=entreprise");
    exit;
}

// Récupérer les questions du quiz
$questions = [];
if (function_exists('GetQuestionsByQuizz_ecole')) {
    $questions = GetQuestionsByQuizz_ecole($quiz_id);
}

// TRAITEMENT FORMULAIRE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Renommer
    if (isset($_POST['update_name'])) {
        $new_name = trim($_POST['name'] ?? '');
        if ($new_name !== "") {
            if (updateQuizName($quiz_id, $new_name)) {
                $_SESSION['success'] = "Nom du quiz mis à jour";
                $quiz['name'] = $new_name;
            } else {
                $_SESSION['error'] = "Erreur lors du changement de nom";
            }
        } else {
            $_SESSION['error'] = "Le nom ne peut pas être vide";
        }
    }

    // 2. Ajouter une question
    if (isset($_POST['add_question'])) {
        $question_text = trim($_POST['new_question'] ?? "");
        $point = (int)($_POST['new_point'] ?? 1);

        if ($question_text !== "") {
            $question_id = createQuestion($quiz_id, $question_text, $point);
            if ($question_id) {
                // réponses par défaut
                for ($i = 0; $i < 4; $i++) {
                    addAnswerToQuestion(
                        $question_id,
                        "Réponse " . ($i + 1),
                        $i === 0
                    );
                }
                $_SESSION['success'] = "Question ajoutée";
            } else {
                $_SESSION['error'] = "Impossible d’ajouter la question";
            }
        }
    }

    // 3. Lancer
    if (isset($_POST['launch_quiz'])) {
        if (empty($questions)) {
            $_SESSION['error'] = "Impossible de lancer un quiz sans questions";
        } else {
            if (updateQuizzStatus($quiz_id, "launched")) {
                $_SESSION['success'] = "Quiz lancé";
            } else {
                $_SESSION['error'] = "Erreur lancement quiz";
            }
        }
    }

    // 4. Finir
    if (isset($_POST['finish_quiz'])) {
        if (updateQuizzStatus($quiz_id, "finished")) {
            $_SESSION['success'] = "Quiz terminé";
        } else {
            $_SESSION['error'] = "Erreur fin quiz";
        }
    }

    // 5. Supprimer question
    if (isset($_POST['delete_question'])) {
        $qid = (int)$_POST['delete_question'];
        $q = getQuestionById($qid);

        if ($q && $q["quizz_id"] == $quiz_id) {
            deleteQuestion($qid);
            $_SESSION['success'] = "Question supprimée";
        } else {
            $_SESSION['error'] = "Question introuvable";
        }
    }

    header("Location: /quizzeo/View/entreprise/edit_quiz.php?id=" . $quiz_id);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Éditer le Quiz - Entreprise</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .question-item { border-left: 4px solid #007bff; }
        .answer-item { border: 1px solid #dee2e6; border-radius: 5px; }
        .correct-answer { background: #f4fff4; border-color: #28a745; }
    </style>
</head>

<body>
<div class="container py-4">

    <div class="d-flex justify-content-between mb-4">
        <h2>Éditer le quiz : <?= htmlspecialchars($quiz['name']); ?></h2>
        <a href="/quizzeo/?url=entreprise" class="btn btn-secondary">← Retour</a>
    </div>

    <!-- Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; ?></div>
    <?php unset($_SESSION['success']); endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; ?></div>
    <?php unset($_SESSION['error']); endif; ?>

    <div class="row">
        <!-- COL GAUCHE -->
        <div class="col-md-4">

            <!-- Renommer -->
            <div class="card mb-3">
                <div class="card-header">Renommer</div>
                <div class="card-body">
                    <form method="POST">
                        <input type="text" name="name" class="form-control mb-2"
                               value="<?= htmlspecialchars($quiz['name']); ?>">
                        <button name="update_name" class="btn btn-primary w-100">Renommer</button>
                    </form>
                </div>
            </div>

            <!-- Ajouter question -->
            <div class="card mb-3">
                <div class="card-header">Nouvelle question</div>
                <div class="card-body">
                    <form method="POST">
                        <input type="text" name="new_question" class="form-control mb-2" placeholder="Question">
                        <select name="new_point" class="form-select mb-2">
                            <option value="1">1 point</option>
                            <option value="2">2 points</option>
                            <option value="3">3 points</option>
                        </select>
                        <button name="add_question" class="btn btn-primary w-100">Ajouter</button>
                    </form>
                </div>
            </div>

            <!-- Lancer / Finir -->
            <div class="card mb-3">
                <div class="card-header">Actions</div>
                <div class="card-body">
                    <form method="POST">
                        <?php if ($quiz['status'] === 'launched'): ?>
                            <button name="finish_quiz" class="btn btn-success w-100">Terminer le quiz</button>
                        <?php elseif ($quiz['status'] === 'finished'): ?>
                            <div class="badge bg-success w-100 p-2 text-center">Quiz terminé</div>
                        <?php else: ?>
                            <button name="launch_quiz" class="btn btn-warning w-100">Lancer le quiz</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

        </div>

        <!-- COL DROITE -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    Questions (<?= count($questions); ?>)
                </div>
                <div class="card-body">

                    <?php if (empty($questions)): ?>
                        <p class="text-muted">Aucune question.</p>
                    <?php endif; ?>

                    <?php foreach ($questions as $i => $q): ?>
                        <div class="question-item p-3 mb-3 bg-white">

                            <div class="d-flex justify-content-between mb-2">
                                <h5>Question <?= $i+1 ?></h5>

                                <div>
                                    <a href="/quizzeo/View/entreprise/edit_question.php?id=<?= $q['id']; ?>"
                                       class="btn btn-outline-primary btn-sm">
                                        Éditer
                                    </a>

                                    <form method="POST" style="display:inline"
                                          onsubmit="return confirm('Supprimer ?')">
                                        <input type="hidden" name="delete_question" value="<?= $q['id']; ?>">
                                        <button class="btn btn-outline-danger btn-sm">X</button>
                                    </form>
                                </div>
                            </div>

                            <p><?= htmlspecialchars($q['title']); ?></p>

                            <?php if (!empty($q['answers'])): ?>
                                <?php foreach ($q['answers'] as $ans): ?>
                                    <div class="answer-item p-2 mb-1 <?= $ans['is_correct'] ? 'correct-answer' : '' ?>">
                                        <input type="radio" <?= $ans['is_correct'] ? 'checked' : '' ?> disabled>
                                        <?= htmlspecialchars($ans['answer_text']); ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                        </div>
                    <?php endforeach; ?>

                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>
