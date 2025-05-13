<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: signup.html");
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=LOOL", "root", "root");

$question_id = $_POST['question_id'];
$title = $_POST['title'];
$content = $_POST['content'];

$stmt = $pdo->prepare("UPDATE Questions SET title = ?, content = ? WHERE question_id = ? AND user_id = ?");
$stmt->execute([$title, $content, $question_id, $_SESSION['user_id']]);

header("Location: community.php");
exit;
