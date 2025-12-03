<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultats du Quiz</title>
</head>
<body>
    <h1>Résultats : <?php echo htmlspecialchars($quiz['name']); ?></h1>
    <table>
        <thead>
            <tr>
                <th>Nom de l'Élève</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $result): ?>
                <tr>
                    <td><?php echo htmlspecialchars($result['name']); ?></td>
                    <td><?php echo $result['score']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="/school/dashboard">Retour au Dashboard</a>
</body>
</html>