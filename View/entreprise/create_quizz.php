<?php
session_start();

// Vérifier si l'utilisateur est connecté et est une entreprise
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "entreprise") {
    header("Location: /quizzeo/?url=login");
    exit;
}

// Récupérer les erreurs et données de session
$errors = $_SESSION['form_errors'] ?? [];
$formData = $_SESSION['form_data'] ?? [];
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;

// Nettoyer la session
unset($_SESSION['form_errors'], $_SESSION['form_data'], $_SESSION['success'], $_SESSION['error']);

// Nombre de questions par défaut
$questionCount = !empty($formData['questions']) ? count($formData['questions']) : 1;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Créer un Quiz - Quizzeo</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
<style>
    body { background-color: #f8f9fa; }
    .card { box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); border: none; }
    .question-item { border-left: 4px solid #007bff; padding: 1rem; margin-bottom: 1.5rem; border-radius: 0.5rem; background-color: #fff; }
    .answer-item { margin-bottom: 0.5rem; }
    .answers-container { display: none; margin-top: 1rem; }
</style>
</head>
<body>
<div class="container py-4">

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-2">Créer un nouveau quiz</h1>
                        <p class="text-muted mb-0">Remplissez le formulaire ci-dessous</p>
                    </div>
                    <a href="/quizzeo/?url=entreprise" class="btn btn-outline-secondary">← Retour</a>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="/quizzeo/?url=entreprise/store" id="quizForm">
                        <!-- Nom du quiz -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Nom du quiz :</label>
                            <input type="text" class="form-control form-control-lg <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                                   name="name"
                                   value="<?= htmlspecialchars($formData['name'] ?? '') ?>" required>
                            <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback d-block"><?= $errors['name']; ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Questions -->
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4>Questions</h4>
                                <button type="button" id="add-question" class="btn btn-outline-primary">
                                    <i class="bi bi-plus-circle"></i> Ajouter une question
                                </button>
                            </div>

                            <div id="questions-container">
                                <?php for ($q = 0; $q < $questionCount; $q++): 
                                    $type = $formData['questions'][$q]['type'] ?? 'qcm';
                                    $showAnswers = $type === 'qcm';
                                ?>
                                <div class="question-item" data-index="<?= $q ?>">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5>Question <?= $q + 1 ?></h5>
                                        <?php if ($q > 0): ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-question">
                                                <i class="bi bi-trash"></i> Supprimer
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Type -->
                                    <div class="mb-3">
                                        <label class="form-label">Type de question</label>
                                        <select class="form-select question-type" name="questions[<?= $q ?>][type]">
                                            <option value="qcm" <?= $type === 'qcm' ? 'selected' : '' ?>>QCM</option>
                                            <option value="free" <?= $type === 'free' ? 'selected' : '' ?>>Réponse libre</option>
                                        </select>
                                    </div>

                                    <!-- Texte -->
                                    <div class="mb-3">
                                        <label class="form-label">Texte :</label>
                                        <input type="text" class="form-control" name="questions[<?= $q ?>][title]"
                                               value="<?= htmlspecialchars($formData['questions'][$q]['title'] ?? '') ?>" required>
                                    </div>

                                    <!-- Points -->
                                    <div class="mb-3">
                                        <label class="form-label">Points :</label>
                                        <input type="number" class="form-control w-auto" name="questions[<?= $q ?>][point]"
                                               value="<?= $formData['questions'][$q]['point'] ?? 1 ?>" min="1" max="10" style="width: 100px;" required>
                                    </div>

                                    <!-- Réponses -->
                                    <div class="answers-container" style="display: <?= $showAnswers ? 'block' : 'none' ?>;">
                                        <label class="form-label">Réponses</label>
                                        <?php for ($a = 0; $a < 4; $a++): ?>
                                        <div class="answer-item">
                                            <div class="form-check d-flex align-items-center">
                                                <input class="form-check-input me-2" type="radio"
                                                       name="questions[<?= $q ?>][correct_answer]" value="<?= $a ?>"
                                                       <?= (($formData['questions'][$q]['correct_answer'] ?? 0) == $a) ? 'checked' : '' ?>>
                                                <input type="text" class="form-control"
                                                       name="questions[<?= $q ?>][answers][<?= $a ?>][text]"
                                                       value="<?= htmlspecialchars($formData['questions'][$q]['answers'][$a]['text'] ?? '') ?>"
                                                       placeholder="Réponse <?= $a + 1 ?>" required>
                                            </div>
                                        </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="/quizzeo/?url=entreprise" class="btn btn-outline-secondary btn-lg">Annuler</a>
                            <button class="btn btn-success btn-lg"><i class="bi bi-check-circle"></i> Créer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Gestion dynamique des questions
let questionCount = <?= $questionCount ?>;

document.getElementById('add-question').addEventListener('click', function() {
    const container = document.getElementById('questions-container');
    const index = questionCount;
    questionCount++;

    const template = `
        <div class="question-item" data-index="${index}">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h5>Question ${index + 1}</h5>
                <button type="button" class="btn btn-sm btn-outline-danger remove-question">
                    <i class="bi bi-trash"></i> Supprimer
                </button>
            </div>
            <div class="mb-3">
                <label class="form-label">Type de question</label>
                <select class="form-select question-type" name="questions[${index}][type]">
                    <option value="qcm" selected>QCM</option>
                    <option value="free">Réponse libre</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Texte :</label>
                <input type="text" class="form-control" name="questions[${index}][title]" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Points :</label>
                <input type="number" class="form-control w-auto" name="questions[${index}][point]" value="1" min="1" max="10" style="width: 100px;" required>
            </div>
            <div class="answers-container">
                <label class="form-label">Réponses</label>
                ${[0,1,2,3].map(i => `
                    <div class="answer-item">
                        <div class="form-check d-flex align-items-center">
                            <input class="form-check-input me-2" type="radio" name="questions[${index}][correct_answer]" value="${i}" ${i === 0 ? 'checked' : ''}>
                            <input type="text" class="form-control" name="questions[${index}][answers][${i}][text]" placeholder="Réponse ${i + 1}" required>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', template);
});

// Supprimer une question
document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-question')) {
        e.target.closest('.question-item').remove();
        updateQuestionNumbers();
    }
});

