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
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');

// Profanity Check
if (contains_profanity($title) || contains_profanity($content)) {
    $_SESSION['profanity_error'] = true;
    header("Location: community.php");
    exit;
}

$image_data = null;
$image_type = null;

// Secure Image Upload
if (!empty($_FILES['image']['name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
    $image_type = mime_content_type($_FILES['image']['tmp_name']);
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/heic', 'image/heif'];

    if (in_array($image_type, $allowed_types)) {
        $image_data = file_get_contents($_FILES['image']['tmp_name']);
    } else {
        echo "<script>alert('❌ Only image files (JPG, PNG, GIF, WEBP, HEIC) are allowed.'); window.location.href='community.php';</script>";
        exit;
    }
}

// Prepared statement to insert question
$stmt = $pdo->prepare("INSERT INTO Questions (user_id, title, content, image_data, image_type) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$user_id, $title, $content, $image_data, $image_type]);

header("Location: community.php");
exit;
?>
