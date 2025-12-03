<?php
require_once('includes/header.php');
require_once(__DIR__ . "/../Controller/admin.php");

// Vérification du rôle admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Démarrage session si nécessaire
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Génération CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- Traitement POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && !empty($_POST['csrf_token'])
    && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    
    // Activer / Désactiver utilisateur
    if (!empty($_POST['user_id']) && !empty($_POST['action'])) {
        $userId = (int)$_POST['user_id'];

        if ($_POST['action'] === 'activate') {
            activateUser($userId);
            $_SESSION['message'] = "Utilisateur activé !";
        } elseif ($_POST['action'] === 'deactivate') {
            deactivateUser($userId);
            $_SESSION['message'] = "Utilisateur désactivé !";
        }

        header("Location: admin.php");
        exit;
    }

    // Activer / Désactiver quiz
    if (!empty($_POST['quiz_id']) && !empty($_POST['action'])) {
        $quizId = (int)$_POST['quiz_id'];

        if ($_POST['action'] === 'activate') {
            activateQuiz($quizId);
            $_SESSION['message'] = "Quiz activé !";
        } elseif ($_POST['action'] === 'deactivate') {
            deactivateQuiz($quizId);
            $_SESSION['message'] = "Quiz désactivé !";
        }

        header("Location: admin.php");
        exit;
    }
}

// --- Récupération données ---
$users = fetchUsers();
$quizzes = fetchQuizzes();
?>

<main class="admin-container">
    <h1>Espace Admin</h1>
    <p>Bienvenue sur l'espace admin. Vous pouvez gérer tous les quizz et utilisateurs à partir de cette page.</p>

    <?php if (isset($_SESSION["message"])): ?>
        <div class="admin-message">
            <?= htmlspecialchars($_SESSION["message"]) ?>
        </div>
        <?php unset($_SESSION["message"]); ?>
    <?php endif ?>

    <!-- ======================= UTILISATEURS ======================= -->
    <h3>Utilisateurs :</h3>

    <?php if (count($users) === 0): ?>
        <p>Il n'y a pas d'utilisateur pour le moment.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Prénom</th>
                    <th>Nom</th>
                    <th>Date de création</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user["firstname"]) ?></td>
                    <td><?= htmlspecialchars($user["lastname"]) ?></td>
                    <td><?= formatDate($user["created_at"]) ?></td>
                    <td><?= htmlspecialchars($user["role"]) ?></td>
                    <td>
                        <form method="POST" action="activate_account.php?user_id=<?= htmlspecialchars($user['id']) ?>">
                            <button>Activer</button>
                        </form>
 
                        <form method="POST" action="desactivate_account.php?user_id=<?= htmlspecialchars($task['id']) ?>">
                            <button>Désactiver</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    <?php endif ?>

    <!-- ======================= QUIZZ ======================= -->
    <h3>Quizz :</h3>

    <?php if (count($quizzes) === 0): ?>
        <p>Il n'y a pas de quizz pour le moment.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Date de création</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($quizzes as $quiz): ?>
                <tr>
                    <td><?= htmlspecialchars($quiz["title"]) ?></td>
                    <td><?= formatDate($quiz["created_at"]) ?></td>
                    <td><?= htmlspecialchars($quiz["status"]) ?></td>
                    <td>
                        <form method="POST" action="admin.php" style="display:inline-block;">
                            <input type="hidden" name="quiz_id" value="<?= htmlspecialchars($quiz['id']) ?>">
                            <input type="hidden" name="action" value="activate">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <button type="submit">Activer</button>
                        </form>

                        <form method="POST" action="admin.php" style="display:inline-block;">
                            <input type="hidden" name="quiz_id" value="<?= htmlspecialchars($quiz['id']) ?>">
                            <input type="hidden" name="action" value="deactivate">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <button type="submit">Désactiver</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    <?php endif ?>

</main>
