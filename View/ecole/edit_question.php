<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Éditer la Question - Quizzeo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        background-color: #f8f9fa;
    }

    .card {
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .answer-item {
        border: 1px solid #dee2e6;
        border-radius: 5px;
        transition: all 0.3s;
    }

    .answer-item:hover {
        background-color: #f8f9fa;
    }

    .correct-answer {
        border-color: #28a745;
        background-color: #f8fff9;
    }
    </style>
</head>

<body>
    <div class="container py-4">
        <!-- Navigation -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Éditer la question</h1>
                <p class="text-muted mb-0">
                    Quiz: <strong><?= htmlspecialchars($quiz['name']); ?></strong>
                    | Question ID: <?= $question_id; ?>
                </p>
            </div>
            <div>
                <a href="/quizzeo/View/ecole/edit_quiz.php?id=<?= $quiz['id']; ?>" class="btn btn-outline-secondary">
                    ← Retour au quiz
                </a>
                <a href="/quizzeo/?url=ecole" class="btn btn-outline-primary">
                    ← Dashboard
                </a>
            </div>
        </div>

        <!-- Messages -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="row">
            <!-- Colonne gauche : Éditer la question -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Texte de la question</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Question *</label>
                                <textarea class="form-control" name="title" rows="4"
                                    required><?= htmlspecialchars($question['title']); ?></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Points attribués</label>
                                <select name="point" class="form-select w-auto">
                                    <option value="1" <?= $question['point'] == 1 ? 'selected' : ''; ?>>1 point</option>
                                    <option value="2" <?= $question['point'] == 2 ? 'selected' : ''; ?>>2 points
                                    </option>
                                    <option value="3" <?= $question['point'] == 3 ? 'selected' : ''; ?>>3 points
                                    </option>
                                    <option value="4" <?= $question['point'] == 4 ? 'selected' : ''; ?>>4 points
                                    </option>
                                    <option value="5" <?= $question['point'] == 5 ? 'selected' : ''; ?>>5 points
                                    </option>
                                </select>
                            </div>

                            <button type="submit" name="update_question" class="btn btn-primary w-100">
                                <i class="bi bi-save"></i> Enregistrer les modifications
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Ajouter une nouvelle réponse -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Ajouter une nouvelle réponse</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Texte de la réponse</label>
                                <input type="text" name="new_answer" class="form-control"
                                    placeholder="Ex: La réponse est 42">
                            </div>
                            <button type="submit" name="add_answer" class="btn btn-outline-primary w-100">
                                <i class="bi bi-plus-circle"></i> Ajouter cette réponse
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Colonne droite : Éditer les réponses -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Réponses</h5>
                            <span class="badge bg-primary"><?= count($answers); ?> réponses</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($answers)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-chat-quote text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">Aucune réponse</h5>
                            <p class="text-muted">Ajoutez des réponses pour cette question</p>
                        </div>
                        <?php else: ?>
                        <p class="text-muted small mb-3">
                            <i class="bi bi-info-circle"></i>
                            Cochez la réponse correcte et modifiez les textes si nécessaire
                        </p>

                        <form method="POST" action="" id="answersForm">
                            <?php foreach ($answers as $index => $answer): ?>
                            <div class="answer-item p-3 mb-3 <?= $answer['is_correct'] ? 'correct-answer' : ''; ?>"
                                id="answer-<?= $index; ?>">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="correct_answer"
                                            value="<?= $index; ?>" id="correct_<?= $index; ?>"
                                            <?= $answer['is_correct'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label fw-bold" for="correct_<?= $index; ?>">
                                            Réponse <?= $index + 1; ?>
                                            <?php if ($answer['is_correct']): ?>
                                            <span class="badge bg-success ms-2">Correcte</span>
                                            <?php endif; ?>
                                        </label>
                                    </div>

                                    <?php if (count($answers) > 2): ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-answer-btn"
                                        data-answer-id="<?= $answer['id']; ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>

                                <input type="hidden" name="answers[<?= $index; ?>][id]" value="<?= $answer['id']; ?>">

                                <div class="mb-2">
                                    <label class="form-label small text-muted">Texte de la réponse</label>
                                    <input type="text" class="form-control" name="answers[<?= $index; ?>][text]"
                                        value="<?= htmlspecialchars($answer['answer_text']); ?>" required>
                                </div>

                                <div class="small text-muted">
                                    ID: <?= $answer['id']; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>

                            <div class="mt-4">
                                <button type="submit" name="update_answers" class="btn btn-success w-100">
                                    <i class="bi bi-check-circle"></i> Enregistrer toutes les réponses
                                </button>
                                <p class="text-muted small mt-2">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    Pensez à cocher la bonne réponse avant d'enregistrer
                                </p>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion des suppressions de réponses
        document.querySelectorAll('.delete-answer-btn').forEach(button => {
            button.addEventListener('click', function() {
                const answerId = this.getAttribute('data-answer-id');
                
                if (confirm('Êtes-vous sûr de vouloir supprimer cette réponse ?')) {
                    // Créer un formulaire caché pour la suppression
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.style.display = 'none';
                    
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'delete_answer';
                    input.value = answerId;
                    
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });

        // Mettre en évidence la réponse correcte
        document.querySelectorAll('input[name="correct_answer"]').forEach(radio => {
            radio.addEventListener('change', function() {
                // Retirer la classe de toutes les réponses
                document.querySelectorAll('.answer-item').forEach(item => {
                    item.classList.remove('correct-answer');
                });

                // Ajouter la classe à la réponse sélectionnée
                const answerIndex = this.value;
                const answerItem = document.getElementById('answer-' + answerIndex);
                if (answerItem) {
                    answerItem.classList.add('correct-answer');
                }
            });
        });

        // Validation du formulaire principal
        document.getElementById('answersForm')?.addEventListener('submit', function(e) {
            const hasCorrect = document.querySelector('input[name="correct_answer"]:checked');
            if (!hasCorrect) {
                e.preventDefault();
                alert('Veuillez sélectionner une réponse correcte');
                return false;
            }
            
            // Vérifier qu'il reste au moins 2 réponses
            const remainingAnswers = document.querySelectorAll('.answer-item').length;
            if (remainingAnswers < 2) {
                e.preventDefault();
                alert('Une question doit avoir au moins 2 réponses');
                return false;
            }
        });
    });
    </script>
</body>

</html>