<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un Quiz</title>
</head>
<body>
    <h1>Créer un Nouveau Quiz</h1>
    <form action="/school/store" method="POST">
        <label for="name">Nom du Quiz :</label>
        <input type="text" id="name" name="name" required>
        <?php if (isset($errors['name'])): ?>
            <span style="color: red;"><?php echo $errors['name']; ?></span>
        <?php endif; ?>
        <button type="submit">Créer</button>
    </form>
</body>
</html>