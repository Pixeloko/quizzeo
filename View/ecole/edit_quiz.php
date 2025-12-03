<?php
session_start();
require_once __DIR__ . "/../../Controller/update.php";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Éditer le Quiz - Quizzeo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Éditer: <?= htmlspecialchars($quiz['name'] ?? ''); ?></h1>
            <a href="dashboard.php" class="btn btn-outline-secondary">← Retour</a>
        </div>

        <!-- Status des quizzs -->
        <div class="card mb-4">
            <div class="card-body">
                <h5>Statut du Quiz: 
                    <span class="badge bg-<?= 
                        $quiz['status'] === 'finished' ? 'success' : 
                        ($quiz['status'] === 'launched' ? 'warning' : 'secondary') 
                    ?>">
                        <?= 
                            $quiz['status'] === 'finished' ? 'Terminé' : 
                            ($quiz['status'] === 'launched' ? 'Lancé' : 'En écriture') 
                        ?>
                    </span>
                </h5>
                
                <?php if ($quiz['status'] !== 'finished'): ?>
                <form method="POST" class="d-inline">
                    <?php if ($quiz['status'] === 'launched'): ?>
                        <button type="submit" name="finish" class="btn btn-success">Terminer le Quiz</button>
                    <?php else: ?>
                        <button type="submit" name="launch" class="btn btn-warning">Lancer le Quiz</button>
                    <?php endif; ?>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Questions du Quiz</h4>
            </div>
            <div class="card-body">
                <?php if (empty($questions)): ?>
                    <p class="text-muted">Aucune question dans ce quiz.</p>
                <?php else: ?>
                    <?php foreach ($questions as $index => $question): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5>Question <?= $index + 1; ?>: <?= htmlspecialchars($question['title']); ?> 
                                <small class="text-muted">(<?= $question['point']; ?> point(s))</small>
                            </h5>
                            
                            <h6>Réponses:</h6>
                            <ul>
                                <?php foreach ($question['answers'] as $answer): ?>
                                <li class="<?= $answer['is_correct'] ? 'text-success fw-bold' : ''; ?>">
                                    <?= htmlspecialchars($answer['answer_text']); ?>
                                    <?php if ($answer['is_correct']): ?>
                                        <span class="badge bg-success">✓ Bonne réponse</span>
                                    <?php endif; ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            
                            <!-- Supprimer questions -->
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="question_id" value="<?= $question['id']; ?>">
                                <button type="submit" name="remove_question" class="btn btn-sm btn-danger">Supprimer</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Questions existantes -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Ajouter des Questions Existantes</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Sélectionner une question:</label>
                        <select name="question_id" class="form-select" required>
                            <option value="">Choisir une question...</option>
                            <?php foreach ($allQuestions as $q): ?>
                                <option value="<?= $q['id']; ?>">
                                    <?= htmlspecialchars($q['title']); ?> 
                                    (<?= count($q['answers'] ?? []); ?> réponses)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="add_question" class="btn btn-primary">Ajouter au Quiz</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>