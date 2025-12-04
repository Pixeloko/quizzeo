<?php


// Démarrage de session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "user") {
    $_SESSION['error'] = "Veuillez vous connecter pour soumettre un quiz";
    header("Location: /quizzeo/?url=login");
    exit;
}

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée";
    header("Location: /quizzeo/?url=user");
    exit;
}

// Récupérer l'ID du quiz
$quiz_id = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : 0;
if ($quiz_id <= 0) {
    $_SESSION['error'] = "Quiz invalide";
    header("Location: /quizzeo/?url=user");
    exit;
}

echo "<p>Quiz ID: $quiz_id</p>";

// Charger les modèles
require_once __DIR__ . '/../Model/function_quizz.php';
require_once __DIR__ . '/../Model/function_question.php';
require_once __DIR__ . '/../Model/function_user.php';

// Vérifier que le quiz existe et est actif
$quiz = getQuizzById($quiz_id);
if (!$quiz) {
    $_SESSION['error'] = "Quiz non trouvé";
    header("Location: /quizzeo/?url=user");
    exit;
}

if ($quiz['status'] !== 'launched') {
    $_SESSION['error'] = "Ce quiz n'est pas encore lancé";
    header("Location: /quizzeo/?url=user");
    exit;
}

if (isset($quiz['is_active']) && $quiz['is_active'] == 0) {
    $_SESSION['error'] = "Ce quiz est désactivé";
    header("Location: /quizzeo/?url=user");
    exit;
}

// Récupérer l'utilisateur
$user_id = $_SESSION['user_id'];

echo "<p>User ID: $user_id</p>";

// Fonction de connexion - vérifiez le nom exact
if (!function_exists('getDatabase') && function_exists('getDatabase')) {
    function getDatabase() {
        return getDatabase();
    }
}

// Obtenir la connexion PDO
try {
    $pdo = getDatabase();
    echo "<p>Connexion BDD réussie</p>";
} catch (Exception $e) {
    $_SESSION['error'] = "Erreur de connexion à la base de données";
    header("Location: /quizzeo/?url=quiz&id=" . $quiz_id);
    exit;
}

// Vérifier si l'utilisateur a déjà répondu à ce quiz
$sql_check = "SELECT id FROM quizz_user WHERE quizz_id = :quiz_id AND user_id = :user_id";
$stmt_check = $pdo->prepare($sql_check);
$stmt_check->execute(['quiz_id' => $quiz_id, 'user_id' => $user_id]);
$existing_attempt = $stmt_check->fetch();

if ($existing_attempt) {
    $_SESSION['error'] = "Vous avez déjà répondu à ce quiz";
    header("Location: /quizzeo/?url=user&action=quiz_results&id=" . $quiz_id);
    exit;
}

// Récupérer toutes les questions du quiz
$questions = getQuestionsByQuizzId($quiz_id);

echo "<p>Nombre de questions: " . count($questions) . "</p>";

if (empty($questions)) {
    $_SESSION['error'] = "Ce quiz n'a pas de questions";
    header("Location: /quizzeo/?url=user");
    exit;
}

// Traiter les réponses
$total_points = 0;
$earned_points = 0;
$total_questions = 0;
$answers_data = [];

foreach ($questions as $question) {
    $question_id = $question['id'];
    $answer_key = "question_" . $question_id;
    
    echo "<p>Traitement question $question_id - Clé POST: $answer_key</p>";
    
    // Récupérer la réponse de l'utilisateur
    $selected_answer_id = isset($_POST[$answer_key]) ? (int)$_POST[$answer_key] : 0;
    
    echo "<p>Réponse sélectionnée: $selected_answer_id</p>";
    
    // Récupérer toutes les réponses pour cette question
    $answers = getAnswersByQuestion($question_id);
    
    if (empty($answers)) {
        echo "<p>Aucune réponse pour la question $question_id</p>";
        continue;
    }
    
    // Trouver la bonne réponse et vérifier si l'utilisateur a bien répondu
    $is_correct = false;
    $selected_answer_text = null;
    $correct_answer_text = null;
    
    foreach ($answers as $answer) {
        if ($answer['id'] == $selected_answer_id) {
            $selected_answer_text = $answer['answer_text'];
        }
        if ($answer['is_correct'] == 1) {
            $correct_answer_text = $answer['answer_text'];
            if ($answer['id'] == $selected_answer_id) {
                $is_correct = true;
                $earned_points += $question['point'];
            }
        }
    }
    
    $total_points += $question['point'];
    $total_questions++;
    
    // Stocker les données de la réponse
    $answers_data[] = [
        'question_id' => $question_id,
        'question_text' => $question['title'],
        'selected_answer_id' => $selected_answer_id,
        'selected_answer_text' => $selected_answer_text,
        'correct_answer_text' => $correct_answer_text,
        'is_correct' => $is_correct,
        'question_points' => $question['point']
    ];
}

echo "<p>Total points: $total_points, Points gagnés: $earned_points</p>";

// Calculer le score
$score_percentage = $total_points > 0 ? round(($earned_points / $total_points) * 100, 1) : 0;

echo "<p>Enregistrement dans la base de données...</p>";

// Enregistrer l'essai du quiz
try {
    $pdo->beginTransaction();
    
    // 1. Enregistrer l'essai dans quizz_user
    $sql_attempt = "INSERT INTO quizz_user (quizz_id, user_id, score, completed_at) 
                    VALUES (:quiz_id, :user_id, :score, NOW())";
    $stmt_attempt = $pdo->prepare($sql_attempt);
    $stmt_attempt->execute([
        'quiz_id' => $quiz_id,
        'user_id' => $user_id,
        'score' => $earned_points
    ]);
    
    $quizz_user_id = $pdo->lastInsertId();
    echo "<p>Quiz_user ID créé: $quizz_user_id</p>";
    
    // 2. Enregistrer chaque réponse dans user_answers
    foreach ($answers_data as $answer) {
        $sql_answer = "INSERT INTO user_answers (quizz_user_id, question_id, answer_id, is_correct) 
                       VALUES (:quizz_user_id, :question_id, :answer_id, :is_correct)";
        $stmt_answer = $pdo->prepare($sql_answer);
        $stmt_answer->execute([
            'quizz_user_id' => $quizz_user_id,
            'question_id' => $answer['question_id'],
            'answer_id' => $answer['selected_answer_id'],
            'is_correct' => $answer['is_correct'] ? 1 : 0
        ]);
    }
    
    $pdo->commit();
    echo "<p>Transaction réussie!</p>";
    
    // Stocker les résultats en session pour la page de résultats
    $_SESSION['quiz_results'] = [
        'quiz_id' => $quiz_id,
        'quiz_name' => $quiz['name'],
        'total_questions' => $total_questions,
        'total_points' => $total_points,
        'earned_points' => $earned_points,
        'score_percentage' => $score_percentage,
        'answers' => $answers_data,
        'completed_at' => date('Y-m-d H:i:s')
    ];
    
    $_SESSION['success'] = "Quiz terminé ! Votre score est de $earned_points/$total_points points ($score_percentage%)";
    
    echo "<p>Redirection vers les résultats...</p>";
    // Rediriger vers la page de résultats
    header("Location: /quizzeo/View/user/dashboard.php");
    exit;
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "<p style='color: red;'>Erreur: " . $e->getMessage() . "</p>";
    echo "<pre>Trace: " . $e->getTraceAsString() . "</pre>";
    $_SESSION['error'] = "Erreur lors de l'enregistrement des réponses: " . $e->getMessage();
    header("Location: /quizzeo/?url=quiz&id=" . $quiz_id);
    exit;
}