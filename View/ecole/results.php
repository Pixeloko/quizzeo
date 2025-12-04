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
                                    <th>Actions</th>
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
                                        <td>
                                            <button class="btn btn-sm btn-outline-info" 
                                                    onclick="showUserDetails(<?= $result['user_id'] ?? 0 ?>)"
                                                    title="Voir détails">
                                                <i class="bi bi-eye"></i>
                                            </button>
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

    <!-- Modal pour les détails utilisateur -->
    <div class="modal fade" id="userDetailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Détails de l'utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="userDetailsContent">
                    <!-- Contenu chargé via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Fonction pour afficher les détails d'un utilisateur
    function showUserDetails(userId) {
        // Ici, vous pourriez faire un appel AJAX pour récupérer plus de détails
        // Pour l'instant, affichons juste un message
        document.getElementById('userDetailsContent').innerHTML = `
            <div class="text-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="mt-2">Chargement des détails...</p>
            </div>
        `;
        
        var modal = new bootstrap.Modal(document.getElementById('userDetailsModal'));
        modal.show();
        
        // Simuler un chargement AJAX
        setTimeout(function() {
            document.getElementById('userDetailsContent').innerHTML = `
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Fonctionnalité en cours de développement.
                </div>
                <p>Prochaines fonctionnalités prévues :</p>
                <ul>
                    <li>Historique complet des réponses</li>
                    <li>Temps passé sur le quiz</li>
                    <li>Détails par question</li>
                    <li>Statistiques détaillées par catégorie</li>
                    <li>Comparaison avec la moyenne de la classe</li>
                </ul>
            `;
        }, 1000);
    }
    
    // Fonction pour exporter en CSV
    function exportToCSV() {
        <?php if (empty($results)): ?>
            alert('Aucune donnée à exporter.');
            return;
        <?php endif; ?>
        
        // Créer le contenu CSV
        let csvContent = "data:text/csv;charset=utf-8,";
        
        // En-têtes
        csvContent += "Position,Nom,Prénom,Email,Score,Pourcentage,Terminé le\n";
        
        // Données
        <?php foreach ($results as $index => $result): ?>
        csvContent += "<?= $index + 1 ?>,<?= 
            addslashes($result['lastname'] ?? '') ?>,<?= 
            addslashes($result['firstname'] ?? '') ?>,<?= 
            addslashes($result['email'] ?? '') ?>,<?= 
            $result['score_display'] ?? '0/0' ?>,<?= 
            $result['percentage'] ?? 0 ?>%,<?= 
            addslashes($result['completed_formatted'] ?? '') ?>\n";
        <?php endforeach; ?>
        
        // Ligne vide
        csvContent += "\n";
        
        // Statistiques
        csvContent += "Statistiques\n";
        csvContent += "Nombre de participants,<?= $total_submissions ?>\n";
        csvContent += "Score moyen,<?= $average_score ?>%\n";
        csvContent += "Total points possibles,<?= getQuizTotalPoints($quiz_id) ?>\n";
        csvContent += "Nom du quiz,<?= htmlspecialchars($quiz['name']) ?>\n";
        csvContent += "Statut du quiz,<?= $quiz['status'] === 'finished' ? 'Terminé' : ($quiz['status'] === 'launched' ? 'Lancé' : 'En écriture') ?>\n";
        csvContent += "Date de l'export,<?= date('d/m/Y H:i:s') ?>\n";
        
        // Télécharger
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "resultats_quiz_<?= $quiz_id ?>_<?= date('Y-m-d') ?>.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    // Fonction pour imprimer uniquement le contenu utile
    window.onbeforeprint = function() {
        // Cacher les boutons et le header de navigation
        const elementsToHide = document.querySelectorAll('.btn, .back-link, .export-btn, .modal');
        elementsToHide.forEach(el => {
            el.classList.add('d-print-none');
        });
    };
    
    window.onafterprint = function() {
        // Rétablir l'affichage
        const elementsToHide = document.querySelectorAll('.d-print-none');
        elementsToHide.forEach(el => {
            el.classList.remove('d-print-none');
        });
    };
    </script>
</body>
</html>