<?php
// View/user/dashboard.php

session_start();

// Vérifier si les variables sont définies, sinon les initialiser
if (!isset($answered_quizzes)) {
    $answered_quizzes = [];
}

if (!isset($available_quizzes)) {
    $available_quizzes = [];
}

// Vérifier l'authentification
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
    body {
        background-color: #f8f9fa;
    }

    .card {
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .welcome-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .stat-card {
        border-left: 4px solid;
    }

    .quiz-card {
        transition: transform 0.3s;
    }

    .quiz-card:hover {
        transform: translateY(-5px);
    }
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
                            <a class="nav-link active" href="/quizzeo/View/home.php">
                                <i class="bi bi-house"></i> Accueil
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
                        <h1 class="h2 mb-2">Bienvenue, <?= htmlspecialchars($_SESSION['firstname'] ?? 'Utilisateur'); ?>
                            !</h1>
                        <p class="mb-0">Testez vos connaissances avec nos quiz interactifs</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <?php if (!empty($available_quizzes)): ?>
                        <a href="/quizzeo/?url=quiz&id=<?= $available_quizzes[0]['id']; ?>"
                            class="btn btn-light btn-lg">
                            <i class="bi bi-rocket-takeoff"></i> Démarrer un quiz
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card stat-card h-100">
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
                    <div class="card stat-card h-100">
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
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Score moyen</h6>
                                    <h3 class="mb-0">
                                        <?php
                                        $total_score = 0;
                                        $quiz_count = 0;
                                        foreach ($answered_quizzes as $quiz) {
                                            if (isset($quiz['score']) && isset($quiz['question_count']) && $quiz['question_count'] > 0) {
                                                $score = ($quiz['score'] / ($quiz['question_count'] * 10)) * 100; // Supposons 10 pts max par question
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
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Dernier quiz</h6>
                                    <h6 class="mb-0">
                                        <?php
                                        if (!empty($answered_quizzes)) {
                                            $last_quiz = $answered_quizzes[0];
                                            echo date('d/m/Y', strtotime($last_quiz['created_at']));
                                        } else {
                                            echo 'Aucun';
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
                            <?php if (!empty($available_quizzes)): ?>
                            <a href="/quizzeo/?url=user&action=available_quizzes"
                                class="btn btn-sm btn-outline-primary">
                                Voir tous les quiz
                            </a>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <?php if (empty($answered_quizzes)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-question-circle text-muted" style="font-size: 3rem;"></i>
                                <h5 class="mt-3">Aucun quiz répondu</h5>
                                <p class="text-muted">Commencez par répondre à un quiz disponible</p>
                                <?php if (!empty($available_quizzes)): ?>
                                <a href="/quizzeo/?url=quiz&id=<?= $available_quizzes[0]['id']; ?>"
                                    class="btn btn-primary">
                                    <i class="bi bi-rocket-takeoff"></i> Démarrer un quiz
                                </a>
                                <?php endif; ?>
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
                                                <small
                                                    class="text-muted"><?= htmlspecialchars($quiz['description'] ?? ''); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?= isset($quiz['question_count']) ? $quiz['question_count'] : '?'; ?>
                                                    questions
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $score = isset($quiz['score']) ? $quiz['score'] : 0;
                                                $max_score = isset($quiz['question_count']) ? $quiz['question_count'] * 10 : 100;
                                                $percentage = $max_score > 0 ? round(($score / $max_score) * 100, 1) : 0;
                                                $badge_class = $percentage >= 70 ? 'bg-success' : 
                                                               ($percentage >= 50 ? 'bg-warning' : 'bg-danger');
                                                ?>
                                                <span class="badge <?= $badge_class; ?>">
                                                    <?= $score; ?> pts (<?= $percentage; ?>%)
                                                </span>
                                            </td>
                                            <td>
                                                <small><?= date('d/m/Y', strtotime($quiz['created_at'])); ?></small>
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





                            <?php if (!empty($display_quizzes)): ?>
                            <?php foreach (array_slice($display_quizzes, 0, 3) as $quiz): ?>
                            <div class="quiz-card card mb-3 border">
                                <div class="card-body">
                                    <h6 class="card-title"><?= htmlspecialchars($quiz['name']); ?></h6>

                                    <!-- Auteur du quiz -->
                                    <?php if (isset($quiz['creator_firstname'])): ?>
                                    <p class="small text-muted mb-1">
                                        <i class="bi bi-person"></i>
                                        <?= htmlspecialchars($quiz['creator_firstname'] . ' ' . $quiz['creator_lastname']) ?>
                                    </p>
                                    <?php endif; ?>

                                    <p class="card-text small text-muted">
                                        <i class="bi bi-question-circle"></i>
                                        <?= isset($quiz['question_count']) ? $quiz['question_count'] : '0'; ?> questions

                                        <!-- Indicateur si déjà répondu -->
                                        <?php 
                        $already_answered = false;
                        if (isset($_SESSION['user_id'])) {
                            $already_answered = hasUserAnsweredQuiz($_SESSION['user_id'], $quiz['id']);
                        }
                        ?>

                                        <?php if ($already_answered): ?>
                                        <span class="badge bg-warning float-end">
                                            <i class="bi bi-check-circle"></i> Déjà répondu
                                        </span>
                                        <?php endif; ?>
                                    </p>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <?php if (!$already_answered): ?>
                                        <a href="/quizzeo/?url=quiz&id=<?= $quiz['id']; ?>"
                                            class="btn btn-sm btn-primary">
                                            <i class="bi bi-play"></i> Commencer
                                        </a>
                                        <?php else: ?>
                                        <a href="/quizzeo/?url=user&action=quiz_results&id=<?= $quiz['id']; ?>"
                                            class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-eye"></i> Voir résultats
                                        </a>
                                        <?php endif; ?>

                                        <small class="text-muted">
                                            <i class="bi bi-calendar"></i>
                                            <?= date('d/m/Y', strtotime($quiz['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>

                            <?php if (count($display_quizzes) > 3): ?>
                            <div class="text-center mt-3">
                                <a href="/quizzeo/?url=user&action=available_quizzes"
                                    class="btn btn-outline-secondary btn-sm">
                                    Voir les <?= count($display_quizzes) - 3; ?> autres quiz
                                </a>
                            </div>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Bootstrap JS -->
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>