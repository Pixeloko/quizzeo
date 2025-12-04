<?php
// Controller/submit_quiz.php
session_start();
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: /quizzeo/?url=login");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['quiz_id'])) {
    header("Location: /quizzeo/?url=home");
    exit;
}

$quiz_id = (int)$_POST['quiz_id'];
$user_id = (int)$_SESSION['user_id'];

require_once __DIR__ . '/../Model/function_user.php';

// Vérifier si l'utilisateur a déjà répondu
if (hasUserAnsweredQuiz($user_id, $quiz_id)) {
    $_SESSION['error'] = "Vous avez déjà répondu à ce quiz";
    header("Location: /quizzeo/?url=home");
    exit;
}

require_once __DIR__ . '/../Model/function_quizz.php';
require_once __DIR__ . '/../Model/function_question.php';

// Calculer le score
$score = 0;
$total_points = 0;

foreach ($_POST as $key => $value) {
    if (strpos($key, 'question_') === 0) {
        $question_id = str_replace('question_', '', $key);
        $answer_id = (int)$value;
        
        // Vérifier si la réponse est correcte
        $answer = getAnswerById($answer_id);
        if ($answer && $answer['is_correct']) {
            // Récupérer les points de la question
            $question = getQuestionById($question_id);
            if ($question) {
                $score += (int)$question['point'];
            }
        }
        
        $total_points += (int)$question['point'] ?? 1;
    }
}

// Enregistrer le résultat dans quizz_user
try {
    $pdo = getDatabase();
    
    $sql = "INSERT INTO quizz_user (user_id, quizz_id, score, completed_at) 
            VALUES (:user_id, :quizz_id, :score, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'user_id' => $user_id,
        'quizz_id' => $quiz_id,
        'score' => $score
    ]);
    
    // Enregistrer aussi les réponses individuelles dans user_answers
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'question_') === 0) {
            $question_id = str_replace('question_', '', $key);
            $answer_id = (int)$value;
            
            $answer = getAnswerById($answer_id);
            $is_correct = $answer && $answer['is_correct'] ? 1 : 0;
            
            $sql = "INSERT INTO user_answers (quizz_user_id, question_id, answer_id, is_correct)
                    VALUES (:quizz_user_id, :question_id, :answer_id, :is_correct)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'quizz_user_id' => $pdo->lastInsertId(),
                'question_id' => $question_id,
                'answer_id' => $answer_id,
                'is_correct' => $is_correct
            ]);
        }
    }
    
    $_SESSION['success'] = "Quiz terminé ! Score : $score/$total_points";
    header("Location: /quizzeo/?url=user_dashboard");
    exit;
    
} catch (Exception $e) {
    error_log("Erreur submit_quiz: " . $e->getMessage());
    $_SESSION['error'] = "Erreur lors de l'enregistrement du quiz";
    header("Location: /quizzeo/?url=home");
    exit;
}