<?php
session_start();
header('Content-Type: text/plain');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    exit('Not logged in');
}

$pdo = new PDO("mysql:host=localhost;dbname=LOOL", "root", "root");

$user_id = $_SESSION['user_id'];
$question_id = isset($_POST['question_id']) ? (int) $_POST['question_id'] : 0;

if ($question_id <= 0) {
    http_response_code(400); // Bad Request
    exit('Invalid question ID');
}

// Check if already liked
$stmt = $pdo->prepare("SELECT 1 FROM Likes WHERE user_id = ? AND question_id = ?");
$stmt->execute([$user_id, $question_id]);

if (!$stmt->fetch()) {
    // Insert new like
    $insert = $pdo->prepare("INSERT INTO Likes (user_id, question_id) VALUES (?, ?)");
    $insert->execute([$user_id, $question_id]);
}

// Return updated like count
$count = $pdo->prepare("SELECT COUNT(*) FROM Likes WHERE question_id = ?");
$count->execute([$question_id]);
echo $count->fetchColumn();
