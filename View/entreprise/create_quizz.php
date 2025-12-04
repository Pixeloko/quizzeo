<?php
session_start();

// Vérifier si l'utilisateur est connecté
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
        .card { box-shadow: 0 2px 10px rgba(0,0,0,0.1); border: none; }
        .question-item { border-left: 4px solid #007bff; }
        .answer-item { margin-bottom: 5px; }
        .answers-container { display: none; }
    </style>
</head>
<body>
<div class="container py-4">

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header bg-white pt-4 d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Créer un nouveau quiz</h1>
                <a href="/quizzeo/?url=entreprise" class="btn btn-outline-secondary">← Retour</a>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="/quizzeo/?url=entreprise/store" id="quizForm">
                    <!-- Nom du quiz -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Nom du quiz :</label>
                        <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                               name="name"
                               value="<?= htmlspecialchars($formData['name'] ?? '') ?>" required>
                        <?php if (isset($errors['name'])): ?>
                            <div class="invalid-feedback"><?= $errors['name']; ?></div>
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
                            <div class="question-item mb-4 p-4 border rounded">

                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5>Question <?= $q + 1 ?></h5>
                                    <?php if ($q > 0): ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-question">
                                            <i class="bi bi-trash"></i> Supprimer
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <!-- Type de question -->
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
                                    <input type="text" class="form-control"
                                           name="questions[<?= $q ?>][title]"
                                           value="<?= htmlspecialchars($formData['questions'][$q]['title'] ?? '') ?>"
                                           required>
                                </div>

                                <!-- Réponses QCM -->
                                <div class="answers-container" style="display: <?= $showAnswers ? 'block' : 'none' ?>;">
                                    <label class="form-label">Réponses (QCM)</label>
                                    <?php for ($a = 0; $a < 4; $a++): ?>
                                        <div class="answer-item mb-2 p-2 border rounded">
                                            <input type="text" class="form-control"
                                                   name="questions[<?= $q ?>][answers][<?= $a ?>][text]"
                                                   value="<?= htmlspecialchars($formData['questions'][$q]['answers'][$a]['text'] ?? '') ?>"
                                                   placeholder="Réponse <?= $a + 1 ?>">
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

<script>
let questionCount = <?= $questionCount; ?>;

// Ajouter une question
document.getElementById('add-question').addEventListener('click', function() {
    questionCount++;
    const container = document.getElementById('questions-container');

    const template = `
    <div class="question-item mb-4 p-4 border rounded">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <h5>Question ${questionCount}</h5>
            <button type="button" class="btn btn-sm btn-outline-danger remove-question">
                <i class="bi bi-trash"></i> Supprimer
            </button>
        </div>
        <div class="mb-3">
            <label class="form-label">Type de question</label>
            <select class="form-select question-type" name="questions[${questionCount-1}][type]">
                <option value="qcm">QCM</option>
                <option value="free">Réponse libre</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Texte :</label>
            <input type="text" class="form-control" name="questions[${questionCount-1}][title]" required>
        </div>
        <div class="answers-container">
            <label class="form-label">Réponses (QCM)</label>
            ${Array.from({length:4}, (_,i)=>`
                <div class="answer-item mb-2 p-2 border rounded">
                    <input type="text" class="form-control" name="questions[${questionCount-1}][answers][${i}][text]" placeholder="Réponse ${i+1}">
                </div>
            `).join('')}
        </div>
    </div>`;
    container.insertAdjacentHTML('beforeend', template);
});

// Supprimer une question
document.addEventListener('click', function(e) {
    if(e.target.closest('.remove-question')){
        e.target.closest('.question-item').remove();
        updateQuestionNumbers();
    }
});

// Mettre à jour les numéros et les noms
function updateQuestionNumbers(){
    const questions = document.querySelectorAll('.question-item');
    questions.forEach((q,index)=>{
        q.querySelector('h5').textContent = `Question ${index+1}`;
        const inputs = q.querySelectorAll('[name]');
        inputs.forEach(input=>{
            let name = input.getAttribute('name');
            name = name.replace(/questions\[\d+\]/, `questions[${index}]`);
            input.setAttribute('name', name);
        });
    });
    questionCount = questions.length;
}

// Afficher/Masquer les réponses selon le type
document.addEventListener('change', function(e){
    if(e.target.classList.contains('question-type')){
        const container = e.target.closest('.question-item').querySelector('.answers-container');
        if(e.target.value === 'qcm'){
            container.style.display = 'block';
        } else {
            container.style.display = 'none';
        }
    }
});
</script>

</body>
</html>
