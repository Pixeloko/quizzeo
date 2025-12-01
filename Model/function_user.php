<?php     
require_once './config/config.php';


function getUserById(int $id): ?array {
    $conn = getDatabase();

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(["id" => $id]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function getUsers(): ?array {
    $conn = getDatabase();

    $stmt = $conn->prepare("SELECT * FROM users WHERE role != 'admin'");
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
}


function getUserByEmail(string $email): ?array {

    $conn = getDatabase();

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt -> execute(["email" => $email]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function createUser(string $username, string $email, string $password): int {
    
    $errors = [];

    $username = trim($username);
    $email = trim($email);
    $password = trim($password);

    if (!$username) {
        $errors["username"] = "Nom d'utilisateur requis";
    }

    if (!$email) {
        $errors["email"] = "Email requis";
    }

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Veuiller entrer un email valide";
    }

    if ($password && strlen($password) < 6) {
        $errors["password"] = "Le mot de passe doit contenir au moins 6 caractères";
    }

    if (!$password) {
        $errors["password"] = "Mot de passe requis";
    }

    $conn = getDatabase();

    $verif = $conn->prepare("SELECT id FROM users WHERE email = :email OR username = :username");
    $verif->execute([
        "email" => $email, 
        "username" => $username
    ]);

    if ($verif->fetch()) {
        $errors["email"] = "Cet email est déjà utilisé.";
        $errors["username"] = "Ce nom d'utilisateur est déjà utilisé.";
    }

    if (!empty($errors)) {
        throw new InvalidArgumentException(json_encode($errors));
    }

    $stmt = $conn->prepare("INSERT INTO users(username, email, password, created_at) VALUES (:username, :email, :password, NOW())");

    $stmt->execute([
        'username' => $username,
        'email'     => $email,
        'password'  => password_hash($password, PASSWORD_BCRYPT)
    ]);

    return (int) $conn->lastInsertId();
}

function updateUser(int $id, string $username, string $email, string $password): bool {

    $conn = getDatabase();

    $stmt = $conn->prepare("UPDATE users SET username = :username, email = :email, password = :password WHERE id = :id");

    $stmt->execute([
        'username' => $username,
        'email'     => $email,
        'password'  => $password ? password_hash($password, PASSWORD_BCRYPT) : null,
        'id'        => $id
    ]);

    return $stmt->rowCount() > 0;
}

function deleteUser(int $id): bool {

    $conn = getDatabase();

    $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute([
        "id" => $id
    ]);

    return $stmt->rowCount() > 0;
}
?>
