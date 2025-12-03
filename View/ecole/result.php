<?php
session_start();
require_once __DIR__ . "/../../Model/function_quizz.php";

// Get quiz ID
$quiz_id = (int)($_GET['id'] ?? 0);
if ($quiz_id <= 0) {
    header("Location: dashboard.php");
    exit;
}

// Get quiz and results
$quiz = getQuizzById($quiz_id);
$results = getQuizzResults($quiz_id); 

// L'utilisateur est il le owner
if (!$quiz || $quiz['user_id'] != $_SESSION['user_id']) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats du Quiz - Quizzeo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Résultats: <?= htmlspecialchars($quiz['name']); ?></h1>
            <div>
                <a href="dashboard.php" class="btn btn-outline-secondary">← Retour au Dashboard</a>
                <button onclick="window.print()" class="btn btn-outline-primary">
                    <i class="bi bi-printer"></i> Imprimer
                </button>
            </div>
        </div>

        <!-- Résumé des statz -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Participants</h5>
                        <h2 class="text-primary"><?= count($results); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Moyenne</h5>
                        <h2 class="text-info">
                            <?php
                            $total = 0;
                            foreach ($results as $result) {
                                $total += $result['score'];
                            }
                            echo count($results) > 0 ? round($total / count($results), 2) : 0;
                            ?>
                        </h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Meilleure Note</h5>
                        <h2 class="text-success">
                            <?= count($results) > 0 ? max(array_column($results, 'score')) : 0; ?>
                        </h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Plus Basse Note</h5>
                        <h2 class="text-danger">
                            <?= count($results) > 0 ? min(array_column($results, 'score')) : 0; ?>
                        </h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau des résultats -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Détail des Résultats</h4>
            </div>
            <div class="card-body">
                <?php if (empty($results)): ?>
                    <p class="text-muted">Aucun résultat disponible pour ce quiz.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nom</th>
                                    <th>Note</th>
                                    <th>Classement</th>
                                    <th>Pourcentage</th>
                                    <th>Détails</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                $prevScore = null;
                                $actualRank = 0;
                                ?>
                                <?php foreach ($results as $index => $result): ?>
                                <?php
                                if ($prevScore !== $result['score']) {
                                    $actualRank = $rank;
                                }
                                $prevScore = $result['score'];
                                ?>
                                <tr>
                                    <td><?= $actualRank; ?></td>
                                    <td><?= htmlspecialchars($result['name']); ?></td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $result['score'] >= 15 ? 'success' : 
                                            ($result['score'] >= 10 ? 'warning' : 'danger') 
                                        ?>">
                                            <?= $result['score']; ?> / 20
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($actualRank == 1): ?>
                                            <span class="text-warning"><i class="bi bi-trophy-fill"></i> 1er</span>
                                        <?php elseif ($actualRank == 2): ?>
                                            <span class="text-secondary"><i class="bi bi-trophy"></i> 2ème</span>
                                        <?php elseif ($actualRank == 3): ?>
                                            <span class="text-bronze"><i class="bi bi-award"></i> 3ème</span>
                                        <?php else: ?>
                                            <?= $actualRank; ?>ème
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= round(($result['score'] / 20) * 100, 1); ?>%
                                    </td>
                                    <td>
                                        <a href="student_detail.php?quiz_id=<?= $quiz_id; ?>&student_id=<?= $result['user_id']; ?>" 
                                           class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-eye"></i> Voir
                                        </a>
                                    </td>
                                </tr>
                                <?php $rank++; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Export des résultats -->
        <div class="mt-4">
            <h5>Exporter les résultats:</h5>
            <a href="export_csv.php?quiz_id=<?= $quiz_id; ?>" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-excel"></i> CSV
            </a>
            <a href="export_pdf.php?quiz_id=<?= $quiz_id; ?>" class="btn btn-outline-danger">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
        </div>
    </div>

    <style>
        .text-bronze { color: #cd7f32; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>