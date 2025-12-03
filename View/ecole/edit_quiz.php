<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Éditer le Quiz</title>
</head>
<body>
    <h1>Éditer : <?php echo htmlspecialchars($quiz['name']); ?></h1>
    <h2>Questions dans le Quiz</h2>
    <ul>
        <?php foreach ($questions as $question): ?>
            <?php 
            $correct_answer_text = '';
            foreach ($question['answers'] as $a) {
                if ($a['is_correct']) {
                    $correct_answer_text = $a['answer_text'];
                    break;
                }
            }
            ?>
            <li>
                <?php echo htmlspecialchars($question['title']); ?> (Points: <?php echo $question['point']; ?>, Bonne réponse: <?php echo htmlspecialchars($correct_answer_text); ?>)
                <form action="/quizzeo/index.php?url=ecole/update&id=<?php echo $quiz['id']; ?>" method="POST" style="display:inline;">
                    <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                    <button type="submit" name="remove_question">Supprimer</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <h2>Ajouter une Question</h2>
    <form action="/quizzeo/index.php?url=ecole/update&id=<?php echo $quiz['id']; ?>" method="POST">
        <select name="question_id">
            <?php foreach ($allQuestions as $q): ?>
                <option value="<?php echo $q['id']; ?>"><?php echo htmlspecialchars($q['title']); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="add_question">Ajouter</button>
    </form>

    <form action="/quizzeo/index.php?url=ecole/update&id=<?php echo $quiz['id']; ?>" method="POST">
        <?php if ($quiz['status'] === 'draft'): ?>
            <button type="submit" name="launch">Lancer le Quiz</button>
        <?php elseif ($quiz['status'] === 'launched'): ?>
            <button type="submit" name="finish">Terminer le Quiz</button>
        <?php endif; ?>
    </form>
</body>
</html>
