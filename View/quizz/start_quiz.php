<?php
session_start();
require_once __DIR__ . "/../../Model/function_quizz.php";

// Récupérer l'ID du quiz
$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($quiz_id <= 0) {
    header("Location: /quizzeo/?url=home");
    exit;
}

// Récupérer le quiz
$quiz = getQuizzById($quiz_id);
if (!$quiz) {
    $_SESSION['error'] = "Quiz non trouvé";
    header("Location: /quizzeo/?url=home");
    exit;
}

// Vérifier si le quiz est actif
if (!$quiz['is_active']) {
    $_SESSION['error'] = "Ce quiz n'est pas disponible pour le moment";
    header("Location: /quizzeo/?url=home");
    exit;
}

// Si l'utilisateur est connecté et c'est son quiz, lui permettre de le lancer même s'il n'est pas actif
$is_owner = false;
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'ecole') {
    if ($quiz['user_id'] == $_SESSION['user_id']) {
        $is_owner = true;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Démarrer le Quiz - <?= htmlspecialchars($quiz['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .quiz-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    .quiz-stats {
        background-color: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .start-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 1rem 3rem;
        font-size: 1.2rem;
        transition: transform 0.3s;
    }
    .start-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="quiz-header text-center">
            <h1 class="display-5 fw-bold"><?= htmlspecialchars($quiz['name']); ?></h1>
            <p class="lead"><?= htmlspecialchars($quiz['description']); ?></p>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="quiz-stats mb-4">
                    <h4 class="mb-4">📋 Informations sur le quiz</h4>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle p-3 me-3">
                                    <i class="bi bi-question-circle" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Nombre de questions</h6>
                                    <p class="h4 mb-0"><?= $quiz['question_count']; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-success text-white rounded-circle p-3 me-3">
                                    <i class="bi bi-clock" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Temps estimé</h6>
                                    <p class="h4 mb-0"><?= $quiz['question_count'] * 1.5; ?> min</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning text-white rounded-circle p-3 me-3">
                                    <i class="bi bi-star" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Points maximum</h6>
                                    <p class="h4 mb-0"><?= $quiz['total_points']; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-info text-white rounded-circle p-3 me-3">
                                    <i class="bi bi-bar-chart" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Difficulté</h6>
                                    <p class="h4 mb-0">
                                        <?php 
                                        $avg_points = $quiz['total_points'] / max($quiz['question_count'], 1);
                                        if ($avg_points <= 2) echo "Facile";
                                        elseif ($avg_points <= 3) echo "Moyen";
                                        else echo "Difficile";
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="quiz-stats">
                    <h4 class="mb-4">📝 Instructions</h4>
                    <ul class="list-unstyled">
                        <li class="mb-2">✅ <strong>Une seule réponse possible</strong> par question</li>
                        <li class="mb-2">⏱️ <strong>Pas de limite de temps</strong> - Prenez votre temps</li>
                        <li class="mb-2">📊 <strong>Score immédiat</strong> - Voir vos résultats à la fin</li>
                        <li class="mb-2">🔄 <strong>Navigation libre</strong> - Retournez aux questions précédentes</li>
                        <li class="mb-2">🎯 <strong>Pour réussir</strong> : Répondez correctement au maximum de questions</li>
                    </ul>
                    
                    <?php if (!$quiz['is_active'] && $is_owner): ?>
                    <div class="alert alert-warning mt-3">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Note :</strong> Ce quiz n'est pas public. Seul vous (le créateur) pouvez le tester.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="quiz-stats text-center h-100 d-flex flex-column justify-content-center">
                    <div class="mb-4">
                        <i class="bi bi-trophy" style="font-size: 4rem; color: #ffc107;"></i>
                        <h3 class="mt-3">Prêt à jouer ?</h3>
                        <p class="text-muted">Testez vos connaissances maintenant !</p>
                    </div>
                    
                    <form action="/quizzeo/Controller/start_quiz_session.php" method="POST">
                        <input type="hidden" name="quiz_id" value="<?= $quiz_id; ?>">
                        
                        <div class="mb-3">
                            <label for="player_name" class="form-label">Votre nom (optionnel)</label>
                            <input type="text" class="form-control" id="player_name" name="player_name" 
                                   placeholder="Ex: Jean Dupont" maxlength="50">
                            <small class="text-muted">Pour personnaliser vos résultats</small>
                        </div>
                        
                        <button type="submit" class="btn start-btn text-white w-100 py-3">
                            <i class="bi bi-play-circle"></i> COMMENCER LE QUIZ
                        </button>
                    </form>
                    
                    <div class="mt-4">
                        <a href="/quizzeo/?url=home" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-arrow-left"></i> Retour à l'accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>