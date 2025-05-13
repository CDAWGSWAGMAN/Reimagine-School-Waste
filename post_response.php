<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: signup.html");
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=LOOL", "root", "root");

$user_id = $_SESSION['user_id'];
$question_id = $_POST['question_id'];
$response_text = $_POST['response_text'];  // ✅ fixed

$stmt = $pdo->prepare("INSERT INTO Responses (question_id, user_id, content) VALUES (?, ?, ?)");
$stmt->execute([$question_id, $user_id, $response_text]);

header("Location: community.php");
exit;
?>
