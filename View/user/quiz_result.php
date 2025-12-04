<?php
// View/user/quiz_results.php

session_start();

// Vérifier l'authentification
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "user") {
    header("Location: /quizzeo/?url=login");
    exit;
}

// Récupérer l'ID du quiz
$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($quiz_id <= 0) {
    header("Location: /quizzeo/?url=user");
    exit;
}

// Charger les modèles
require_once __DIR__ . '/../Model/function_quizz.php';
require_once __DIR__ . '/../Model/function_user.php';

$user_id = $_SESSION['user_id'];

// Récupérer les résultats depuis la session ou la BDD
if (isset($_SESSION['quiz_results']) && $_SESSION['quiz_results']['quiz_id'] == $quiz_id) {
    $results = $_SESSION['quiz_results'];
    // Nettoyer la session après utilisation
    unset($_SESSION['quiz_results']);
} else {
    // Récupérer depuis la BDD
    $results = getUserQuizResults($user_id, $quiz_id);
}

// Récupérer les infos du quiz
$quiz = getQuizzById($quiz_id);

// Déterminer le niveau selon le score
$percentage = $results['score_percentage'];
if ($percentage >= 90) {
    $level = "Excellent !";
    $level_class = "text-success";
    $icon = "bi-trophy";
} elseif ($percentage >= 70) {
    $level = "Très bien !";
    $level_class = "text-primary";
    $icon = "bi-award";
} elseif ($percentage >= 50) {
    $level = "Passable";
    $level_class = "text-warning";
    $icon = "bi-check-circle";
} else {
    $level = "À améliorer";
    $level_class = "text-danger";
    $icon = "bi-emoji-frown";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats du Quiz - <?= htmlspecialchars($quiz['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
    .results-header {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        border-radius: 15px;
        padding: 3rem 2rem;
        margin-bottom: 2rem;
    }
    .score-circle {
        width: 150px;
        height: 150px;
        border: 10px solid;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }
    .question-review {
        border-left: 4px solid #28a745;
        padding-left: 1rem;
        margin-bottom: 1rem;
    }
    .question-review.incorrect {
        border-left-color: #dc3545;
    }
    .correct-answer {
        color: #28a745;
        font-weight: bold;
    }
    .incorrect-answer {
        color: #dc3545;
        font-weight: bold;
    }
    .progress-bar-custom {
        height: 25px;
        font-size: 14px;
        font-weight: bold;
    }
    </style>
</head>
<body>
    <div class="container py-5">
        <!-- Navigation -->
        <nav class="navbar navbar-light bg-white rounded-3 shadow-sm mb-4">
            <div class="container-fluid">
                <a class="navbar-brand fw-bold text-primary" href="/quizzeo/?url=user">
                    <i class="bi bi-arrow-left"></i> Retour au dashboard
                </a>
            </div>
        </nav>

        <!-- Messages -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <?= htmlspecialchars($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- En-tête des résultats -->
        <div class="results-header text-center">
            <h1 class="display-4 fw-bold">Quiz Terminé !</h1>
            <p class="h3"><?= htmlspecialchars($quiz['name']); ?></p>
            <p class="h6 mt-3">
                Terminé le <?= date('d/m/Y à H:i'); ?>
            </p>
        </div>

        <!-- Score principal -->
        <div class="row justify-content-center mb-5">
            <div class="col-md-6 text-center">
                <div class="score-circle <?= $level_class; ?>" 
                     style="border-color: <?= 
                        $percentage >= 90 ? '#28a745' : 
                        ($percentage >= 70 ? '#007bff' : 
                        ($percentage >= 50 ? '#ffc107' : '#dc3545')); ?>">
                    <div>
                        <h1 class="display-2 fw-bold"><?= $percentage; ?>%</h1>
                        <p class="h5">Score</p>
                    </div>
                </div>
                
                <div class="mt-4">
                    <h2 class="<?= $level_class; ?>">
                        <i class="bi <?= $icon; ?>"></i> <?= $level; ?>
                    </h2>
                    <p class="lead h4">
                        <?= $results['earned_points']; ?> points sur <?= $results['total_points']; ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Statistiques détaillées -->
        <div class="row mb-5">
            <div class="col-md-3 mb-3">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 2rem;"></i>
                        <h3 class="mt-2">
                            <?php 
                            $correct_count = 0;
                            foreach ($results['answers'] as $answer) {
                                if ($answer['is_correct']) $correct_count++;
                            }
                            echo $correct_count;
                            ?>
                        </h3>
                        <p class="text-muted">Réponses correctes</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-x-circle-fill text-danger" style="font-size: 2rem;"></i>
                        <h3 class="mt-2">
                            <?= count($results['answers']) - $correct_count; ?>
                        </h3>
                        <p class="text-muted">Réponses incorrectes</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-lightning-fill text-warning" style="font-size: 2rem;"></i>
                        <h3 class="mt-2"><?= $results['earned_points']; ?></h3>
                        <p class="text-muted">Points obtenus</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-bar-chart-fill text-info" style="font-size: 2rem;"></i>
                        <h3 class="mt-2"><?= count($results['answers']); ?></h3>
                        <p class="text-muted">Questions totales</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barre de progression -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Progression</h5>
            </div>
            <div class="card-body">
                <div class="progress progress-bar-custom mb-3">
                    <div class="progress-bar bg-success" 
                         role="progressbar" 
                         style="width: <?= $percentage; ?>%"
                         aria-valuenow="<?= $percentage; ?>" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                        <?= $percentage; ?>%
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <p class="mb-1">Score obtenu</p>
                        <h4><?= $results['earned_points']; ?>/<?= $results['total_points']; ?></h4>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1">Pourcentage</p>
                        <h4><?= $percentage; ?>%</h4>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1">Taux de réussite</p>
                        <h4>
                            <?= count($results['answers']) > 0 
                                ? round(($correct_count / count($results['answers'])) * 100, 1) 
                                : 0; ?>%
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revue détaillée des questions -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="bi bi-list-check"></i> Revue détaillée des questions</h4>
                <button class="btn btn-sm btn-outline-secondary" onclick="toggleAllReviews()">
                    <i class="bi bi-arrows-expand"></i> Tout développer/réduire
                </button>
            </div>
            <div class="card-body">
                <?php foreach ($results['answers'] as $index => $answer): ?>
                <div class="question-review <?= $answer['is_correct'] ? '' : 'incorrect'; ?> mb-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="mb-0">
                            Question <?= $index + 1; ?>
                            <span class="badge bg-<?= $answer['is_correct'] ? 'success' : 'danger'; ?> ms-2">
                                <?= $answer['is_correct'] ? '+' . $answer['question_points'] . ' pts' : '0 pt'; ?>
                            </span>
                        </h5>
                        <span class="badge bg-<?= $answer['is_correct'] ? 'success' : 'danger'; ?>">
                            <?= $answer['is_correct'] ? 'Correct' : 'Incorrect'; ?>
                        </span>
                    </div>
                    
                    <p class="fw-bold mb-3"><?= htmlspecialchars($answer['question_text']); ?></p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card h-100 <?= $answer['is_correct'] ? 'border-success' : 'border-danger'; ?>">
                                <div class="card-body">
                                    <h6 class="card-title">Votre réponse</h6>
                                    <p class="card-text <?= $answer['is_correct'] ? 'correct-answer' : 'incorrect-answer'; ?>">
                                        <?= !empty($answer['selected_answer_text']) 
                                            ? htmlspecialchars($answer['selected_answer_text']) 
                                            : '<span class="text-muted">Non répondu</span>'; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!$answer['is_correct'] && !empty($answer['correct_answer_text'])): ?>
                        <div class="col-md-6">
                            <div class="card h-100 border-success">
                                <div class="card-body">
                                    <h6 class="card-title">Bonne réponse</h6>
                                    <p class="card-text correct-answer">
                                        <?= htmlspecialchars($answer['correct_answer_text']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Actions -->
        <div class="d-flex justify-content-center gap-3">
            <a href="/quizzeo/?url=quiz&id=<?= $quiz_id; ?>" 
               class="btn btn-primary btn-lg" 
               onclick="return confirm('Rejouer ce quiz ? Votre ancien score sera conservé.')">
                <i class="bi bi-arrow-repeat"></i> Rejouer
            </a>
            
            <a href="/quizzeo/?url=user" class="btn btn-outline-secondary btn-lg">
                <i class="bi bi-house"></i> Retour au dashboard
            </a>
            
            <button onclick="window.print()" class="btn btn-outline-info btn-lg">
                <i class="bi bi-printer"></i> Imprimer
            </button>
            
            <button onclick="shareResults()" class="btn btn-outline-success btn-lg">
                <i class="bi bi-share"></i> Partager
            </button>
        </div>

        <!-- Conseils d'amélioration -->
        <?php if ($percentage < 70): ?>
        <div class="card mt-4 border-warning">
            <div class="card-header bg-warning bg-opacity-10">
                <h5 class="mb-0"><i class="bi bi-lightbulb"></i> Conseils pour améliorer votre score</h5>
            </div>
            <div class="card-body">
                <ul>
                    <?php if ($percentage < 50): ?>
                    <li>Revisez attentivement les questions que vous avez ratées</li>
                    <li>Prenez votre temps pour lire chaque question avant de répondre</li>
                    <li>Éliminez d'abord les réponses manifestement fausses</li>
                    <?php else: ?>
                    <li>Vous êtes sur la bonne voie ! Continuez à vous entraîner</li>
                    <li>Concentrez-vous sur les types de questions que vous avez manquées</li>
                    <?php endif; ?>
                    <li>Vous pouvez rejouer ce quiz pour vous améliorer</li>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function toggleAllReviews() {
        const reviews = document.querySelectorAll('.question-review');
        const allExpanded = reviews[0].classList.contains('expanded');
        
        reviews.forEach(review => {
            if (allExpanded) {
                review.classList.remove('expanded');
            } else {
                review.classList.add('expanded');
            }
        });
    }
    
    function shareResults() {
        const score = <?= $percentage; ?>;
        const quizName = "<?= addslashes($quiz['name']); ?>";
        const message = `J'ai obtenu ${score}% au quiz "${quizName}" sur Quizzeo !`;
        
        if (navigator.share) {
            navigator.share({
                title: 'Mes résultats Quizzeo',
                text: message,
                url: window.location.href
            });
        } else {
            navigator.clipboard.writeText(message + '\n' + window.location.href).then(() => {
                alert('Résultats copiés dans le presse-papier !');
            });
        }
    }
    
    // Imprimer uniquement la partie résultats
    function printResults() {
        const printContent = document.querySelector('.container').innerHTML;
        const originalContent = document.body.innerHTML;
        
        document.body.innerHTML = printContent;
        window.print();
        document.body.innerHTML = originalContent;
        location.reload();
    }
    </script>
</body>
</html>