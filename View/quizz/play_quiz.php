<?php
session_start();

// Vérifier si une session de quiz est en cours
if (!isset($_SESSION['quiz_session'])) {
    header("Location: /quizzeo/?url=home");
    exit;
}

$quiz_session = $_SESSION['quiz_session'];
$current_question_index = $quiz_session['current_question'];
$total_questions = count($quiz_session['questions']);

// Vérifier si le quiz est terminé
if ($current_question_index >= $total_questions) {
    $quiz_session['completed'] = true;
    $_SESSION['quiz_session'] = $quiz_session;
    header("Location: /quizzeo/View/quiz/quiz_results.php");
    exit;
}

// Récupérer la question actuelle
$current_question = $quiz_session['questions'][$current_question_index];

// Traitement de la réponse
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    $selected_answer_id = (int)$_POST['answer'];
    
    // Trouver la réponse sélectionnée
    $selected_answer = null;
    $correct_answer_id = null;
    
    foreach ($current_question['answers'] as $answer) {
        if ($answer['id'] == $selected_answer_id) {
            $selected_answer = $answer;
        }
        if ($answer['is_correct']) {
            $correct_answer_id = $answer['id'];
        }
    }
    
    // Enregistrer la réponse
    $_SESSION['quiz_session']['answers'][$current_question['id']] = [
        'selected_answer_id' => $selected_answer_id,
        'is_correct' => $selected_answer ? $selected_answer['is_correct'] : false,
        'points_earned' => $selected_answer && $selected_answer['is_correct'] ? $current_question['point'] : 0
    ];
    
    // Mettre à jour le score
    if ($selected_answer && $selected_answer['is_correct']) {
        $_SESSION['quiz_session']['score'] += $current_question['point'];
    }
    
    // Passer à la question suivante
    $_SESSION['quiz_session']['current_question']++;
    
    // Rediriger pour éviter le re-post
    header("Location: /quizzeo/View/quiz/play_quiz.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question <?= $current_question_index + 1; ?> - <?= htmlspecialchars($quiz_session['quiz_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .quiz-container {
        max-width: 800px;
        margin: 0 auto;
    }
    .question-card {
        border: none;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-radius: 15px;
        overflow: hidden;
    }
    .question-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
    }
    .progress-container {
        height: 10px;
        background-color: #e9ecef;
        border-radius: 5px;
        overflow: hidden;
        margin: 1rem 0;
    }
    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #28a745, #20c997);
        transition: width 0.3s ease;
    }
    .answer-option {
        border: 2px solid #dee2e6;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .answer-option:hover {
        border-color: #667eea;
        background-color: #f8f9fa;
    }
    .answer-option.selected {
        border-color: #28a745;
        background-color: #f0fff4;
    }
    .answer-radio {
        display: none;
    }
    .answer-label {
        display: block;
        width: 100%;
        cursor: pointer;
        margin: 0;
    }
    .question-points {
        background-color: #ffc107;
        color: #000;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-weight: bold;
    }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- En-tête du quiz -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 mb-1"><?= htmlspecialchars($quiz_session['quiz_name']); ?></h1>
                <?php if (!empty($quiz_session['player_name'])): ?>
                <p class="text-muted mb-0">Joueur : <?= htmlspecialchars($quiz_session['player_name']); ?></p>
                <?php endif; ?>
            </div>
            <div class="text-end">
                <div class="question-points d-inline-block me-3">
                    <i class="bi bi-star-fill"></i> <?= $current_question['point']; ?> points
                </div>
                <a href="/quizzeo/Controller/cancel_quiz.php" class="btn btn-outline-danger btn-sm" 
                   onclick="return confirm('Abandonner le quiz ? Votre progression sera perdue.')">
                    <i class="bi bi-x-circle"></i> Abandonner
                </a>
            </div>
        </div>

        <!-- Barre de progression -->
        <div class="progress-container">
            <div class="progress-bar" 
                 style="width: <?= (($current_question_index) / $total_questions) * 100; ?>%"></div>
        </div>
        
        <div class="d-flex justify-content-between mb-3">
            <small>Question <?= $current_question_index + 1; ?> sur <?= $total_questions; ?></small>
            <small><?= round((($current_question_index) / $total_questions) * 100); ?>% complété</small>
        </div>

        <!-- Carte de question -->
        <div class="question-card">
            <div class="question-header">
                <h2 class="h5 mb-0">Question <?= $current_question_index + 1; ?></h2>
            </div>
            
            <div class="card-body">
                <!-- Texte de la question -->
                <div class="mb-4">
                    <p class="h5"><?= htmlspecialchars($current_question['title']); ?></p>
                </div>

                <!-- Formulaire des réponses -->
                <form method="POST" id="quizForm">
                    <?php foreach ($current_question['answers'] as $index => $answer): ?>
                    <div class="answer-option" id="option-<?= $answer['id']; ?>">
                        <input type="radio" name="answer" value="<?= $answer['id']; ?>" 
                               id="answer-<?= $answer['id']; ?>" class="answer-radio">
                        <label for="answer-<?= $answer['id']; ?>" class="answer-label">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="rounded-circle border border-secondary d-flex align-items-center justify-content-center" 
                                         style="width: 30px; height: 30px;">
                                        <?= chr(65 + $index); ?> <!-- A, B, C, D... -->
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <?= htmlspecialchars($answer['answer_text']); ?>
                                </div>
                            </div>
                        </label>
                    </div>
                    <?php endforeach; ?>

                    <!-- Boutons de navigation -->
                    <div class="d-flex justify-content-between mt-4">
                        <div>
                            <?php if ($current_question_index > 0): ?>
                            <button type="button" class="btn btn-outline-secondary" id="prevBtn">
                                <i class="bi bi-arrow-left"></i> Question précédente
                            </button>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <button type="submit" class="btn btn-primary px-4" id="nextBtn">
                                <?php if ($current_question_index < $total_questions - 1): ?>
                                Question suivante <i class="bi bi-arrow-right"></i>
                                <?php else: ?>
                                Terminer le quiz <i class="bi bi-check-circle"></i>
                                <?php endif; ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Mini statistiques -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="card-title">Score actuel</h6>
                        <p class="h3 text-primary"><?= $quiz_session['score']; ?> pts</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="card-title">Questions restantes</h6>
                        <p class="h3 text-warning"><?= $total_questions - ($current_question_index + 1); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="card-title">Temps écoulé</h6>
                        <p class="h3 text-success">
                            <?php 
                            $elapsed = time() - $quiz_session['start_time'];
                            echo floor($elapsed / 60) . ':' . str_pad($elapsed % 60, 2, '0', STR_PAD_LEFT);
                            ?>
                        </p>
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
    document.addEventListener('DOMContentLoaded', function() {
        // Sélectionner une réponse
        document.querySelectorAll('.answer-option').forEach(option => {
            option.addEventListener('click', function() {
                // Désélectionner toutes les options
                document.querySelectorAll('.answer-option').forEach(opt => {
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

        // Validation avant soumission
        document.getElementById('quizForm').addEventListener('submit', function(e) {
            const selectedAnswer = document.querySelector('input[name="answer"]:checked');
            if (!selectedAnswer) {
                e.preventDefault();
                alert('Veuillez sélectionner une réponse avant de continuer.');
                return false;
            }
        });

        // Bouton précédent
        document.getElementById('prevBtn')?.addEventListener('click', function() {
            // Aller à la question précédente
            window.location.href = '/quizzeo/Controller/previous_question.php';
        });
    });
    </script>
</body>
</html>