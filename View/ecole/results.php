<?php
// View/ecole/results.php
if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../includes/header.php";

// Vérifier le rôle école
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "ecole") {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . "/../../Model/function_quizz.php";
require_once __DIR__ . "/../../Model/function_user.php";

// Vérifier l'ID du quiz
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Quiz invalide";
    header("Location: dashboard.php");
    exit;
}

$quiz_id = (int)$_GET['id'];

// Vérifier que le quiz appartient à l'utilisateur
$quiz = getQuizzById($quiz_id);
if (!$quiz || $quiz['user_id'] != $_SESSION['user_id']) {
    $_SESSION['error'] = "Accès non autorisé à ce quiz";
    header("Location: dashboard.php");
    exit;
}

// Récupérer les résultats
$results = getQuizzResults($quiz_id);
$total_submissions = countSubmissions($quiz_id);
$average_score = getQuizAverageScore($quiz_id);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats du Quiz - Quizzeo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .results-table th {
            background-color: #f8f9fa;
            position: sticky;
            top: 0;
        }
        .score-cell {
            font-weight: bold;
            text-align: center;
        }
        .percentage-cell {
            text-align: center;
            min-width: 100px;
        }
        .badge-percentage {
            font-size: 0.9em;
            padding: 4px 8px;
        }
        .stats-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .export-btn {
            margin-left: 10px;
        }
        .back-link {
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <!-- Bouton retour -->
        <div class="mb-4">
            <a href="dashboard.php" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Retour au dashboard
            </a>
        </div>

        <!-- Messages d'erreur/succès -->
        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); ?></div>
        <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        


        <!-- Table des résultats -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($results)): ?>
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle me-2"></i>
                        Aucun résultat disponible pour le moment. Aucun étudiant n'a encore répondu à ce quiz.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover results-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Email</th>
                                    <th>Score</th>
                                    <th>Pourcentage</th>
                                    <th>Terminé le</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $index => $result): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($result['lastname'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($result['firstname'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($result['email'] ?? '') ?></td>
                                        <td class="score-cell"><?= $result['score_display'] ?? '0/0' ?></td>
                                        <td class="percentage-cell">
                                            <?php 
                                            $percentage = $result['percentage'] ?? 0;
                                            $badge_class = 'bg-';
                                            if ($percentage >= 80) $badge_class .= 'success';
                                            elseif ($percentage >= 60) $badge_class .= 'warning';
                                            else $badge_class .= 'danger';
                                            ?>
                                            <span class="badge <?= $badge_class ?> badge-percentage">
                                                <?= $percentage ?>%
                                            </span>
                                        </td>
                                        <td><?= $result['completed_formatted'] ?? 'Non disponible' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>