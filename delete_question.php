<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: signup.html");
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=LOOL", "root", "root");

$question_id = $_POST['question_id'];

// ✅ First delete all related likes
$pdo->prepare("DELETE FROM Likes WHERE question_id = ?")->execute([$question_id]);

// ✅ Then delete all related responses (optional, but good to include)
$pdo->prepare("DELETE FROM Responses WHERE question_id = ?")->execute([$question_id]);

// ✅ Now delete the question
$stmt = $pdo->prepare("DELETE FROM Questions WHERE question_id = ? AND user_id = ?");
$stmt->execute([$question_id, $_SESSION['user_id']]);

header("Location: community.php");
exit;
