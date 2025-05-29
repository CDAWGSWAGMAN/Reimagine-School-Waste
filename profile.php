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
$user_id = $_SESSION['user_id'];

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($email)) {
        if (!empty($password)) {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE Users SET username = ?, email = ?, password_hash = ? WHERE user_id = ?");
            $stmt->execute([$username, $email, $password_hash, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE Users SET username = ?, email = ? WHERE user_id = ?");
            $stmt->execute([$username, $email, $user_id]);
        }
        $_SESSION['username'] = $username;
        header("Location: profile.php?success=1");
        exit;
    } else {
        $error = "Username and email are required.";
    }
}

$stmt = $pdo->prepare("SELECT username, email FROM Users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Profile Settings</title>
  <link rel="stylesheet" href="styles.css" />
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
    }

    .user-greeting {
      display: flex;
      align-items: center;
      gap: 15px;
      margin-left: auto;
      font-weight: bold;
    }

    .logout-btn {
      padding: 6px 12px;
      background-color: #d9534f;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    .profile-form {
      max-width: 500px;
      margin: 40px auto;
      padding: 20px;
      background: #f4f4f4;
      border-radius: 10px;
    }

    input, button {
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

    .alert-success {
      max-width: 500px;
      margin: 20px auto;
      padding: 12px;
      background-color: #dff0d8;
      color: #3c763d;
      border: 1px solid #d6e9c6;
      border-radius: 6px;
      font-weight: bold;
      text-align: center;
    }

    .alert-error {
      max-width: 500px;
      margin: 20px auto;
      padding: 12px;
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
      border-radius: 6px;
      font-weight: bold;
      text-align: center;
    }
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

<?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
  <div class="alert-success">✅ Your profile changes were saved!</div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="alert-error">⚠️ <?php echo $error; ?></div>
<?php endif; ?>
<br>
<br><br>
  <br>
  <br>
  
<div class="profile-form">
  
  <h2>Edit Your Profile</h2>
  <form method="POST">
    <label>Username:</label>
    <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

    <label>Email:</label>
    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

    <label>New Password (leave blank to keep current):</label>
    <input type="password" name="password">

    <button type="submit">Save Changes</button>
  </form>
</div>
<!-- Fetch and display user’s posts -->
<?php
$stmt = $pdo->prepare("SELECT * FROM Questions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$user_questions = $stmt->fetchAll();
?>

<div class="profile-form">
  <h2>Your Posts</h2>

  <?php if (count($user_questions) === 0): ?>
    <p>You haven't posted any questions yet.</p>
  <?php else: ?>
    <?php foreach ($user_questions as $q): ?>
      <div style="background:#fff; padding:15px; margin:10px 0; border-radius:8px;">
        <h3 style="margin-bottom:5px;"><?php echo htmlspecialchars($q['title']); ?></h3>
        <p style="font-size:14px;"><?php echo nl2br(htmlspecialchars($q['content'])); ?></p>
        <small style="color:gray;">Posted on <?php echo date("F j, Y", strtotime($q['created_at'])); ?></small>
        <form action="edit_question.php" method="GET" style="display:inline;">
          <input type="hidden" name="question_id" value="<?php echo $q['question_id']; ?>">
          <button style="margin-right:8px;">Edit</button>
        </form>
        <form action="delete_question.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this post?');" style="display:inline;">
          <input type="hidden" name="question_id" value="<?php echo $q['question_id']; ?>">
          <button style="background:#d9534f; color:white;">Delete</button>
        </form>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<script>
function confirmLogout() {
  if (confirm("Are you sure you want to log out?")) {
    window.location.href = "logout.php";
  }
}

setTimeout(() => {
  const successAlert = document.querySelector('.alert-success');
  if (successAlert) successAlert.style.display = 'none';
}, 4000);
</script>

</body>
</html>