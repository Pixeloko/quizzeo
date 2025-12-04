<?php
if(session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../includes/header.php";

// l'utilisateur a-t-il le rôle école
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "ecole") {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . "/../../Model/function_quizz.php";
$quizzes = getQuizzByUserId($_SESSION["user_id"]);

// URL de base pour les liens de partage
$base_url = 'http://' . $_SERVER['HTTP_HOST'];
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard École - Quizzeo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/style.css">
    
    <style>
    /* Couleurs personnalisées */
    :root {
        --primary-color: #8e79b2;
        --secondary-color: #e76667;
        --accent-color: #fddea7;
        --light-color: #ffffff;
    }
    
    body {
        background-color: #f8f9fa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .container {
        max-width: 1200px;
    }
    
    /* Header */
    .dashboard-header {
        background: linear-gradient(135deg, var(--primary-color), #6a5b95);
        color: white;
        padding: 2rem;
        border-radius: 15px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 12px rgba(142, 121, 178, 0.3);
    }
    
    .dashboard-header h1 {
        color: white;
        font-weight: 700;
    }
    
    /* Boutons */
    .btn-success {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
        padding: 10px 20px;
        font-weight: 600;
        border-radius: 8px;
    }
    
    .btn-success:hover {
        background-color: #7a68a0;
        border-color: #7a68a0;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(142, 121, 178, 0.3);
    }
    
    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }
    
    .btn-primary:hover {
        background-color: #7a68a0;
        border-color: #7a68a0;
    }
    
    .btn-warning {
        background-color: var(--accent-color);
        border-color: var(--accent-color);
        color: #333;
    }
    
    .btn-warning:hover {
        background-color: #fcd28f;
        border-color: #fcd28f;
    }
    
    .btn-danger {
        background-color: var(--secondary-color);
        border-color: var(--secondary-color);
        color: white;
    }
    
    .btn-danger:hover {
        background-color: #d95556;
        border-color: #d95556;
    }
    
    .btn-info {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }
    
    .btn-info:hover {
        background-color: #7a68a0;
        border-color: #7a68a0;
    }
    
    /* Carte */
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 2rem;
        transition: transform 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-5px);
    }
    
    .card-header {
        background-color: var(--primary-color);
        color: white;
        border-bottom: none;
        padding: 1.2rem 1.5rem;
        font-weight: 600;
    }
    
    .card-header h3 {
        color: white;
        margin: 0;
        font-weight: 700;
    }
    
    .card-body {
        background-color: white;
        padding: 1.5rem;
    }
    
    /* Table */
    .table {
        margin-bottom: 0;
    }
    
    .table thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid var(--primary-color);
        color: var(--primary-color);
        font-weight: 700;
        padding: 1rem;
    }
    
    .table tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-color: #eee;
    }
    
    .table tbody tr:hover {
        background-color: rgba(142, 121, 178, 0.05);
    }
    
    /* Badges */
    .badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85em;
    }
    
    .bg-success {
        background-color: var(--primary-color) !important;
    }
    
    .bg-warning {
        background-color: var(--accent-color) !important;
        color: #333;
    }
    
    .bg-secondary {
        background-color: #e9ecef !important;
        color: #6c757d;
    }
    
    .bg-info {
        background-color: var(--primary-color) !important;
    }
    
    /* Alerts */
    .alert-success {
        background-color: rgba(142, 121, 178, 0.15);
        border-color: var(--primary-color);
        color: var(--primary-color);
    }
    
    .alert-danger {
        background-color: rgba(231, 102, 103, 0.15);
        border-color: var(--secondary-color);
        color: var(--secondary-color);
    }
    
    /* Bouton copier */
    .copy-link-btn {
        position: relative;
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }
    
    .copy-link-btn:hover {
        background-color: #7a68a0;
        border-color: #7a68a0;
        color: white;
    }
    
    .tooltip-custom {
        position: absolute;
        background: var(--primary-color);
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        z-index: 1000;
        white-space: nowrap;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.3s;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    
    .tooltip-custom.show {
        opacity: 1;
    }
    
    /* Bouton groupe */
    .btn-group-sm .btn {
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        margin-right: 5px;
        font-size: 0.875rem;
    }
    
    .btn-group-sm .btn i {
        margin-right: 5px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .dashboard-header {
            padding: 1.5rem;
        }
        
        .btn-group {
            flex-wrap: wrap;
        }
        
        .btn-group-sm .btn {
            margin-bottom: 5px;
        }
    }
    </style>
</head>

<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Dashboard École</h1>
            <div>
                <a href="/quizzeo/View/ecole/create_quizz.php" class="btn btn-success">+ Nouveau Quiz</a>
            </div>
        </div>

        <!-- Messages -->
        <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['message']); ?></div>
        <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); ?></div>
        <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <!-- Table pour les quizzs -->
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Mes Quizzes</h3>
            </div>
            <div class="card-body">
                <?php if (empty($quizzes)): ?>
                <p class="text-muted">Aucun quiz créé pour le moment.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nom du Quiz</th>
                                <th>Statut</th>
                                <th>Réponses</th>
                                <th>Créé le</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quizzes as $quiz): 
                            $submissions = countSubmissions($quiz['id']);
                            $status = getQuizStatus($quiz['id']);
                            
                            // Générer le lien de partage
                            $share_link = $base_url . '/quizzeo/?url=quiz&id=' . $quiz['id'];
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($quiz['name']); ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                    $status === 'finished' ? 'success' : 
                                    ($status === 'launched' ? 'warning' : 'secondary') 
                                ?>">
                                        <?= 
                                        $status === 'finished' ? 'Terminé' : 
                                        ($status === 'launched' ? 'Lancé' : 'En écriture') 
                                    ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($submissions > 0): ?>
                                    <a href="/quizzeo/View/ecole/results.php?id=<?= $quiz['id']; ?>" class="text-decoration-none">
                                        <span class="badge bg-success">
                                            <i class="bi bi-people-fill me-1"></i>
                                            <?= $submissions; ?> réponse(s)
                                        </span>
                                    </a>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-people me-1"></i>
                                        <?= $submissions; ?> réponse(s)
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y', strtotime($quiz['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <?php if ($status === 'draft'): ?>
                                        <!-- Mode édition -->
                                        <a href="/quizzeo/View/ecole/edit_quiz.php?id=<?= $quiz['id']; ?>"
                                            class="btn btn-primary">
                                            <i class="bi"></i> Éditer
                                        </a>

                                        <form method="POST"
                                            action="/quizzeo/View/ecole/edit_quiz.php?id=<?= $quiz['id']; ?>"
                                            style="display: inline;">
                                            <input type="hidden" name="launch_quiz" value="1">
                                            <button type="submit" class="btn btn-warning"
                                                onclick="return confirm('Lancer ce quiz ? Les étudiants pourront y répondre.')">
                                                <i class="bi bi-rocket-takeoff"></i> Lancer
                                            </button>
                                        </form>
