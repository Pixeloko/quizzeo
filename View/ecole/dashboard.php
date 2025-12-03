<?php
require_once __DIR__ . '/../includes/header.php';

$quizzes = $_SESSION['quizzes_data'] ?? []; // Récupérer les données
unset($_SESSION['quizzes_data']);

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Dashboard École</title>
</head>

<body>
    <h1>Dashboard des Quiz</h1>
    <a href="./View/ecole/create_quizz.php">Créer un nouveau quiz</a>
    <a href="/quizzeo/index.php?url=ecole/update&id=<?= $quiz['id'] ?>">Modifier un quiz</a>
    <table>
        <thead>
            <tr>
                <th>Nom du Quiz</th>
                <th>Statut</th>
                <th>Nombre de Réponses</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($quizzes as $quiz): ?>
            <tr>
                <td><?= htmlspecialchars($quiz['name']) ?></td>
                <td><?= htmlspecialchars($quiz['status']) ?></td>
                <td><?= $quiz['response_count'] ?></td>
                <td>
                    <a href="/quizzeo/index.php?url=ecole/edit/<?= $quiz['id'] ?>">Éditer</a>
                    <?php if ($quiz['status'] === 'finished'): ?>
                    <a href="/quizzeo/index.php?url=ecole/show/<?= $quiz['id'] ?>">Voir Résultats</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>


        </tbody>
    </table>
</body>

</html>