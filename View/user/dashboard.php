<?php
// View/user/dashboard.php

session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "user") {
    header("Location: /quizzeo/?url=login");
    exit;
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
    .welcome-section { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
    .stat-card { border-left: 4px solid; }
    .stat-card.primary { border-left-color: #007bff; }
    .stat-card.success { border-left-color: #28a745; }
    .stat-card.warning { border-left-color: #ffc107; }
    .stat-card.info { border-left-color: #17a2b8; }
    .quiz-card { transition: transform 0.3s; }
    .quiz-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
            <div class="container">
                <a class="navbar-brand fw-bold text-primary" href="/quizzeo/?url=user">
                    <i class="bi bi-question-circle"></i> Quizzeo
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link active" href="/quizzeo/?url=user">
                                <i class="bi bi-house"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/quizzeo/?url=user&action=available_quizzes">
                                <i class="bi bi-list-check"></i> Quiz disponibles
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/quizzeo/?url=user&action=profile">
                                <i class="bi bi-person"></i> Mon profil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="/quizzeo/?url=logout">
                                <i class="bi bi-box-arrow-right"></i> Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container">
            <!-- Messages -->
            <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4">
                <?= htmlspecialchars($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4">
                <?= htmlspecialchars($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Section Bienvenue -->
            <div class="welcome-section rounded-3 p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="h2 mb-2">Bienvenue, <?= htmlspecialchars($_SESSION['first_name'] ?? 'Utilisateur'); ?> !</h1>
                        <p class="mb-0">Testez vos connaissances avec nos quiz interactifs</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="/quizzeo/?url=user&action=available_quizzes" class="btn btn-light btn-lg">
                            <i class="bi bi-rocket-takeoff"></i> Découvrir les quiz
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card stat-card primary h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Quiz répondu</h6>
                                    <h3 class="mb-0"><?= count($answered_quizzes); ?></h3>
                                </div>
                                <i class="bi bi-check-circle text-primary" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card success h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Disponible</h6>
                                    <h3 class="mb-0"><?= count($available_quizzes); ?></h3>
                                </div>
                                <i class="bi bi-question-circle text-success" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card warning h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Score moyen</h6>
                                    <h3 class="mb-0">
                                        <?php
                                        $total_score = 0;
                                        $quiz_count = 0;
                                        foreach ($answered_quizzes as $quiz) {
                                            if ($quiz['question_count'] > 0) {
                                                $score = ($quiz['correct_count'] / $quiz['question_count']) * 100;
                                                $total_score += $score;
                                                $quiz_count++;
                                            }
                                        }
                                        echo $quiz_count > 0 ? round($total_score / $quiz_count, 1) . '%' : '0%';
                                        ?>
                                    </h3>
                                </div>
                                <i class="bi bi-graph-up text-warning" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card info h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Activité</h6>
                                    <h6 class="mb-0">
                                        <?php
                                        if (!empty($answered_quizzes)) {
                                            $last_quiz = $answered_quizzes[0];
                                            echo date('d/m/Y', strtotime($last_quiz['last_answered']));
                                        } else {
                                            echo 'Aucune';
                                        }
                                        ?>
                                    </h6>
                                </div>
                                <i class="bi bi-calendar-check text-info" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quiz récemment répondu -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-clock-history"></i> Derniers quiz répondu</h5>
                            <a href="/quizzeo/?url=user&action=available_quizzes" class="btn btn-sm btn-outline-primary">
                                Voir tous les quiz
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($answered_quizzes)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-question-circle text-muted" style="font-size: 3rem;"></i>
                                <h5 class="mt-3">Aucun quiz répondu</h5>
                                <p class="text-muted">Commencez par répondre à un quiz disponible</p>
                                <a href="/quizzeo/?url=user&action=available_quizzes" class="btn btn-primary">
                                    <i class="bi bi-rocket-takeoff"></i> Découvrir les quiz
                                </a>
                            </div>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Quiz</th>
                                            <th>Questions</th>
                                            <th>Score</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($answered_quizzes, 0, 5) as $quiz): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($quiz['name']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($quiz['description'] ?? ''); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?= $quiz['answered_count']; ?>/<?= $quiz['question_count']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $percentage = $quiz['question_count'] > 0 
                                                    ? round(($quiz['correct_count'] / $quiz['question_count']) * 100, 1) 
                                                    : 0;
                                                $badge_class = $percentage >= 70 ? 'bg-success' : 
                                                               ($percentage >= 50 ? 'bg-warning' : 'bg-danger');
                                                ?>
                                                <span class="badge <?= $badge_class; ?>">
                                                    <?= $percentage; ?>%
                                                </span>
                                            </td>
                                            <td>
                                                <small><?= date('d/m/Y', strtotime($quiz['last_answered'])); ?></small>
                                            </td>
                                            <td>
                                                <a href="/quizzeo/?url=user&action=quiz_results&id=<?= $quiz['id']; ?>" 
                                                   class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-eye"></i> Résultats
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quiz disponibles récents -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-lightning"></i> Quiz disponibles</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($available_quizzes)): ?>
                            <div class="text-center py-3">
                                <i class="bi bi-emoji-smile text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted mt-2 mb-0">Aucun nouveau quiz disponible</p>
                            </div>
                            <?php else: ?>
                            <?php foreach (array_slice($available_quizzes, 0, 3) as $quiz): ?>
                            <div class="quiz-card card mb-3 border">
                                <div class="card-body">
                                    <h6 class="card-title"><?= htmlspecialchars($quiz['name']); ?></h6>
                                    <p class="card-text small text-muted">
                                        <?= $quiz['question_count']; ?> questions
                                        <?php if ($quiz['already_answered'] > 0): ?>
                                        <span class="badge bg-warning float-end">
                                            Déjà commencé
                                        </span>
                                        <?php endif; ?>
                                    </p>
                                    <div class="d-flex justify-content-between">
                                        <a href="/quizzeo/?url=quiz&id=<?= $quiz['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="bi bi-play"></i> Commencer
                                        </a>
                                        <small class="text-muted">
                                            Créé le <?= date('d/m/Y', strtotime($quiz['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php if (count($available_quizzes) > 3): ?>
                            <div class="text-center mt-3">
                                <a href="/quizzeo/?url=user&action=available_quizzes" 
                                   class="btn btn-outline-secondary btn-sm">
                                    Voir les <?= count($available_quizzes) - 3; ?> autres quiz
                                </a>
                            </div>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Accès rapide -->
                    <div class="card mt-4">
                        <div class="card-body">
                            <h6 class="card-title mb-3"><i class="bi bi-link-45deg"></i> Accès rapide</h6>
                            <div class="d-grid gap-2">
                                <a href="/quizzeo/?url=user&action=available_quizzes" class="btn btn-outline-primary">
                                    <i class="bi bi-list-check"></i> Tous les quiz
                                </a>
                                <a href="/quizzeo/?url=user&action=profile" class="btn btn-outline-secondary">
                                    <i class="bi bi-gear"></i> Mon profil
                                </a>
                                <button class="btn btn-outline-info" onclick="shareQuizzeo()">
                                    <i class="bi bi-share"></i> Partager Quizzeo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-5 py-4 border-top text-center">
            <div class="container">
                <p class="text-muted mb-0">
                    &copy; <?= date('Y'); ?> Quizzeo. Tous droits réservés.
                    <span class="mx-2">|</span>
                    <a href="/quizzeo/?url=user&action=profile" class="text-decoration-none">Mon compte</a>
                    <span class="mx-2">|</span>
                    <a href="#" class="text-decoration-none">Contact</a>
                </p>
            </div>
        </footer>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function shareQuizzeo() {
        if (navigator.share) {
            navigator.share({
                title: 'Quizzeo - Plateforme de quiz interactifs',
                text: 'Découvrez Quizzeo, une plateforme de quiz interactifs pour tester vos connaissances !',
                url: window.location.origin
            });
        } else {
            alert('Partagez ce lien : ' + window.location.origin);
        }
    }
    </script>
</body>
</html>