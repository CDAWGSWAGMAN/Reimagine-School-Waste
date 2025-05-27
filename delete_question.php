<?php
session_start([
    'cookie_lifetime' => 0, // expires on browser close
    'cookie_httponly' => true, // JS can't access cookies
    'cookie_secure' => isset($_SERVER['HTTPS']), // only send cookie over HTTPS
    'use_strict_mode' => true, // reject uninitialized session IDs
    'use_only_cookies' => true, // don't allow session ID in URL
]);

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
