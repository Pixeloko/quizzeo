<?php
if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté et est une entreprise
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "entreprise") {
    header("Location: /quizzeo/?url=login");
    exit;
}

// Récupérer les erreurs et données précédentes
$errors = $_SESSION['form_errors'] ?? [];
$formData = $_SESSION['form_data'] ?? [];
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;

// Nettoyer la session
unset($_SESSION['form_errors'], $_SESSION['form_data'], $_SESSION['success'], $_SESSION['error']);

// Nombre de questions à afficher
$questionCount = !empty($formData['questions']) ? count($formData['questions']) : 1;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un Quiz - Entreprise</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .card { box-shadow: 0 2px 10px rgba(0,0,0,0.1); border: none; }
        .question-item { border-left: 4px solid #007bff; padding: 15px; margin-bottom: 15px; background-color: #fff; }
        .answers-container { margin-top: 10px; }
    </style>
</head>
<body>
<div class="container py-4">

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3>Créer un nouveau quiz</h3>
        <a href="/quizzeo/?url=entreprise" class="btn btn-outline-secondary">← Retour</a>
    </div>
    <div class="card-body">
        <form method="POST" action="/quizzeo/?url=entreprise/store" id="quizForm">
            
            <!-- Nom du quiz -->
            <div class="mb-4">
                <label class="form-label">Nom du quiz :</label>
                <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                       name="name" value="<?= htmlspecialchars($formData['name'] ?? '') ?>" required>
                <?php if(isset($errors['name'])): ?>
                    <div class="invalid-feedback"><?= $errors['name'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Section questions -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Questions</h4>
                <button type="button" id="add-question" class="btn btn-outline-primary">
                    <i class="bi bi-plus-circle"></i> Ajouter une question
                </button>
            </div>

            <div id="questions-container">
                <?php for($q = 0; $q < $questionCount; $q++):
                    $type = $formData['questions'][$q]['type'] ?? 'qcm';
                    $showAnswers = $type === 'qcm';
                ?>
                <div class="question-item" data-type="<?= $type ?>">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5>Question <?= $q + 1 ?></h5>
                        <?php if($q > 0): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-question"><i class="bi bi-trash"></i> Supprimer</button>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Type de question :</label>
                        <select class="form-select question-type" name="questions[<?= $q ?>][type]">
                            <option value="qcm" <?= $type==='qcm' ? 'selected' : '' ?>>QCM</option>
                            <option value="free" <?= $type==='free' ? 'selected' : '' ?>>Réponse libre</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Question :</label>
                        <input type="text" class="form-control" name="questions[<?= $q ?>][title]" 
                               value="<?= htmlspecialchars($formData['questions'][$q]['title'] ?? '') ?>" required>
                    </div>

                    <?php if($showAnswers): ?>
                    <div class="answers-container">
                        <label class="form-label">Réponses possibles :</label>
                        <?php for($a=0; $a<4; $a++): ?>
                        <div class="mb-2 d-flex align-items-center">
                            <input type="radio" class="form-check-input me-2" 
                                   name="questions[<?= $q ?>][correct_answer]" value="<?= $a ?>" <?= ($a===0)?'checked':'' ?>>
                            <input type="text" class="form-control" name="questions[<?= $q ?>][answers][<?= $a ?>][text]" 
                                   placeholder="Réponse <?= $a + 1 ?>" 
                                   value="<?= htmlspecialchars($formData['questions'][$q]['answers'][$a]['text'] ?? '') ?>">
                        </div>
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endfor; ?>
            </div>

            <div class="mt-4 d-flex justify-content-between">
                <a href="/quizzeo/?url=entreprise" class="btn btn-outline-secondary btn-lg">Annuler</a>
                <button class="btn btn-success btn-lg">Créer le quiz</button>
            </div>
        </form>
    </div>
</div>
</div>

<script>
let questionCount = <?= $questionCount ?>;

// Ajouter une question
document.getElementById('add-question').addEventListener('click', () => {
    questionCount++;
    const container = document.getElementById('questions-container');
    const template = `
    <div class="question-item" data-type="qcm">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5>Question ${questionCount}</h5>
            <button type="button" class="btn btn-sm btn-outline-danger remove-question"><i class="bi bi-trash"></i> Supprimer</button>
        </div>
        <div class="mb-3">
            <label>Type de question :</label>
            <select class="form-select question-type" name="questions[${questionCount-1}][type]">
                <option value="qcm" selected>QCM</option>
                <option value="free">Réponse libre</option>
            </select>
        </div>
        <div class="mb-3">
            <label>Texte de la question :</label>
            <input type="text" class="form-control" name="questions[${questionCount-1}][title]" required>
        </div>
        <div class="answers-container">
            ${[0,1,2,3].map(a => `
            <div class="mb-2 d-flex align-items-center">
                <input type="radio" class="form-check-input me-2" name="questions[${questionCount-1}][correct_answer]" value="${a}" ${a===0?'checked':''}>
                <input type="text" class="form-control" name="questions[${questionCount-1}][answers][${a}][text]" placeholder="Réponse ${a+1}">
            </div>
            `).join('')}
        </div>
    </div>`;
    container.insertAdjacentHTML('beforeend', template);
});

// Supprimer une question
document.addEventListener('click', e => {
    if(e.target.closest('.remove-question')){
        e.target.closest('.question-item').remove();
        updateQuestionNumbers();
    }
});

// Mettre à jour les numéros et index
function updateQuestionNumbers(){
    const questions = document.querySelectorAll('.question-item');
    questions.forEach((q, index)=>{
        q.querySelector('h5').textContent = `Question ${index+1}`;
        const inputs = q.querySelectorAll('[name]');
        inputs.forEach(input=>{
            input.name = input.name.replace(/questions\[\d+\]/, `questions[${index}]`);
        });
    });
    questionCount = questions.length;
}

// Affichage QCM/Free
document.addEventListener('change', e => {
    if(e.target.classList.contains('question-type')){
        const parent = e.target.closest('.question-item');
        if(e.target.value==='qcm'){
            parent.setAttribute('data-type','qcm');
            parent.querySelector('.answers-container').style.display='block';
        } else {
            parent.setAttribute('data-type','free');
            parent.querySelector('.answers-container').style.display='none';
        }
    }
});
</script>

</body>
</html>
