<?php
session_start([
    'cookie_lifetime' => 0,
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'use_strict_mode' => true,
    'use_only_cookies' => true,
]);
session_regenerate_id(true);

// Security headers
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:");
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
header("Referrer-Policy: no-referrer");
header('Content-Type: text/plain');

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Check user login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Not logged in');
}

// CSRF validation
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    exit('CSRF validation failed');
}

// Validate and sanitize question_id
$question_id = filter_input(INPUT_POST, 'question_id', FILTER_VALIDATE_INT);
if (!$question_id) {
    http_response_code(400);
    exit('Invalid question ID');
}

$pdo = new PDO("mysql:host=localhost;dbname=LOOL", "root", "root", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$user_id = $_SESSION['user_id'];

// Check if user already liked
$stmt = $pdo->prepare("SELECT 1 FROM Likes WHERE user_id = ? AND question_id = ?");
$stmt->execute([$user_id, $question_id]);

if (!$stmt->fetch()) {
    $insert = $pdo->prepare("INSERT INTO Likes (user_id, question_id) VALUES (?, ?)");
    $insert->execute([$user_id, $question_id]);
}

// Return updated like count
$count = $pdo->prepare("SELECT COUNT(*) FROM Likes WHERE question_id = ?");
$count->execute([$question_id]);
echo $count->fetchColumn();
