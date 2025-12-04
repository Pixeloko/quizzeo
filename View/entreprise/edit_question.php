<?php
// View/entreprise/edit_question.php

session_start();
require_once __DIR__ . "/../../Model/function_quizz.php";
require_once __DIR__ . "/../../Model/function_question.php";

// Vérifier l'authentification et le rôle
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "entreprise") {
    header("Location: /quizzeo/?url=login");
    exit;
}

// Récupérer l'ID de la question
$question_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($question_id <= 0) {
    $_SESSION['error'] = "Question non trouvée";
    header("Location: /quizzeo/?url=entreprise");
    exit;
}

// Récupérer la question
$question = getQuestionById($question_id);
if (!$question) {
    $_SESSION['error'] = "Question non trouvée";
    header("Location: /quizzeo/?url=entreprise");
    exit;
}

// Récupérer le quiz parent
$quiz = getQuizzById($question['quizz_id']);
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

// Récupérer les réponses
$answers = $question['answers'] ?? [];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Mettre à jour la question
    if (isset($_POST['update_question'])) {
        $title = trim($_POST['title'] ?? '');
        $point = (int)($_POST['point'] ?? 1);
        $type = $_POST['type'] ?? 'qcm';

        if (!empty($title)) {
            updateQuestion($question_id, $title, $point, $type);
            $question['title'] = $title;
            $question['point'] = $point;
            $question['type'] = $type;
            $_SESSION['success'] = "Question mise à jour";
        } else {
            $_SESSION['error'] = "Le texte de la question ne peut pas être vide";
        }
    }

    // Mettre à jour les réponses (QCM uniquement)
    if (isset($_POST['update_answers']) && ($question['type'] ?? 'qcm') === 'qcm') {
        $answers_data = $_POST['answers'] ?? [];
        $correct_answer = (int)($_POST['correct_answer'] ?? 0);

        foreach ($answers_data as $index => $answer_data) {
            if (isset($answer_data['id']) && !empty($answer_data['id'])) {
                $answer_id = (int)$answer_data['id'];
                $answer_text = trim($answer_data['text'] ?? '');
                $is_correct = ($index == $correct_answer);

                if ($answer_id > 0 && !empty($answer_text)) {
                    updateAnswer($answer_id, $answer_text, $is_correct);
                }
            }
        }
        $_SESSION['success'] = "Réponses mises à jour";
        $question = getQuestionById($question_id);
        $answers = $question['answers'] ?? [];
    }

    // Ajouter une nouvelle réponse (QCM uniquement)
    if (isset($_POST['add_answer']) && ($question['type'] ?? 'qcm') === 'qcm') {
        $new_answer = trim($_POST['new_answer'] ?? '');
        if (!empty($new_answer)) {
            addAnswerToQuestion($question_id, $new_answer, false);
            $_SESSION['success'] = "Nouvelle réponse ajoutée";
            $question = getQuestionById($question_id);
            $answers = $question['answers'] ?? [];
        }
    }

    // Supprimer une réponse (QCM uniquement)
    if (isset($_POST['delete_answer']) && ($question['type'] ?? 'qcm') === 'qcm') {
        $answer_id = (int)$_POST['delete_answer'];
        deleteAnswer($answer_id);
        $_SESSION['success'] = "Réponse supprimée";
        $question = getQuestionById($question_id);
        $answers = $question['answers'] ?? [];
    }

    // Redirection pour éviter la double soumission
    header("Location: ?id=" . $question_id);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Éditer la Question - Entreprise</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
