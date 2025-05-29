<?php
// Secure session start
session_start([
    'cookie_lifetime' => 0,
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'use_strict_mode' => true,
    'use_only_cookies' => true,
]);
session_regenerate_id(true);

// Set security headers
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'");
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
header("Referrer-Policy: no-referrer");

// Ensure user is authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: signup.html");
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// CSRF token validation
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    exit('Invalid CSRF token');
}

// Validate and sanitize input
$question_id = filter_var($_POST['question_id'], FILTER_VALIDATE_INT);
if (!$question_id) {
    http_response_code(400);
    exit('Invalid question ID');
}

// Database connection
$pdo = new PDO("mysql:host=localhost;dbname=LOOL", "root", "root", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

// Confirm the question belongs to the user
$stmt = $pdo->prepare("SELECT user_id FROM Questions WHERE question_id = ?");
$stmt->execute([$question_id]);
$question = $stmt->fetch();

if (!$question || $question['user_id'] !== $_SESSION['user_id']) {
    http_response_code(403);
    exit('Unauthorized');
}

// Delete related likes
$stmt = $pdo->prepare("DELETE FROM Likes WHERE question_id = ?");
$stmt->execute([$question_id]);

// Delete related responses
$stmt = $pdo->prepare("DELETE FROM Responses WHERE question_id = ?");
$stmt->execute([$question_id]);

// Delete the question
$stmt = $pdo->prepare("DELETE FROM Questions WHERE question_id = ?");
$stmt->execute([$question_id]);

// Redirect to community page
header("Location: community.php");
exit;
?>
