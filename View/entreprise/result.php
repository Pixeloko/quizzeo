<?php
session_start();
require_once __DIR__ . "/../../Model/function_quizz.php";

// Vérifier l'authentification et rôle
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ecole') { // ou 'entreprise'
    header("Location: /quizzeo/?url=login");
    exit;
}

// Récupérer l'ID du quiz
$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($quiz_id <= 0) {
    $_SESSION['error'] = "Quiz invalide.";
    header("Location: /quizzeo/?url=ecole");
    exit;
}

// Récupérer le quiz et les résultats
$quiz = getQuizzById($quiz_id);
$results = getQuizzResults($quiz_id) ?: [];

// Vérifier que l'utilisateur est le propriétaire
if (!$quiz || $quiz['user_id'] != $_SESSION['user_id']) {
    $_SESSION['error'] = "Accès non autorisé.";
    header("Location: /quizzeo/?url=ecole");
    exit;
}

// Calcul des statistiques
$participants = count($results);
$totalScore = array_sum(array_column($results, 'score'));
$moyenne = $participants > 0 ? round($totalScore / $participants, 2) : 0;
$maxScore = $participants > 0 ? max(array_column($results, 'score')) : 0;
$minScore = $participants > 0 ? min(array_column($results, 'score')) : 0;

// Classement avec gestion des égalités
usort($results, fn($a, $b) => $b['score'] <=> $a['score']);
$rank = 0;
$prevScore = null;
$actualRank = 0;
foreach ($results as &$result) {
    $rank++;
    if ($prevScore !== $result['score']) {
        $actualRank = $rank;
    }
    $result['rank'] = $actualRank;
    $prevScore = $result['score'];
}
unset($result); // break reference
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Résultats du Quiz - Quizzeo</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
<style>
.text-bronze { color: #cd7f32; }
</style>
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Résultats: <?= htmlspecialchars($quiz['name']); ?></h1>
        <div>
            <a href="/quizzeo/?url=ecole" class="btn btn-outline-secondary">← Retour au Dashboard</a>
            <button onclick="window.print()" class="btn btn-outline-primary">
                <i class="bi bi-printer"></i> Imprimer
            </button>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Participants</h5>
                    <h2 class="text-primary"><?= $participants; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Moyenne</h5>
                    <h2 class="text-info"><?= $moyenne; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Meilleure Note</h5>
                    <h2 class="text-success"><?= $maxScore; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Plus Basse Note</h5>
                    <h2 class="text-danger"><?= $minScore; ?></h2>
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
                            <?php foreach ($results as $result): ?>
                            <tr>
                                <td><?= $result['rank']; ?></td>
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
                                    <?php if ($result['rank'] == 1): ?>
                                        <span class="text-warning"><i class="bi bi-trophy-fill"></i> 1er</span>
                                    <?php elseif ($result['rank'] == 2): ?>
                                        <span class="text-secondary"><i class="bi bi-trophy"></i> 2ème</span>
                                    <?php elseif ($result['rank'] == 3): ?>
                                        <span class="text-bronze"><i class="bi bi-award"></i> 3ème</span>
                                    <?php else: ?>
                                        <?= $result['rank']; ?>ème
                                    <?php endif; ?>
                                </td>
                                <td><?= round(($result['score']/20)*100,1); ?>%</td>
                                <td>
                                    <a href="student_detail.php?quiz_id=<?= $quiz_id; ?>&student_id=<?= $result['user_id']; ?>" 
                                       class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-eye"></i> Voir
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

    <!-- Export -->
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
