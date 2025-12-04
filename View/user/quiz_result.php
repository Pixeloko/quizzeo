<?php
// View/user/quiz_result.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier l'authentification
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: /quizzeo/?url=login");
    exit;
}

require_once __DIR__ . '/../../Model/function_user.php';
$user_id = $_SESSION['user_id'];

// Récupérer les résultats
$results = getUserQuizResults($user_id);

// Calculer les totaux
$total_quizzes = count($results);
$total_score = 0;
$total_possible = 0;
$average_percentage = 0;

if ($total_quizzes > 0) {
    foreach ($results as $result) {
        $total_score += $result['score'] ?? 0;
        $total_possible += $result['total_points'] ?? 0;
    }
    if ($total_possible > 0) {
        $average_percentage = round(($total_score / $total_possible) * 100, 2);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Résultats - Quizzeo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .progress { height: 25px; }
        .progress-bar { line-height: 25px; }
        .stats-card { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Mes Résultats</h1>
        
        <!-- Statistiques globales -->
        <div class="row mb-4 stats-card">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3>Mes Statistiques Globales</h3>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="display-4 text-primary"><?= $total_quizzes ?></div>
                                <p class="text-muted">Quiz Complétés</p>
                            </div>
                            <div class="col-md-3">
                                <div class="display-4 text-success"><?= $total_score ?></div>
                                <p class="text-muted">Points Obtenus</p>
                            </div>
                            <div class="col-md-3">
                                <div class="display-4 text-info"><?= $total_possible ?></div>
                                <p class="text-muted">Points Possibles</p>
                            </div>
                            <div class="col-md-3">
                                <div class="display-4 text-warning"><?= $average_percentage ?>%</div>
                                <p class="text-muted">Moyenne</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Historique des Quiz</h2>
            <a href="/quizzeo/?url=user" class="btn btn-secondary">← Retour au dashboard</a>
        </div>
        
        <?php if (!empty($results)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Quiz</th>
                            <th>Date</th>
                            <th>Score</th>
                            <th>Performance</th>
                            <th>Détails</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $result): 
                            $percentage = $result['percentage'] ?? 0;
                            if (isset($result['total_points']) && $result['total_points'] > 0) {
                                $percentage = round(($result['score'] / $result['total_points']) * 100, 2);
                            }
                            
                            // Couleur selon la performance
                            $bar_class = 'bg-info';
                            $text_class = '';
                            if ($percentage >= 80) {
                                $bar_class = 'bg-success';
                                $text_class = 'text-success';
                            } elseif ($percentage >= 60) {
                                $bar_class = 'bg-warning';
                                $text_class = 'text-warning';
                            } elseif ($percentage >= 40) {
                                $bar_class = 'bg-info';
                                $text_class = 'text-info';
                            } else {
                                $bar_class = 'bg-danger';
                                $text_class = 'text-danger';
                            }
                        ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($result['quiz_title'] ?? 'Quiz') ?></strong>
                                </td>
                                <td>
                                    <?php 
                                    $date = $result['completed_at'] ?? '';
                                    echo $date ? date('d/m/Y H:i', strtotime($date)) : 'Inconnu';
                                    ?>
                                </td>
                                <td>
                                    <span class="fw-bold <?= $text_class ?>">
                                        <?= $result['score'] ?? 0 ?>
                                        <?php if (isset($result['total_points'])): ?>
                                            /<?= $result['total_points'] ?>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2">
                                            <div class="progress-bar <?= $bar_class ?>" 
                                                 style="width: <?= $percentage ?>%"
                                                 role="progressbar" 
                                                 aria-valuenow="<?= $percentage ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                        <span class="fw-bold"><?= $percentage ?>%</span>
                                    </div>
                                </td>
                                <td>
                                    <a href="/quizzeo/?url=user&action=quiz_detail&id=<?= $result['quiz_id'] ?? 0 ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        Détails
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                <div class="py-5">
                    <h4><i class="bi bi-graph-up"></i> Vous n'avez pas encore de résultats</h4>
                    <p class="lead">Participez à votre premier quiz pour voir vos statistiques ici !</p>
                    <a href="/quizzeo/?url=home" class="btn btn-primary btn-lg mt-3">
                        <i class="bi bi-play-circle"></i> Découvrir les quiz disponibles
                    </a>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Graphique des performances (optionnel) -->
        <?php if (!empty($results) && count($results) > 1): ?>
        <div class="card mt-4">
            <div class="card-header bg-secondary text-white">
                <h4>Évolution de vos performances</h4>
            </div>
            <div class="card-body">
                <canvas id="performanceChart" height="100"></canvas>
            </div>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [
                    <?php 
                    $dates = [];
                    foreach ($results as $result) {
                        $date = $result['completed_at'] ?? '';
                        if ($date) {
                            $dates[] = "'" . date('d/m', strtotime($date)) . "'";
                        }
                    }
                    echo implode(', ', array_reverse($dates));
                    ?>
                ].reverse(),
                datasets: [{
                    label: 'Score (%)',
                    data: [
                        <?php 
                        $percentages = [];
                        foreach ($results as $result) {
                            $percentages[] = $result['percentage'] ?? 0;
                        }
                        echo implode(', ', array_reverse($percentages));
                        ?>
                    ].reverse(),
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1,
                    fill: true,
                    backgroundColor: 'rgba(75, 192, 192, 0.1)'
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
        </script>
        <?php endif; ?>
    </div>
</body>
</html>