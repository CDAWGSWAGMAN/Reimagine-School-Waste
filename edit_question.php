<?php
session_start([
    'cookie_lifetime' => 0,
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'use_strict_mode' => true,
    'use_only_cookies' => true,
]);

// Recommended security headers
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'");
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
header("Referrer-Policy: no-referrer");

if (!isset($_SESSION['user_id'])) {
    header("Location: signup.html");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pdo = new PDO("mysql:host=localhost;dbname=LOOL", "root", "root", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$question_id = filter_input(INPUT_GET, 'question_id', FILTER_VALIDATE_INT);
if (!$question_id) {
    echo "Invalid question ID.";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM Questions WHERE question_id = ? AND user_id = ?");
$stmt->execute([$question_id, $_SESSION['user_id']]);
$question = $stmt->fetch();

if (!$question) {
    echo "Unauthorized or question not found.";
    exit;
}
?>
