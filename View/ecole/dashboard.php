<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard École</title>
</head>
<body>
    <h1>Dashboard des Quiz</h1>
    <a href="/school/create">Créer un nouveau quiz</a>
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
                    <td><?php echo htmlspecialchars($quiz['name']); ?></td>
                    <td><?php echo htmlspecialchars($quiz['status']); ?></td>
                    <td><?php echo $quiz['response_count']; ?></td>
                    <td>
                        <a href="/school/edit/<?php echo $quiz['id']; ?>">Éditer</a>
                        <?php if ($quiz['status'] === 'finished'): ?>
                            <a href="/school/show/<?php echo $quiz['id']; ?>">Voir Résultats</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>