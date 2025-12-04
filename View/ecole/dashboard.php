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
    .copy-link-btn {
        position: relative;
    }

    .tooltip-custom {
        position: absolute;
        background: #28a745;
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        z-index: 1000;
        white-space: nowrap;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .tooltip-custom.show {
        opacity: 1;
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
                                    <a href="results.php?id=<?= $quiz['id']; ?>" class="text-decoration-none">
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
                                            <i class="bi bi-pencil"></i> Éditer
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