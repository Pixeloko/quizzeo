<?php
require_once('includes/header.php');
require_once(__DIR__ . "/../Controller/admin.php");

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
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($users as $user): 
                // Déterminer le statut
                $status = isset($user['is_active']) ? $user['is_active'] : 1;
            ?>
            <tr>
                <td><?= htmlspecialchars($user["firstname"]) ?></td>  <!-- firstname au lieu de firstname -->
                <td><?= htmlspecialchars($user["lastname"]) ?></td>   <!-- lastname au lieu de lastname -->
                <td><?= formatDate($user["created_at"]) ?></td>
                <td><?= htmlspecialchars($user["role"]) ?></td>
                <td>
                    <?php if ($status == 1): ?>
                        <span class="badge bg-success">Actif</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Inactif</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($status == 0): ?>
                        <!-- Bouton Activer (visible seulement si inactif) -->
                        <form method="POST" action="admin.php" style="display:inline-block;">
                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                            <input type="hidden" name="action" value="activate">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <button type="submit" class="btn btn-success btn-sm">Activer</button>
                        </form>
                    <?php else: ?>
                        <!-- Bouton Désactiver (visible seulement si actif) -->
                        <form method="POST" action="admin.php" style="display:inline-block;">
                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                            <input type="hidden" name="action" value="deactivate">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <button type="submit" class="btn btn-warning btn-sm">Désactiver</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach ?>
        </tbody>
    </table>
<?php endif; ?>

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
                <?php foreach ($quizzes as $quiz): 
                    $quiz_status = isset($quiz['is_active']) ? $quiz['is_active'] : 1;
                ?>
                <tr>
                    <td><?= htmlspecialchars($quiz["name"]) ?></td>
                    <td><?= formatDate($quiz["created_at"]) ?></td>
                    <td>
                        <?php if ($quiz_status == 1): ?>
                            <span class="badge bg-success">Actif</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Inactif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($quiz_status == 0): ?>
                            <form method="POST" action="admin.php" style="display:inline-block;">
                                <input type="hidden" name="quiz_id" value="<?= htmlspecialchars($quiz['id']) ?>">
                                <input type="hidden" name="action" value="activate">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <button type="submit" class="btn btn-success btn-sm">Activer</button>
                            </form>
                        <?php else: ?>
                            <form method="POST" action="admin.php" style="display:inline-block;">
                                <input type="hidden" name="quiz_id" value="<?= htmlspecialchars($quiz['id']) ?>">
                                <input type="hidden" name="action" value="deactivate">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <button type="submit" class="btn btn-warning btn-sm">Désactiver</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    <?php endif ?>

</main>