
<?php
$errors = [];
$title = "";
$content = "";
$published = false;

if ($_SERVER["REQUEST_METHOD"]  === "POST") {
    // Je récupère les données du formulaire
    $title = trim($_POST["title"]); 
    $content = trim($_POST["content"]);
    $published = isset($_POST["published"]);
 
  if (empty($title)) {
    $errors["title"] = "Le titre est requis";
  }


 
    if(empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO articles(title, content, published)
                VALUES(:titre, :content, :published)");
            $stmt->execute([
                'titre'=>$title,
                'content'=>$content,
                'published'=>$published,
            ]);
    } catch(PDOException $e) {
        $errors["general"] = "Impossible de créer l'article.";
    }
    }

    if(empty($errors)) {
        $_SESSION["message"] = "Envoi réussi !";
        header('Location : dashboard.php');
        exit;    
    }
}

?>