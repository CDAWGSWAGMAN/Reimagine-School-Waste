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
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'");
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
header("Referrer-Policy: no-referrer");

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header("Location: signup.html");
    exit;
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// DB connection
$pdo = new PDO("mysql:host=localhost;dbname=LOOL", "root", "root", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

// Validate and get question_id
$question_id = filter_input(INPUT_GET, 'question_id', FILTER_VALIDATE_INT);
if (!$question_id) {
    echo "Invalid question ID.";
    exit;
}

// Verify ownership of question
$stmt = $pdo->prepare("SELECT * FROM Questions WHERE question_id = ? AND user_id = ?");
$stmt->execute([$question_id, $_SESSION['user_id']]);
$question = $stmt->fetch();

if (!$question) {
    echo "Unauthorized or question not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Question</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="post-form">
    <h2>Edit Your Question</h2>
    <form action="update_question.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="question_id" value="<?php echo htmlspecialchars($question_id); ?>">
        <label for="title">Title:</label><br>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($question['title']); ?>" required><br><br>
        <label for="content">Content:</label><br>
        <textarea id="content" name="content" rows="6" required><?php echo htmlspecialchars($question['content']); ?></textarea><br><br>
        <button type="submit">Update</button>
    </form>
</div>
</body>
</html>
