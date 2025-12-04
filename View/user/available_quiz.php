<?php
// View/user/available_quizzes.php

session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "user") {
    header("Location: /quizzeo/?url=login");
    exit;
}

// Initialiser les variables si elles ne sont pas définies
if (!isset($all_quizzes)) {
    $all_quizzes = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tous les Quiz - Quizzeo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
    .quiz-card { transition: transform 0.3s; }
    .quiz-card:hover { transform: translateY(-5px); }
    .filter-badge { cursor: pointer; }
    .already-answered { opacity: 0.8; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-light bg-white shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="/quizzeo/?url=user">
                <i class="bi bi-arrow-left"></i> Retour au dashboard
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h2">Tous les Quiz</h1>
                <p class="text-muted">Découvrez tous les quiz disponibles sur Quizzeo</p>
            </div>
        </div>

        <!-- Filtres -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title mb-3">Filtrer par :</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-primary filter-badge" data-filter="all">
                                Tous (<?= count($all_quizzes); ?>)
                            </span>
                            <span class="badge bg-success filter-badge" data-filter="available">
                                Disponibles (<?= count(array_filter($all_quizzes, fn($q) => !$q['already_answered'])); ?>)
                            </span>
                            <span class="badge bg-warning filter-badge" data-filter="answered">
                                Déjà répondu (<?= count(array_filter($all_quizzes, fn($q) => $q['already_answered'])); ?>)
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des quiz -->
        <div class="row">
            <?php if (empty($all_quizzes)): ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="bi bi-question-circle text-muted" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">Aucun quiz disponible</h3>
                    <p class="text-muted">Revenez plus tard pour découvrir de nouveaux quiz</p>
                    <a href="/quizzeo/?url=user" class="btn btn-primary">
                        <i class="bi bi-house"></i> Retour au dashboard
                    </a>
                </div>
            </div>
            <?php else: ?>
            <?php foreach ($all_quizzes as $quiz): ?>
            <div class="col-md-6 col-lg-4 mb-4 quiz-item" 
                 data-answered="<?= $quiz['already_answered'] ? 'true' : 'false'; ?>">
                <div class="card quiz-card h-100 <?= $quiz['already_answered'] ? 'already-answered' : ''; ?>">
                    <div class="card-body d-flex flex-column">
                        <!-- En-tête du quiz -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="card-title mb-1"><?= htmlspecialchars($quiz['name']); ?></h5>
                                <?php if ($quiz['already_answered']): ?>
                                <span class="badge bg-warning">
                                    <i class="bi bi-check-circle"></i> Déjà répondu
                                </span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Description -->
                            <?php if (!empty($quiz['description'])): ?>
                            <p class="card-text small text-muted mb-2">
                                <?= htmlspecialchars(substr($quiz['description'], 0, 100)); ?>
                                <?php if (strlen($quiz['description']) > 100): ?>...<?php endif; ?>
                            </p>
                            <?php endif; ?>
                            
                            <!-- Informations -->
                            <div class="small text-muted">
                                <div class="mb-1">
                                    <i class="bi bi-question-circle"></i>
                                    <?= isset($quiz['question_count']) ? $quiz['question_count'] : '0'; ?> questions
                                </div>
                                <?php if (isset($quiz['creator_firstname'])): ?>
                                <div class="mb-1">
                                    <i class="bi bi-person"></i>
                                    <?= htmlspecialchars($quiz['creator_firstname'] . ' ' . $quiz['creator_lastname']) ?>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <i class="bi bi-calendar"></i>
                                    Créé le <?= date('d/m/Y', strtotime($quiz['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Boutons d'action -->
                        <div class="mt-auto">
                            <?php if (!$quiz['already_answered']): ?>
                            <a href="/quizzeo/?url=quiz&id=<?= $quiz['id']; ?>" 
                               class="btn btn-primary w-100">
                                <i class="bi bi-play-circle"></i> Commencer le quiz
                            </a>
                            <?php else: ?>
                            <div class="d-flex gap-2">
                                <a href="/quizzeo/?url=user&action=quiz_results&id=<?= $quiz['id']; ?>" 
                                   class="btn btn-outline-info flex-grow-1">
                                    <i class="bi bi-eye"></i> Voir résultats
                                </a>
                                <a href="/quizzeo/?url=quiz&id=<?= $quiz['id']; ?>" 
                                   class="btn btn-outline-secondary"
                                   onclick="return confirm('Rejouer ce quiz ? Votre ancien score sera conservé.')">
                                    <i class="bi bi-arrow-repeat"></i>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Filtrage des quiz
    document.addEventListener('DOMContentLoaded', function() {
        const filterBadges = document.querySelectorAll('.filter-badge');
        const quizItems = document.querySelectorAll('.quiz-item');
        
        filterBadges.forEach(badge => {
            badge.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');
                
                // Mettre à jour les badges actifs
                filterBadges.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                // Filtrer les quiz
                quizItems.forEach(item => {
                    const answered = item.getAttribute('data-answered');
                    
                    switch(filter) {
                        case 'all':
                            item.style.display = 'block';
                            break;
                        case 'available':
                            item.style.display = answered === 'false' ? 'block' : 'none';
                            break;
                        case 'answered':
                            item.style.display = answered === 'true' ? 'block' : 'none';
                            break;
                    }
                });
            });
        });
        
        // Activer le filtre "Tous" par défaut
        document.querySelector('.filter-badge[data-filter="all"]').classList.add('active');
    });
    </script>
</body>
</html>