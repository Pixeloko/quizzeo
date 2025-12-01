<?php
require_once("./includes/header.php");

// Démarre la session si pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ----- Connexion à la base de données -----
$host = "localhost:5555";
$dbname = "quiz_db"; // change avec ta BDD
$user = "root"; // ton utilisateur MySQL
$passwordDB = "0000"; // ton mot de passe MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $passwordDB);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base : " . $e->getMessage());
}

// ----- Initialisation -----
$errors = [];
$email = "";
$password = "";

// ----- Traitement du formulaire -----
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Veuillez entrer un email valide";
    }

    if (empty($password)) {
        $errors["password"] = "Le mot de passe est requis";
    }

    // Si pas d'erreurs, on cherche l'utilisateur
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(["email" => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($password, $user["password"])) {
                $errors["general"] = "❌ Identifiants invalides";
            } else {
                // Connexion réussie
                $_SESSION["user_id"] = $user["id"]; // attention, ici le nom de la colonne est 'id'
                $_SESSION["message"] = "✅ Connexion réussie !";
                header("Location: dashboard.php");
                exit;
            }
        } catch (PDOException $e) {
            $errors["general"] = "Une erreur s'est produite. Veuillez réessayer plus tard";
        }
    }
}
?>

<form method="POST">
    <?php if (isset($errors["general"])): ?>
        <div style="color: red"><?= htmlspecialchars($errors["general"]) ?></div>
    <?php endif ?>

    <h1>Connexion</h1>

    <div>
        <label for="email">Email (requis) :</label>
        <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>" required />
        <?php if (isset($errors["email"])): ?>
            <p style="color: red"><?= htmlspecialchars($errors["email"]) ?></p>
        <?php endif ?>
    </div>

    <div>
        <label for="password">Mot de passe (requis) :</label>
        <input type="password" name="password" id="password" required />
        <?php if (isset($errors["password"])): ?>
            <p style="color: red"><?= htmlspecialchars($errors["password"]) ?></p>
        <?php endif ?>
    </div>

    <button type="submit">Se connecter</button>
</form>

<?php require_once("./includes/footer.php"); ?>
