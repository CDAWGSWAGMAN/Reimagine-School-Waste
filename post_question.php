<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: signup.html");
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=LOOL", "root", "root");

$user_id = $_SESSION['user_id'];
$title = $_POST['title'];
$content = $_POST['content'];

$image_data = null;
$image_type = null;

if (!empty($_FILES['image']['name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
    $image_type = mime_content_type($_FILES['image']['tmp_name']);
    
    if (strpos($image_type, 'image/') === 0) {
        $image_data = file_get_contents($_FILES['image']['tmp_name']);
    } else {
        die("Only image files are allowed.");
    }
}

$stmt = $pdo->prepare("INSERT INTO Questions (user_id, title, content, image_data, image_type) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$user_id, $title, $content, $image_data, $image_type]);

header("Location: community.php");
exit;
