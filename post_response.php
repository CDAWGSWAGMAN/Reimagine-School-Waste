<?php
session_start([
    'cookie_lifetime' => 0, // expires on browser close
    'cookie_httponly' => true, // JS can't access cookies
    'cookie_secure' => isset($_SERVER['HTTPS']), // only send cookie over HTTPS
    'use_strict_mode' => true, // reject uninitialized session IDs
    'use_only_cookies' => true, // don't allow session ID in URL
]);
session_regenerate_id(true);

require_once 'profanity_filter.php';

// Security Headers
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:");
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
header("Referrer-Policy: no-referrer");

if (!isset($_SESSION['user_id'])) {
    header("Location: signup.html");
    exit;
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("CSRF token validation failed.");
}

$pdo = new PDO("mysql:host=localhost;dbname=LOOL", "root", "root");

$user_id = $_SESSION['user_id'];
$question_id = $_POST['question_id'];
$response_text = trim($_POST['response_text']);

// Profanity Check
if (contains_profanity($response_text)) {
    echo "<script>alert('Your response contains inappropriate language. Please revise and try again.'); window.location.href='community.php';</script>";
    exit;
}

$stmt = $pdo->prepare("INSERT INTO Responses (question_id, user_id, content) VALUES (?, ?, ?)");
$stmt->execute([$question_id, $user_id, $response_text]);

header("Location: community.php");
exit;