function updateQuestionNumbers() {
    const questions = document.querySelectorAll('.question-item');
    questions.forEach((q, i) => {
        q.querySelector('h5').textContent = `Question ${i + 1}`;
        q.dataset.index = i;
        const inputs = q.querySelectorAll('[name]');
        inputs.forEach(input => {
            input.setAttribute('name', input.getAttribute('name').replace(/questions\[\d+\]/, `questions[${i}]`));
        });
    });
    questionCount = questions.length;
}

// Fonction pour mettre à jour l'affichage des réponses selon le type
function updateAnswersVisibility() {
    document.querySelectorAll('.question-item').forEach(q => {
        const typeSelect = q.querySelector('.question-type');
        const answersContainer = q.querySelector('.answers-container');
        if (typeSelect && answersContainer) {
            answersContainer.style.display = typeSelect.value === 'qcm' ? 'block' : 'none';
        }
    });
}

// Écouteur pour chaque changement de type
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('question-type')) {
        updateAnswersVisibility();
    }
});

// Initialiser l'affichage au chargement
updateAnswersVisibility();


// Validation avant soumission
document.getElementById('quizForm').addEventListener('submit', function(e) {
    const questions = document.querySelectorAll('.question-item');
    if (questions.length === 0) {
        e.preventDefault();
        alert('Veuillez ajouter au moins une question');
        return false;
    }
    let isValid = true;
    questions.forEach(q => {
        const checked = q.querySelector('input[type="radio"]:checked');
        if (!checked) {
            isValid = false;
            q.style.borderColor = '#dc3545';
        }
    });
    if (!isValid) {
        e.preventDefault();
        alert('Veuillez sélectionner une bonne réponse pour chaque question');
        return false;
    }
});
</script>
</body>
</html>
