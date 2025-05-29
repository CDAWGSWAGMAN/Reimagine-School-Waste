<?php
session_start([
    'cookie_lifetime' => 0,
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'use_strict_mode' => true,
    'use_only_cookies' => true,
]);
session_regenerate_id(true);

// Security Headers
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:");
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
header("Referrer-Policy: no-referrer");

require_once 'profanity_filter.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: signup.html");
    exit;
}

// CSRF Token Check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo "<script>alert('CSRF token validation failed.'); window.location.href='community.php';</script>";
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=LOOL", "root", "root", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$user_id = $_SESSION['user_id'];
$question_id = $_POST['question_id'] ?? '';
$response_text = trim($_POST['response_text'] ?? '');

// Profanity Check
if (contains_profanity($response_text)) {
    $_SESSION['profanity_error'] = true;
    header("Location: community.php");
    exit;
}

// Validate numeric question_id
if (!is_numeric($question_id)) {
    echo "<script>alert('Invalid question ID.'); window.location.href='community.php';</script>";
    exit;
}

// Store response securely
$stmt = $pdo->prepare("INSERT INTO Responses (question_id, user_id, content) VALUES (?, ?, ?)");
$stmt->execute([$question_id, $user_id, $response_text]);

header("Location: community.php");
exit;
?>
