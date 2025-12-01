<?php
    include 'header.php';

if (!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}


try {
    $quizz= getQuizz();
} catch (PDOException $e) {
    echo "Erreur lors de la récupération des articles : " . $e->getMessage();
}

?>

<header><h1> Quizz disponibles </h1></header>
    <section>
       <?php foreach($quizz as $pomme): ?>
        <?php if ($quizz[1] ) ; ?>
        <article>
          <div>
            <time datetime="<?= ($pomme["created_at"]) ?>">
              <?= formatDate($pomme["created_at"]) ?>
            </time>
          </div>

          <h3><?= ($pomme["title"]) ?></h3>
          <a href="<?= 'article.php?id=' . $article["article_id"] ?>">Lire la suite</a>
        </article>
        <endif; ?>
        <?php endforeach ?>
    </section>
<?php include 'includes/footer.php'; ?>

</body>
</html>