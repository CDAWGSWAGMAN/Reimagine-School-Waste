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

// CSRF Validation
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo "<script>alert('CSRF token validation failed.'); window.location.href='community.php';</script>";
    exit;
}

// Connect securely
$pdo = new PDO("mysql:host=localhost;dbname=LOOL", "root", "root", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$question_id = $_POST['question_id'] ?? '';
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');

// Validate question ID
if (!is_numeric($question_id)) {
    echo "<script>alert('Invalid question ID.'); window.location.href='community.php';</script>";
    exit;
}

// Profanity Check
if (contains_profanity($title) || contains_profanity($content)) {
    $_SESSION['profanity_error'] = true;
    header("Location: community.php");
    exit;
}

// Update with prepared statement
$stmt = $pdo->prepare("UPDATE Questions SET title = ?, content = ? WHERE question_id = ? AND user_id = ?");
$stmt->execute([$title, $content, $question_id, $_SESSION['user_id']]);

header("Location: community.php");
exit;
?>