<style>
body { background-color: #f8f9fa; }
.card { border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
.answer-item { border: 1px solid #dee2e6; border-radius: 5px; transition: all 0.3s; }
.answer-item:hover { background-color: #f8f9fa; }
.correct-answer { border-color: #28a745; background-color: #f8fff9; }
</style>
</head>
<body>
<div class="container py-4">

<!-- Navigation -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Éditer la question</h1>
        <p class="text-muted mb-0">
            Quiz: <strong><?= htmlspecialchars($quiz['name']); ?></strong>
            | Question ID: <?= $question_id; ?>
        </p>
    </div>
    <div>
        <a href="/quizzeo/View/entreprise/edit_quiz.php?id=<?= $quiz['id']; ?>" class="btn btn-outline-secondary">← Retour au quiz</a>
        <a href="/quizzeo/?url=entreprise" class="btn btn-outline-primary">← Dashboard</a>
    </div>
</div>

<!-- Messages -->
<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <?= htmlspecialchars($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['success']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <?= htmlspecialchars($_SESSION['error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['error']); endif; ?>

<div class="row">
    <!-- Colonne gauche : Éditer la question -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Texte de la question</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Type de question *</label>
                        <select class="form-select" name="type">
                            <option value="qcm" <?= ($question['type'] ?? 'qcm') === 'qcm' ? 'selected' : '' ?>>QCM</option>
                            <option value="free" <?= ($question['type'] ?? '') === 'free' ? 'selected' : '' ?>>Réponse libre</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Question *</label>
                        <textarea class="form-control" name="title" rows="4" required><?= htmlspecialchars($question['title']); ?></textarea>
                    </div>

                    <button type="submit" name="update_question" class="btn btn-primary w-100">
                        <i class="bi bi-save"></i> Enregistrer la question
                    </button>
                </form>
            </div>
        </div>

        <?php if (($question['type'] ?? 'qcm') === 'qcm'): ?>
        <!-- Ajouter une nouvelle réponse -->
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Ajouter une réponse</h5></div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Texte de la réponse</label>
                        <input type="text" name="new_answer" class="form-control" placeholder="Ex: La réponse est 42">
                    </div>
                    <button type="submit" name="add_answer" class="btn btn-outline-primary w-100">
                        <i class="bi bi-plus-circle"></i> Ajouter
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Colonne droite : Éditer les réponses -->
    <div class="col-md-6">
        <?php if (($question['type'] ?? 'qcm') === 'qcm'): ?>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Réponses</h5>
                <span class="badge bg-primary"><?= count($answers) ?></span>
            </div>
            <div class="card-body">
                <?php if (empty($answers)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-chat-quote" style="font-size:3rem;"></i>
                    <p class="mt-3">Aucune réponse</p>
                </div>
                <?php else: ?>
                <form method="POST" action="?id=<?= $question_id ?>">
                    <?php foreach ($answers as $index => $answer): ?>
                    <div class="answer-item p-3 mb-3 <?= $answer['is_correct']?'correct-answer':'' ?>">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="correct_answer" value="<?= $index ?>" id="correct_<?= $index ?>" <?= $answer['is_correct']?'checked':'' ?>>
                                <label class="form-check-label fw-bold" for="correct_<?= $index ?>">Réponse <?= $index+1 ?></label>
                            </div>
                            <?php if(count($answers)>2): ?>
                            <input type="hidden" name="answers[<?= $index ?>][id]" value="<?= $answer['id'] ?>">
                            <button type="submit" name="delete_answer" value="<?= $answer['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer cette réponse ?')"><i class="bi bi-trash"></i></button>
                            <?php endif; ?>
                        </div>
                        <input type="text" class="form-control" name="answers[<?= $index ?>][text]" value="<?= htmlspecialchars($answer['answer_text']) ?>" required>
                    </div>
                    <?php endforeach; ?>
                    <button type="submit" name="update_answers" class="btn btn-success w-100"><i class="bi bi-check-circle"></i> Enregistrer les réponses</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Mettre en évidence la réponse correcte
document.querySelectorAll('input[name="correct_answer"]').forEach(radio => {
    radio.addEventListener('change', function(){
        document.querySelectorAll('.answer-item').forEach(item => item.classList.remove('correct-answer'));
        const answerItem = this.closest('.answer-item');
        if(answerItem) answerItem.classList.add('correct-answer');
    });
});
</script>
</body>
</html>
