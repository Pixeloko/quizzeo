<?php
// View/ecole/create_quizz.php

session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "ecole") {
    header("Location: /quizzeo/?url=login");
    exit;
}

// Récupérer les erreurs et données de session
$errors = $_SESSION['form_errors'] ?? [];
$formData = $_SESSION['form_data'] ?? [];
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;

// Nettoyer la session
unset($_SESSION['form_errors']);
unset($_SESSION['form_data']);
unset($_SESSION['success']);
unset($_SESSION['error']);

// Déterminer le nombre de questions à afficher
$questionCount = !empty($formData['questions']) ? count($formData['questions']) : 1;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un Quiz - Quizzeo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { box-shadow: 0 2px 10px rgba(0,0,0,0.1); border: none; }
        .question-item { border-left: 4px solid #007bff; }
        .answer-item { transition: all 0.3s; }
        .answer-item:hover { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Messages -->
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header bg-white border-bottom-0 pt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="h3 mb-2">Créer un nouveau quiz</h1>
                                <p class="text-muted mb-0">Remplissez le formulaire ci-dessous</p>
                            </div>
                            <a href="/quizzeo/?url=ecole" class="btn btn-outline-secondary">
                                ← Retour au dashboard
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body p-4">
                        <!-- Formulaire -->
                        <form method="POST" action="/quizzeo/?url=ecole/store" id="quizForm">
                            
                            <!-- Nom du quiz -->
                            <div class="mb-4">
                                <label for="name" class="form-label fw-bold">
                                    Nom du quiz *
                                    <span class="text-danger" title="Champ obligatoire">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                       id="name" 
                                       name="name" 
                                       value="<?= htmlspecialchars($formData['name'] ?? ''); ?>" 
                                       placeholder="Ex: Quiz de Mathématiques - Chapitre 3" 
                                       required>
                                <?php if (isset($errors['name'])): ?>
                                    <div class="invalid-feedback d-block"><?= $errors['name']; ?></div>
                                <?php endif; ?>
                                <div class="form-text">Donnez un nom clair et descriptif à votre quiz</div>
                            </div>

                            <!-- Section des questions -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 class="mb-0">Questions</h4>
                                    <button type="button" id="add-question" class="btn btn-outline-primary">
                                        <i class="bi bi-plus-circle"></i> Ajouter une question
                                    </button>
                                </div>

                                <div id="questions-container">
                                    <?php for ($q = 0; $q < $questionCount; $q++): ?>
                                    <div class="question-item mb-4 p-4 border rounded">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h5 class="mb-0">Question <?= $q + 1; ?></h5>
                                            <?php if ($q > 0): ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-question">
                                                <i class="bi bi-trash"></i> Supprimer
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Texte de la question -->
                                        <div class="mb-3">
                                            <label class="form-label">Texte de la question *</label>
                                            <input type="text" 
                                                   class="form-control <?= isset($errors["question_{$q}"]) ? 'is-invalid' : '' ?>" 
                                                   name="questions[<?= $q; ?>][title]" 
                                                   value="<?= htmlspecialchars($formData['questions'][$q]['title'] ?? ''); ?>" 
                                                   required>
                                            <?php if (isset($errors["question_{$q}"])): ?>
                                                <div class="invalid-feedback d-block"><?= $errors["question_{$q}"]; ?></div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Points -->
                                        <div class="mb-3">
                                            <label class="form-label">Points *</label>
                                            <input type="number" 
                                                   class="form-control w-auto <?= isset($errors["point_{$q}"]) ? 'is-invalid' : '' ?>" 
                                                   name="questions[<?= $q; ?>][point]" 
                                                   value="<?= htmlspecialchars($formData['questions'][$q]['point'] ?? 1); ?>" 
                                                   min="1" max="10" 
                                                   style="width: 100px;" 
                                                   required>
                                            <?php if (isset($errors["point_{$q}"])): ?>
                                                <div class="invalid-feedback d-block"><?= $errors["point_{$q}"]; ?></div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Réponses -->
                                        <div class="answers-container">
                                            <label class="form-label">Réponses *</label>
                                            <p class="text-muted small mb-2">Cochez la bonne réponse pour chaque question</p>
                                            
                                            <?php for ($a = 0; $a < 4; $a++): ?>
                                            <div class="answer-item mb-2 p-2 border rounded">
                                                <div class="form-check d-flex align-items-center">
                                                    <input class="form-check-input me-2" 
                                                           type="radio" 
                                                           name="questions[<?= $q; ?>][correct_answer]" 
                                                           value="<?= $a; ?>" 
                                                           <?= (($formData['questions'][$q]['correct_answer'] ?? 0) == $a) ? 'checked' : ''; ?>
                                                           <?= $a === 0 && !isset($formData['questions'][$q]['correct_answer']) ? 'checked' : ''; ?>>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           name="questions[<?= $q; ?>][answers][<?= $a; ?>][text]" 
                                                           value="<?= htmlspecialchars($formData['questions'][$q]['answers'][$a]['text'] ?? ''); ?>" 
                                                           placeholder="Réponse <?= $a + 1; ?>" 
                                                           required>
                                                    <input type="hidden" 
                                                           name="questions[<?= $q; ?>][answers][<?= $a; ?>][id]" 
                                                           value="<?= $a; ?>">
                                                </div>
                                            </div>
                                            <?php endfor; ?>
                                            
                                            <?php if (isset($errors["answers_{$q}"])): ?>
                                                <div class="alert alert-warning mt-2"><?= $errors["answers_{$q}"]; ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                                
                                <?php if (isset($errors['questions'])): ?>
                                    <div class="alert alert-warning"><?= $errors['questions']; ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Boutons -->
                            <div class="d-flex justify-content-between mt-5">
                                <a href="/quizzeo/?url=ecole" class="btn btn-outline-secondary btn-lg">
                                    Annuler
                                </a>
                                <button type="submit" class="btn btn-success btn-lg px-4">
                                    <i class="bi bi-check-circle"></i> Créer le quiz
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Gestion des questions dynamiques
        let questionCount = <?= $questionCount; ?>;
        
        document.getElementById('add-question').addEventListener('click', function() {
            questionCount++;
            const container = document.getElementById('questions-container');
            
            const template = `
                <div class="question-item mb-4 p-4 border rounded">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="mb-0">Question ${questionCount}</h5>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-question">
                            <i class="bi bi-trash"></i> Supprimer
                        </button>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Texte de la question *</label>
                        <input type="text" class="form-control" name="questions[${questionCount-1}][title]" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Points *</label>
                        <input type="number" class="form-control w-auto" name="questions[${questionCount-1}][point]" value="1" min="1" max="10" style="width: 100px;" required>
                    </div>
                    
                    <div class="answers-container">
                        <label class="form-label">Réponses *</label>
                        <p class="text-muted small mb-2">Cochez la bonne réponse pour chaque question</p>
                        
                        ${Array.from({length: 4}, (_, i) => `
                            <div class="answer-item mb-2 p-2 border rounded">
                                <div class="form-check d-flex align-items-center">
                                    <input class="form-check-input me-2" type="radio" name="questions[${questionCount-1}][correct_answer]" value="${i}" ${i === 0 ? 'checked' : ''}>
                                    <input type="text" class="form-control" name="questions[${questionCount-1}][answers][${i}][text]" placeholder="Réponse ${i + 1}" required>
                                    <input type="hidden" name="questions[${questionCount-1}][answers][${i}][id]" value="${i}">
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
            questions.forEach((question, index) => {
                const title = question.querySelector('h5');
                if (title) {
                    title.textContent = `Question ${index + 1}`;
                }
                updateQuestionIndexes(question, index);
            });
            questionCount = questions.length;
        }
        
        function updateQuestionIndexes(questionElement, newIndex) {
            // Mettre à jour tous les inputs
            const inputs = questionElement.querySelectorAll('[name]');
            inputs.forEach(input => {
                const name = input.getAttribute('name');
                const updatedName = name.replace(/questions\[\d+\]/g, `questions[${newIndex}]`);
                input.setAttribute('name', updatedName);
            });
        }
        
        // Validation avant soumission
        document.getElementById('quizForm').addEventListener('submit', function(e) {
            const questions = document.querySelectorAll('.question-item');
            
            if (questions.length === 0) {
                e.preventDefault();
                alert('Veuillez ajouter au moins une question');
                return false;
            }
            
            // Vérifier que chaque question a une bonne réponse
            let isValid = true;
            questions.forEach((question, index) => {
                const hasRadioChecked = question.querySelector('input[type="radio"]:checked');
                if (!hasRadioChecked) {
                    isValid = false;
                    question.style.borderColor = '#dc3545';
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