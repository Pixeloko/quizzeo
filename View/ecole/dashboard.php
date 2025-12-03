<?php
session_start();
require_once __DIR__ . "/../includes/header.php";

// l'utilisateur a-t-il le rôle école
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "ecole") {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . "/../../Model/function_quizz.php";
$quizzes = getQuizzByUserId($_SESSION["user_id"]);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard École - Quizzeo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Dashboard École</h1>
            <div>
                <a href="ecole/create" class="btn btn-success">+ Nouveau Quiz</a>
                <a href="../logout.php" class="btn btn-outline-danger">Déconnexion</a>
            </div>
        </div>

        <!-- Susccès ou erreur ? -->
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
                                    require_once __DIR__ . "/../../Model/function_quizz.php";
                                    $submissions = countSubmissions($quiz['id']);
                                    $status = getQuizStatus($quiz['id']); 
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
                                    <span class="badge bg-info"><?= $submissions; ?> réponse(s)</span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($quiz['created_at'])); ?></td>
                                <td>
                                    <?php if ($status !== 'finished'): ?>
                                    <a href="/quizzeo/View/ecole/edit_quiz.php?id=<?= $quiz['id']; ?>"
                                        class="btn btn-sm btn-primary">Éditer</a>

                                    <a href="/quizzeo/Controller/launch_quiz.php?id=<?= $quiz['id']; ?>"
                                        class="btn btn-sm btn-warning"
                                        onclick="return confirm('Lancer ce quiz ? Les étudiants pourront y répondre.')">Lancer</a>
                                    <?php else: ?>
                                    <a href="results.php?id=<?= $quiz['id']; ?>" class="btn btn-sm btn-success">Voir
                                        Résultats</a>
                                    <?php endif; ?>
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

</body>

</html>