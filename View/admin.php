<?php 
    require_once("includes/header.php");
    require_once("./Controller/admin.php");
?>
<main>
    <h1>Espace Admin</h1>
    <p>Bienvenue sur l'espace admin. Vous pouvez gérer tout les quizz et utilisateurs à partir de cette page.</p>

    <?php if (isset($_SESSION["message"])): ?>
    <div style="color: green"><?=  htmlspecialchars($_SESSION["message"]) ?></div>
    <?php unset($_SESSION["message"]) ?>
    <?php endif ?>

    <h3>utilisateurs :</h3>
    <?php
    $users = getUsers()
    ?>

    <?php if (count($users) === 0): ?>
    <p>Il n'y a pas d'utilisateur pour le moment.</p>
    <?php else: ?>
    <table border=1>
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
            <?php foreach($users as $user): ?>
            <tr>
                <td><?= $user["firstname"] ?></td>
                <td><?= $user["lastname"] ?></td>
                <td><?= formatDate($user["created_at"]) ?></td>
                <td><?= $user["role"] ?></td>
                <td>
                    <form method="POST" action="updateUser.php">
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                        <input type="hidden" name="action" value="activate">
                        <button type="submit">Activer</button>
                    </form>

                    <form method="POST" action="updateUser.php">
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                        <input type="hidden" name="action" value="deactivate">
                        <button type="submit">Désactiver</button>
                    </form>

                </td>
            </tr>
            <?php endforeach?>
        </tbody>
    </table>
    <?php endif ?>

    <h3>Quizz :</h3>

    <?php if (count($Quizz) === 0): ?>
    <p>Il n'y a pas de quizz pour le moment.</p>
    <?php else: ?>
    <table border=1>
        <thead>
            <tr>
                <th>Titre</th>
                <th>Date de création</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($Quizz as $quizz): ?>
            <tr>
                <td><?= $quizz["title"] ?></td>
                <td><?= formatDate($quizz["created_at"]) ?></td>
                <td>
                    <form class="btn_dash" method="GET" action="updateTask.php">
                        <input type="hidden" name="user" value="<?= htmlspecialchars($user['id']) ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <button type="submit">Activer</button>
                    </form>
                    <form method="POST" action="completeTask.php">
                        <input type="hidden" name="user" value="<?= htmlspecialchars($user['id']) ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <button>Désactiver</button>
                    </form>

                </td>
            </tr>
            <?php endforeach?>
        </tbody>
    </table>
    <?php endif ?>

</main>