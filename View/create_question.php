<?php 
require_once __DIR__ . "/includes/header.php"; 
require_once __DIR__ . "/../Controller/create_question.php"
?>

<main>
    <h1>Ajouter une question au quiz : <?= htmlspecialchars($quizz['name']) ?></h1>

    <?php if (!empty($errors['general'])): ?>
        <p style="color:red"><?= htmlspecialchars($errors['general']) ?></p>
    <?php endif; ?>

    <form method="POST">
        <p>
            <label for="question_text">Question :</label><br>
            <input type="text" id="question_text" name="question_text" value="<?= htmlspecialchars($question_text ?? '') ?>" style="width:80%">
            <?php if (!empty($errors['question_text'])): ?><br><span style="color:red"><?= htmlspecialchars($errors['question_text']) ?></span><?php endif; ?>
        </p>

        <p>
            <label for="point">Points (ex : 1) :</label>
            <input type="number" id="point" name="point" min="1" value="<?= htmlspecialchars($point ?? 1) ?>">
        </p>

        <fieldset>
            <legend>Réponses (coche la bonne)</legend>

            <?php for ($i = 0; $i < 4; $i++): ?>
                <div style="margin-bottom:8px;">
                    <input type="radio" id="correct_<?= $i ?>" name="correct" value="<?= $i ?>" <?= (isset($correct_index) && $correct_index === $i) ? 'checked' : '' ?>>
                    <label for="answer<?= $i ?>">Réponse <?= $i + 1 ?> :</label>
                    <input type="text" id="answer<?= $i ?>" name="answer<?= $i ?>" value="<?= htmlspecialchars($answers[$i] ?? '') ?>" style="width:60%">
                    <?php if (!empty($errors["answer{$i}"])): ?><br><span style="color:red"><?= htmlspecialchars($errors["answer{$i}"]) ?></span><?php endif; ?>
                </div>
            <?php endfor; ?>

            <?php if (!empty($errors['correct'])): ?><div style="color:red"><?= htmlspecialchars($errors['correct']) ?></div><?php endif; ?>
        </fieldset>

        <button type="submit">Ajouter la question</button>
    </form>
</main>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