<?php endif?>



                                        <form method="POST"
                                            action="/quizzeo/View/ecole/edit_quiz.php?id=<?= $quiz['id']; ?>"
                                            style="display: inline;">
                                            <input type="hidden" name="finish_quiz" value="1">
                                            <button type="submit" class="btn btn-danger"
                                                onclick="return confirm('Terminer ce quiz ? Aucun étudiant ne pourra plus y répondre.')">
                                                <i class="bi"></i> Terminer
                                            </button>
                                        </form>

   

                                        <!-- TOUJOURS AFFICHER LE BOUTON RÉSULTATS SI DES RÉPONSES EXISTENT -->
                                        <?php if ($submissions > 0): ?>
                                        <a href="results.php?id=<?= $quiz['id']; ?>" class="btn btn-success">
                                            <i class="bi"></i> Résultats
                                        </a>
                                        <?php else: ?>
                                        <button class="btn btn-outline-secondary" disabled
                                            title="Aucune réponse pour l'instant">
                                            <i class="bi"></i> Résultats
                                        </button>
                                        <?php endif; ?>

                                        <!-- BOUTON DE PARTAGE -->
                                        <button type="button" class="btn btn-info copy-link-btn"
                                            data-share-link="<?= htmlspecialchars($share_link); ?>"
                                            onclick="copyShareLink(this)">
                                            <i class="bi"></i> Partager
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
        function copyShareLink(button) {
            const shareLink = button.getAttribute('data-share-link');

            // Créer un élément input temporaire
            const tempInput = document.createElement('input');
            tempInput.value = shareLink;
            document.body.appendChild(tempInput);

            // Sélectionner et copier
            tempInput.select();
            tempInput.setSelectionRange(0, 99999); // Pour mobile

            try {
                // Essayer l'API Clipboard moderne
                navigator.clipboard.writeText(shareLink).then(() => {
                    showCopySuccess(button);
                }).catch(err => {
                    // Fallback
                    document.execCommand('copy');
                    showCopySuccess(button);
                });
            } catch (err) {
                // Fallback pour anciens navigateurs
                document.execCommand('copy');
                showCopySuccess(button);
            }

            // Nettoyer
            document.body.removeChild(tempInput);
        }

        function showCopySuccess(button) {
            // Sauvegarder le texte original
            const originalHTML = button.innerHTML;
            const originalClass = button.className;

            // Changer l'apparence du bouton
            button.innerHTML = '<i class="bi bi-check"></i> Copié !';
            button.className = originalClass.replace('btn-info', 'btn-success');
            button.disabled = true;

            // Afficher une tooltip
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip-custom';
            tooltip.textContent = 'Lien copié !';
            tooltip.style.top = (button.offsetTop - 30) + 'px';
            tooltip.style.left = (button.offsetLeft + button.offsetWidth / 2 - tooltip.offsetWidth / 2) + 'px';
            document.body.appendChild(tooltip);

            // Afficher la tooltip
            setTimeout(() => tooltip.classList.add('show'), 10);

            // Revenir à l'état normal après 2 secondes
            setTimeout(() => {
                button.innerHTML = originalHTML;
                button.className = originalClass;
                button.disabled = false;

                // Supprimer la tooltip
                tooltip.classList.remove('show');
                setTimeout(() => {
                    if (tooltip.parentNode) {
                        tooltip.parentNode.removeChild(tooltip);
                    }
                }, 300);
            }, 2000);
        }

        // Alternative: fonction pour afficher le lien dans une alerte
        function showShareLink(button) {
            const shareLink = button.getAttribute('data-share-link');
            alert('Lien de partage :\n\n' + shareLink + '\n\n(Copiez ce lien pour le partager)');

            // Option: demander si l'utilisateur veut copier
            if (confirm('Voulez-vous copier ce lien dans le presse-papier ?')) {
                copyShareLink(button);
            }
        }
        </script>
</body>

</html>