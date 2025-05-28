<?php
session_start([
    'cookie_lifetime' => 0, // expires on browser close
    'cookie_httponly' => true, // JS can't access cookies
    'cookie_secure' => isset($_SERVER['HTTPS']), // only send cookie over HTTPS
    'use_strict_mode' => true, // reject uninitialized session IDs
    'use_only_cookies' => true, // don't allow session ID in URL
]);

require_once 'profanity_filter.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: signup.html");
    exit;
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo "<script>alert('CSRF token validation failed.'); window.location.href='community.php';</script>";
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=LOOL", "root", "root");

$question_id = $_POST['question_id'];
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');

if (contains_profanity($title) || contains_profanity($content)) {
    echo "<script>alert('Your update contains inappropriate language. Please revise and try again.'); window.location.href='community.php';</script>";
    exit;
}

$stmt = $pdo->prepare("UPDATE Questions SET title = ?, content = ? WHERE question_id = ? AND user_id = ?");
$stmt->execute([$title, $content, $question_id, $_SESSION['user_id']]);

header("Location: community.php");
exit;
