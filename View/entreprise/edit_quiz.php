<?php
// View/entreprise/edit_quiz.php

// Activer les erreurs (débogage)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();
require_once __DIR__ . "/../../Model/function_quizz.php";
require_once __DIR__ . "/../../Model/function_question.php";
require_once __DIR__ . "/../../Model/function_quizz_question.php";

// Vérifier l'authentification et le rôle "entreprise"
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "entreprise") {
    header("Location: /quizzeo/?url=login");
    exit;
}

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
if (function_exists('GetQuestionsByQuizz_entreprise')) {
    $questions = GetQuestionsByQuizz_entreprise($quiz_id);
}

// TRAITEMENT DU FORMULAIRE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Mettre à jour le nom du quiz
    if (isset($_POST['update_name'])) {
        $new_name = trim($_POST['name'] ?? '');
        if (!empty($new_name) && function_exists('updateQuizName')) {
            $result = updateQuizName($quiz_id, $new_name);
            if ($result) {
                $quiz['name'] = $new_name;
                $_SESSION['success'] = "Nom du quiz mis à jour";
            } else {
                $_SESSION['error'] = "Échec de la mise à jour du nom";
            }
        } else {
            $_SESSION['error'] = "Le nom ne peut pas être vide";
        }
    }

    // 2. Ajouter une nouvelle question
    if (isset($_POST['add_question'])) {
        $question_text = trim($_POST['new_question'] ?? '');
        $point = (int)($_POST['new_point'] ?? 1);

        if (!empty($question_text) && function_exists('createQuestion')) {
            $question_id = createQuestion($quiz_id, $question_text, $point);
            if ($question_id) {
                // Ajouter 4 réponses par défaut
                for ($i = 0; $i < 4; $i++) {
                    $answer_text = "Réponse " . ($i + 1);
                    $is_correct = ($i === 0);
                    addAnswerToQuestion($question_id, $answer_text, $is_correct);
                }
                $_SESSION['success'] = "Question ajoutée avec succès";
            } else {
                $_SESSION['error'] = "Échec de la création de la question";
            }
        } else {
            $_SESSION['error'] = "Le texte de la question ne peut pas être vide";
        }
    }

    // 3. Lancer le quiz
    if (isset($_POST['launch_quiz'])) {
        if (empty($questions)) {
            $_SESSION['error'] = "Impossible de lancer un quiz sans questions";
        } elseif (function_exists('updateQuizzStatus')) {
            $result = updateQuizzStatus($quiz_id, 'launched');
            if ($result) {
                $_SESSION['success'] = "Quiz lancé ! Les participants peuvent y répondre.";
                $quiz['status'] = 'launched';
            } else {
                $_SESSION['error'] = "Échec du lancement du quiz";
            }
        }
    }

    // 4. Terminer le quiz
    if (isset($_POST['finish_quiz']) && function_exists('updateQuizzStatus')) {
        $result = updateQuizzStatus($quiz_id, 'finished');
        if ($result) {
            $_SESSION['success'] = "Quiz terminé";
            $quiz['status'] = 'finished';
        } else {
            $_SESSION['error'] = "Échec de la fin du quiz";
        }
    }

    // Redirection pour éviter le resoumission du formulaire
    header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $quiz_id);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Éditer le Quiz - Quizzeo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .question-item { border-left: 4px solid #007bff; margin-bottom: 20px; }
        .answer-item { border: 1px solid #dee2e6; border-radius: 5px; }
        .correct-answer { border-color: #28a745; background-color: #f8fff9; }
    </style>
</head>
<body>
<div class="container py-4">
    <!-- Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Éditer : <?= htmlspecialchars($quiz['name']); ?></h1>
            <p class="text-muted mb-0">
                ID: <?= $quiz_id; ?> | Statut: 
                <span class="badge bg-<?= 
                    $quiz['status'] === 'finished' ? 'success' : 
                    ($quiz['status'] === 'launched' ? 'warning' : 'secondary') 
                ?>">
                    <?= 
                        $quiz['status'] === 'finished' ? 'Terminé' : 
                        ($quiz['status'] === 'launched' ? 'Lancé' : 'En écriture') 
                    ?>
                </span>
            </p>
        </div>
        <a href="/quizzeo/?url=entreprise" class="btn btn-outline-primary">← Retour au dashboard</a>
    </div>

    <!-- Messages -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="row">
        <!-- Colonne gauche : Actions -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Actions</h5></div>
                <div class="card-body">
                    <!-- Lancer/terminer -->
                    <form method="POST" class="mb-3">
                        <?php if ($quiz['status'] === 'launched'): ?>
                            <button type="submit" name="finish_quiz" class="btn btn-success w-100 mb-2">
                                <i class="bi bi-stop-circle"></i> Terminer le quiz
                            </button>
                        <?php elseif ($quiz['status'] === 'finished'): ?>
                            <span class="badge bg-success w-100 p-2 text-center">
                                <i class="bi bi-check-circle"></i> Quiz terminé
                            </span>
                        <?php else: ?>
                            <button type="submit" name="launch_quiz" class="btn btn-warning w-100 mb-2">
                                <i class="bi bi-play-circle"></i> Lancer le quiz
                            </button>
                        <?php endif; ?>
                    </form>

                    <!-- Renommer le quiz -->
                    <form method="POST" class="mb-3">
                        <div class="mb-2">
                            <label class="form-label">Renommer le quiz</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($quiz['name']); ?>">
                        </div>
                        <button type="submit" name="update_name" class="btn btn-outline-primary w-100">
                            <i class="bi bi-pencil"></i> Renommer
                        </button>
                    </form>

                    <!-- Ajouter une question -->
                    <form method="POST">
                        <div class="mb-2">
                            <label class="form-label">Ajouter une question</label>
                            <input type="text" name="new_question" class="form-control" placeholder="Nouvelle question">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Points</label>
                            <select name="new_point" class="form-select">
                                <?php for ($i=1; $i<=5; $i++): ?>
                                    <option value="<?= $i; ?>"><?= $i; ?> point<?= $i>1?'s':''; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <button type="submit" name="add_question" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle"></i> Ajouter
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Colonne droite : Questions -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Questions du quiz</h5>
                        <span class="badge bg-primary"><?= count($questions); ?> questions</span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($questions)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-question-circle text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Aucune question</h5>
                            <p class="text-muted">Ajoutez votre première question en utilisant le formulaire à gauche</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($questions as $index => $question_item): ?>
                            <div class="question-item p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0">
                                        Question <?= $index + 1; ?>
                                        <span class="badge bg-info ms-2"><?= $question_item['point']; ?> point(s)</span>
                                    </h6>
                                    <div>
                                        <a href="/quizzeo/View/entreprise/edit_question.php?id=<?= $question_item['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i> Éditer
                                        </a>
                                        <a href="/quizzeo/Controller/delete_question.php?id=<?= $question_item['id']; ?>&quiz_id=<?= $quiz_id; ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Supprimer cette question ?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </div>

                                <p class="mb-3"><?= htmlspecialchars($question_item['title']); ?></p>

                                <?php if (!empty($question_item['answers'])): ?>
                                    <div class="answers-container">
                                        <p class="text-muted small mb-2">Réponses :</p>
                                        <?php foreach ($question_item['answers'] as $answer): ?>
                                            <div class="answer-item p-2 mb-1 <?= $answer['is_correct'] ? 'correct-answer' : ''; ?>">
                                                <div class="form-check d-flex align-items-center">
                                                    <input class="form-check-input me-2" type="radio" disabled <?= $answer['is_correct'] ? 'checked' : ''; ?>>
                                                    <span><?= htmlspecialchars($answer['answer_text']); ?></span>
                                                    <?php if ($answer['is_correct']): ?>
                                                        <span class="badge bg-success ms-2"><i class="bi bi-check-circle"></i> Bonne réponse</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Icons & JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Confirmation avant suppression d'une question
    document.querySelectorAll('.btn-outline-danger').forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette question ?')) {
                e.preventDefault();
            }
        });
    });
</script>
</body>
</html>
