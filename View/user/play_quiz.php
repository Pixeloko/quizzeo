<?php
// View/user/play_quiz.php

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier l'authentification
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "user") {
    header("Location: /quizzeo/?url=login");
    exit;
}

// Charger les fonctions
require_once __DIR__ . '/../Model/function_quizz.php';
require_once __DIR__ . '/../Model/function_quizz_question.php';
require_once __DIR__ . '/../Model/function_question.php';

// Récupérer l'ID du quiz depuis l'URL
$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($quiz_id <= 0) {
    $_SESSION['error'] = "Quiz non trouvé";
    header("Location: /quizzeo/?url=user");
    exit;
}

// Récupération du quiz
$quizz = getQuizzById($quiz_id);

if (!$quizz || $quizz['status'] !== 'launched') {
    $_SESSION['error'] = "Ce quiz n'est pas disponible";
    header("Location: /quizzeo/?url=user");
    exit;
}

// Récupération des questions
$questions = getQuestionsByQuizzId($quiz_id);

if (empty($questions)) {
    $_SESSION['error'] = "Ce quiz n'a pas de questions";
    header("Location: /quizzeo/?url=user");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($quizz['name']) ?> - Quizzeo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
    .quiz-container {
        max-width: 800px;
        margin: 0 auto;
    }
    .question-card {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        background-color: white;
    }
    .answer-option {
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 12px 15px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .answer-option:hover {
        background-color: #f8f9fa;
        border-color: #007bff;
    }
    .answer-option.selected {
        background-color: #e7f3ff;
        border-color: #007bff;
    }
    .progress-bar {
        height: 10px;
        border-radius: 5px;
        background-color: #e9ecef;
    }
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #28a745, #20c997);
        border-radius: 5px;
        transition: width 0.3s;
    }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="/quizzeo/?url=user">
                <i class="bi bi-house"></i> Retour au dashboard
            </a>
        </div>
    </nav>

    <div class="quiz-container py-4">
        <!-- En-tête -->
        <div class="text-center mb-4">
            <h1 class="h2"><?= htmlspecialchars($quizz['name']) ?></h1>
            <?php if (!empty($quizz['description'])): ?>
                <p class="text-muted"><?= htmlspecialchars($quizz['description']) ?></p>
            <?php endif; ?>
            <div class="progress-bar mt-3">
                <div class="progress-fill" style="width: 0%" id="quiz-progress"></div>
            </div>
            <small class="text-muted" id="progress-text">Question 0 sur <?= count($questions); ?></small>
        </div>

        <!-- Messages d'erreur/succès -->
        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4">
            <?= htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Formulaire -->
        <!-- CORRECTION ICI : URL absolue vers la racine -->
        <form action="/quizzeo/?url=submit_quizz" method="POST" id="quiz-form">
            <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">
            
            <?php if (!empty($questions)): ?>
                <?php foreach ($questions as $index => $question): ?>
                <div class="question-card" id="question-<?= $index ?>" 
                     style="<?= $index > 0 ? 'display: none;' : '' ?>">
                    
                    <h4 class="mb-3">
                        Question <?= $index + 1 ?> 
                        <span class="badge bg-info float-end"><?= $question['point'] ?> point(s)</span>
                    </h4>
                    
                    <p class="mb-4 fw-bold"><?= htmlspecialchars($question['title']) ?></p>
                    
                    <?php 
                    $answers = getAnswersByQuestion((int)$question['id']);
                    ?>
                    
                    <?php if (!empty($answers)): ?>
                        <div class="answers-container">
                            <?php foreach ($answers as $answer_index => $answer): ?>
                            <div class="answer-option" data-question="<?= $index ?>" 
                                 data-answer="<?= $answer['id'] ?>">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="radio" 
                                           name="question_<?= $question['id'] ?>"
                                           value="<?= $answer['id'] ?>"
                                           id="answer_<?= $question['id'] ?>_<?= $answer['id'] ?>"
                                           required>
                                    <label class="form-check-label w-100" 
                                           for="answer_<?= $question['id'] ?>_<?= $answer['id'] ?>">
                                        <?= htmlspecialchars($answer['answer_text']) ?>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> 
                            Aucune réponse disponible pour cette question.
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle"></i> 
                    Aucune question dans ce quiz.
                </div>
            <?php endif; ?>

            <!-- Boutons de navigation -->
            <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-outline-secondary" id="prev-btn" style="display: none;">
                    <i class="bi bi-arrow-left"></i> Précédent
                </button>
                
                <button type="button" class="btn btn-primary" id="next-btn">
                    Suivant <i class="bi bi-arrow-right"></i>
                </button>
                
                <button type="submit" class="btn btn-success" id="submit-btn" style="display: none;">
                    <i class="bi bi-check-circle"></i> Terminer le quiz
                </button>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const questions = document.querySelectorAll('.question-card');
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        const submitBtn = document.getElementById('submit-btn');
        const progressFill = document.getElementById('quiz-progress');
        const progressText = document.getElementById('progress-text');
        let currentQuestion = 0;
        
        // Mettre à jour la progression
        function updateProgress() {
            const progress = ((currentQuestion) / questions.length) * 100;
            progressFill.style.width = progress + '%';
            progressText.textContent = `Question ${currentQuestion + 1} sur ${questions.length}`;
        }
        
        // Afficher la question actuelle
        function showQuestion(index) {
            questions.forEach((q, i) => {
                q.style.display = i === index ? 'block' : 'none';
            });
            
            // Gérer les boutons
            prevBtn.style.display = index > 0 ? 'inline-block' : 'none';
            nextBtn.style.display = index < questions.length - 1 ? 'inline-block' : 'none';
            submitBtn.style.display = index === questions.length - 1 ? 'inline-block' : 'none';
            
            // Mettre à jour la progression
            updateProgress();
            
            // Scroll vers le haut
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        // Sélectionner une réponse
        document.querySelectorAll('.answer-option').forEach(option => {
            option.addEventListener('click', function() {
                const questionIndex = this.getAttribute('data-question');
                const answerId = this.getAttribute('data-answer');
                
                // Désélectionner toutes les options de cette question
                document.querySelectorAll(`.answer-option[data-question="${questionIndex}"]`).forEach(opt => {
                    opt.classList.remove('selected');
                });
                
                // Sélectionner cette option
                this.classList.add('selected');
                
                // Cocher le radio correspondant
                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                }
            });
        });
        
        // Navigation
        prevBtn.addEventListener('click', function() {
            if (currentQuestion > 0) {
                currentQuestion--;
                showQuestion(currentQuestion);
            }
        });
        
        nextBtn.addEventListener('click', function() {
            // Vérifier qu'une réponse est sélectionnée
            const currentQuestionElement = questions[currentQuestion];
            const selectedAnswer = currentQuestionElement.querySelector('input[type="radio"]:checked');
            
            if (!selectedAnswer) {
                alert('Veuillez sélectionner une réponse avant de continuer.');
                return;
            }
            
            if (currentQuestion < questions.length - 1) {
                currentQuestion++;
                showQuestion(currentQuestion);
            }
        });
        
        // Validation finale avant soumission
        document.getElementById('quiz-form').addEventListener('submit', function(e) {
            // Vérifier que toutes les questions ont une réponse
            let allAnswered = true;
            
            for (let i = 0; i < questions.length; i++) {
                const questionElement = questions[i];
                const selectedAnswer = questionElement.querySelector('input[type="radio"]:checked');
                
                if (!selectedAnswer) {
                    allAnswered = false;
                    // Revenir à la question non répondue
                    currentQuestion = i;
                    showQuestion(currentQuestion);
                    alert(`Veuillez répondre à la question ${i + 1} avant de soumettre.`);
                    e.preventDefault();
                    break;
                }
            }
            
            if (allAnswered && !confirm('Êtes-vous sûr de vouloir soumettre vos réponses ? Vous ne pourrez plus les modifier.')) {
                e.preventDefault();
            }
        });
        
        // Initialiser
        updateProgress();
    });
    </script>
</body>
</html>