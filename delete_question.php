<?php
session_start([
    'cookie_lifetime' => 0,
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'use_strict_mode' => true,
    'use_only_cookies' => true,
]);

if (!isset($_SESSION['user_id'])) {
    header("Location: signup.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    exit('Invalid CSRF token');
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
