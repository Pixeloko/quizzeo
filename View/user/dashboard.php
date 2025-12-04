<?php
require_once __DIR__ . '/../../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "user") {
    header("Location: /quizzeo/?url=login");
    exit;
}

require_once __DIR__ . '/../../Model/function_user.php';

$user_id = $_SESSION['user_id'];
$quiz_count = getQuizCountByUser($user_id);
$last_quiz = getLastQuizScore($user_id);
$available_quizzes = getAvailableQuizzes($user_id); // Pour afficher le nombre de quiz disponibles

// Préparer l'affichage
$last_score_display = '--';
$last_quiz_date = 'Aucun';
$last_quiz_title = '';

if ($last_quiz) {
    $last_score_display = $last_quiz['score'] . '/' . $last_quiz['total_points'];
    $last_quiz_date = $last_quiz['formatted_date'] ?? date('d/m/Y', strtotime($last_quiz['completed_at']));
    $last_quiz_title = $last_quiz['quiz_title'];
    $last_quiz_percentage = $last_quiz['percentage'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Utilisateur - Quizzeo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
    body { background-color: #f8f9fa; }
    .card { border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .stat-card { border-left: 4px solid; }
    .stat-quiz { border-left-color: #007bff; }
    .stat-score { border-left-color: #ffc107; }
    .stat-date { border-left-color: #17a2b8; }
    .stat-available { border-left-color: #28a745; }
    .progress { height: 6px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <nav class="navbar navbar-light bg-white shadow-sm mb-4">
            <div class="container">
                <a href="/quizzeo/?url=logout" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Déconnexion
                </a>
            </div>
        </nav>

        <div class="container">
            <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4">
                <?= htmlspecialchars($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <h1 class="h3 mb-2">Bienvenue, <?= htmlspecialchars($_SESSION['firstname'] ?? 'Utilisateur'); ?> !</h1>
                    <p class="mb-0">Consultez vos performances et découvrez de nouveaux quiz</p>
                </div>
            </div>

            <!-- STATISTIQUES -->
            <div class="row mb-4">
                <!-- Quiz répondu -->
                <div class="col-md-3 mb-3">
                    <div class="card stat-card stat-quiz h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <h6 class="text-muted mb-1">Quiz répondu</h6>
                                    <h3 class="mb-0"><?= $quiz_count ?></h3>
                                </div>
                                <i class="bi bi-check-circle text-primary" style="font-size: 2rem;"></i>
                            </div>
                            <?php if ($quiz_count == 0): ?>
                            <small class="text-muted">Commencez votre premier quiz !</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Dernier score -->
                <div class="col-md-3 mb-3">
                    <div class="card stat-card stat-score h-100">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Dernier score</h6>
                            <?php if ($last_quiz): ?>
                            <h4 class="mb-1"><?= $last_score_display ?></h4>
                            <small class="text-muted d-block mb-2">
                                <?= htmlspecialchars($last_quiz_title) ?>
                            </small>
                            <div class="progress">
                                <div class="progress-bar 
                                    <?= ($last_quiz_percentage >= 70) ? 'bg-success' : 
                                        (($last_quiz_percentage >= 50) ? 'bg-warning' : 'bg-danger') ?>" 
                                     style="width: <?= $last_quiz_percentage ?>%">
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1"><?= $last_quiz_percentage ?>%</small>
                            <?php else: ?>
                            <div class="text-center py-3">
                                <i class="bi bi-trophy text-muted" style="font-size: 2rem;"></i>
                                <p class="small text-muted mt-2 mb-0">Pas encore de score</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Dernier quiz -->
                <div class="col-md-3 mb-3">
                    <div class="card stat-card stat-date h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Dernier quiz</h6>
                                    <h5 class="mb-0"><?= $last_quiz_date ?></h5>
                                </div>
                                <i style="font-size: 2rem;"></i>
                            </div>

                        </div>
                    </div>
                </div>


            <!-- BOUTON POUR COMMENCER UN QUIZ -->
            <?php if (count($available_quizzes) > 0): ?>
            <div class="text-center mb-4">
                <a href="/quizzeo/?url=home" class="btn btn-primary btn-lg">
                    <i class="bi bi-play-circle"></i> Commencer un nouveau quiz
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>