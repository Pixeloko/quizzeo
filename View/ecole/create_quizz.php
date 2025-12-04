<?php
// View/ecole/create_quizz.php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "ecole") {
    header("Location: /quizzeo/?url=login");
    exit;
}

// Récupérer les messages
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;

// Nettoyer la session
unset($_SESSION['success']);
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un Quiz - Quizzeo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        background-color: #ffffff;
    }
    
    .card {
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }
    
    .question-item {
        border-left: 4px solid #8e79b2;
        border-radius: 8px;
    }
    
    .answer-item {
        border: 1px solid #8e79b2;
        border-radius: 5px;
        transition: all 0.3s;
        background-color: #ffffff;
    }
    
    .answer-item:hover {
        background-color: rgba(142, 121, 178, 0.05);
        border-color: #7a68a0;
    }
    
    .btn-success {
        background-color: #8e79b2;
        border-color: #8e79b2;
        color: white;
    }
    
    .btn-success:hover {
        background-color: #7a68a0;
        border-color: #7a68a0;
    }
    
    .btn-outline-primary {
        border-color: #8e79b2;
        color: #8e79b2;
    }
    
    .btn-outline-primary:hover {
        background-color: #8e79b2;
        border-color: #8e79b2;
        color: white;
    }
    
    .btn-outline-secondary {
        border-color: #cccccc;
        color: #666666;
    }
    
    .btn-outline-secondary:hover {
        background-color: #f5f5f5;
        border-color: #bbbbbb;
        color: #666666;
    }
    
    .btn-outline-danger {
        border-color: #e76667;
        color: #e76667;
    }
    
    .btn-outline-danger:hover {
        background-color: #e76667;
        border-color: #e76667;
        color: white;
    }
    
    .form-check-input:checked {
        background-color: #8e79b2;
        border-color: #8e79b2;
    }
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

        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0">Créer un nouveau quiz</h1>
                    <a href="/quizzeo/?url=ecole" class="btn btn-outline-secondary">
                        ← Retour
                    </a>
                </div>
            </div>

            <div class="card-body">
                <form method="POST" action="/quizzeo/Controller/create_quiz.php" id="quizForm">
                    
                    <!-- Nom du quiz -->
                    <div class="mb-4">
                        <label for="name" class="form-label fw-bold">Nom du quiz</label>
                        <input type="text" class="form-control form-control-lg" id="name" name="name" required>
                    </div>

                    <!-- Questions -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4>Questions</h4>
                            <button type="button" id="add-question" class="btn btn-outline-primary">
                                + Ajouter une question
                            </button>
                        </div>

                        <div id="questions-container">
                            <!-- Première question par défaut -->
                            <div class="question-item mb-4 p-4 border rounded">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="mb-0">Question 1</h5>
                                </div>

                                <!-- Texte de la question -->
                                <div class="mb-3">
                                    <label class="form-label">Texte de la question </label>
                                    <input type="text" class="form-control" name="questions[0][title]" required>
                                </div>

                                <!-- Points -->
                                <div class="mb-3">
                                    <label class="form-label">Points </label>
                                    <select class="form-select w-auto" name="questions[0][point]" required>
                                        <option value="1" selected>1 point</option>
                                        <option value="2">2 points</option>
                                        <option value="3">3 points</option>
                                        <option value="4">4 points</option>
                                        <option value="5">5 points</option>
                                    </select>
                                </div>

                                <!-- Réponses -->
                                <div class="answers-container">
                                    <label class="form-label">Réponses </label>
                                    <p class="text-muted small mb-2">Cochez la bonne réponse</p>
                                    
                                    <?php for ($a = 0; $a < 4; $a++): ?>
                                    <div class="answer-item mb-2 p-2 border rounded">
                                        <div class="form-check d-flex align-items-center">
                                            <input class="form-check-input me-2" type="radio" 
                                                   name="questions[0][correct_answer]" 
                                                   value="<?= $a; ?>" <?= $a === 0 ? 'checked' : '' ?>>
                                            <input type="text" class="form-control" 
                                                   name="questions[0][answers][<?= $a; ?>][text]" 
                                                   placeholder="Réponse <?= $a + 1; ?>" required>
                                        </div>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Boutons -->
                    <div class="d-flex justify-content-between mt-4">
                        <a href="/quizzeo/?url=ecole" class="btn btn-outline-secondary">
                            Annuler
                        </a>
                        <button type="submit" class="btn btn-success px-4">
                            Créer le quiz
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    let questionCount = 1;

    // Ajouter une question
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
                    <label class="form-label">Texte de la question </label>
                    <input type="text" class="form-control" name="questions[${questionCount-1}][title]" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Points </label>
                    <select class="form-select w-auto" name="questions[${questionCount-1}][point]" required>
                        <option value="1" selected>1 point</option>
                        <option value="2">2 points</option>
                        <option value="3">3 points</option>
                        <option value="4">4 points</option>
                        <option value="5">5 points</option>
                    </select>
                </div>
                
                <div class="answers-container">
                    <label class="form-label">Réponses </label>
                    <p class="text-muted small mb-2">Cochez la bonne réponse</p>
                    
                    ${Array.from({length: 4}, (_, i) => `
                        <div class="answer-item mb-2 p-2 border rounded">
                            <div class="form-check d-flex align-items-center">
                                <input class="form-check-input me-2" type="radio" 
                                       name="questions[${questionCount-1}][correct_answer]" 
                                       value="${i}" ${i === 0 ? 'checked' : ''}>
                                <input type="text" class="form-control" 
                                       name="questions[${questionCount-1}][answers][${i}][text]" 
                                       placeholder="Réponse ${i + 1}" required>
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

    // Renumérotation des questions
    function updateQuestionNumbers() {
        const questions = document.querySelectorAll('.question-item');
        questions.forEach((question, index) => {
            const title = question.querySelector('h5');
            if (title) {
                title.textContent = `Question ${index + 1}`;
            }
        });
        questionCount = questions.length;
    }

    // Validation simple
    document.getElementById('quizForm').addEventListener('submit', function(e) {
        const questions = document.querySelectorAll('.question-item');
        
        if (questions.length === 0) {
            e.preventDefault();
            alert('Veuillez ajouter au moins une question');
            return false;
        }
    });
    </script>
</body>
</html>