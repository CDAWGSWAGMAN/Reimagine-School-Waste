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

$user_id = $_SESSION['user_id'];
$question_id = $_POST['question_id'];
$response_text = $_POST['response_text'];  // ✅ fixed

$stmt = $pdo->prepare("INSERT INTO Responses (question_id, user_id, content) VALUES (?, ?, ?)");
$stmt->execute([$question_id, $user_id, $response_text]);

header("Location: community.php");
exit;
?>
