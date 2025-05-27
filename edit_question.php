<?php
session_start([
  'cookie_lifetime' => 0, // expires on browser close
  'cookie_httponly' => true, // JS can't access cookies
  'cookie_secure' => isset($_SERVER['HTTPS']), // only send cookie over HTTPS
  'use_strict_mode' => true, // reject uninitialized session IDs
  'use_only_cookies' => true, // don't allow session ID in URL
]);

if (!isset($_SESSION['user_id'])) {
    header("Location: signup.html");
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=LOOL", "root", "root");

$question_id = $_GET['question_id'];

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
  <title>Edit Post - Lunch Out of Landfills</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
    }
    
    .user-greeting { margin-left: auto; padding-right: 20px; font-weight: bold; }
    
    .edit-form {
      max-width: 600px;
      margin: 40px auto;
      background: #f5f5f5;
      padding: 20px;
      border-radius: 10px;
    }
    input, textarea, button {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border-radius: 6px;
      border: 1px solid #ccc;
    }
    button {
      background-color: #4CAF50;
      color: white;
      border: none;
    }
    .logout-btn {padding: 6px 12px; background-color: #d9534f; color: white; border: none; border-radius: 4px; cursor: pointer; }
  </style>
</head>
<body>

<nav>
  <div class="logo">
    <img src="images/LOOL.png" alt="LOOL Logo" onclick="window.location.href='index.html';">
  </div>
  <ul>
    <li><a href="index.html">Home</a></li>
    <li><a href="tool_kit.html">Resources</a></li>
    <li><a href="How_to.html">Getting Started</a></li>
    <li><a href="data.html">Data</a></li>
    
    <li><a href="community.php">Community Forms</a></li>
  </ul>
  <div class="user-greeting">
    <a href="profile.php" style="text-decoration: none; color: inherit;">
      Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>
    </a>
    <button onclick="confirmLogout()" class="logout-btn">Logout</button>
  </div>
</nav>
<br>
<br>
<br>
<br>
<br>
<div class="edit-form">
  <h2>Edit Your Post</h2>
  <form action="update_question.php" method="POST">
    <input type="hidden" name="question_id" value="<?php echo $question['question_id']; ?>">
    <label>Title:</label>
    <input type="text" name="title" value="<?php echo htmlspecialchars($question['title']); ?>" required>

    <label>Content:</label>
    <textarea name="content" rows="5" required><?php echo htmlspecialchars($question['content']); ?></textarea>

    <button type="submit">Update Post</button>
  </form>
</div>

<script>
function confirmLogout() {
  if (confirm("Are you sure you want to log out?")) {
    window.location.href = "logout.php";
  }
}
</script>

</body>
</html>
