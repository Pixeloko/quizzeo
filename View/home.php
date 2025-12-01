<?php
    include 'header.php';

if (!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}


try {
    $quizz= getActiveQuizz();
} catch (PDOException $e) {
    echo "Erreur lors de la récupération des articles : " . $e->getMessage();
}

?>

<header><h1> Quizz disponibles </h1></header>
    <section>
       <?php foreach($quizz as $pomme): ?>
        <article>
          <div>
            <time datetime="<?= ($pomme["created_at"]) ?>">
              <?= formatDate($pomme["created_at"]) ?>
            </time>
          </div>
          <h3><?= ($pomme["title"]) ?></h3>
          <a href="<?= 'quizz.php?id=' . $pomme["quizz_id"] ?>">Répondre au quizz</a>
       </article>
        <?php endforeach ?>
    </section>
<?php require_once 'footer.php'; ?>
</body>
</html>