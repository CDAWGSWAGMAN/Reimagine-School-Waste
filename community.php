<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: signup.html");
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=LOOL", "root", "root");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Community - Lunch Out of Landfills</title>
  <link rel="stylesheet" href="styles.css" />
  <script src="app.js"></script>
  <style>
    .user-greeting { margin-left: auto; padding-right: 20px; }
    .post-form, .question, .response {
      max-width: 600px; margin: 20px auto; padding: 20px;
      border-radius: 10px; background: #f5f5f5;
    }
    .question img { max-width: 100%; height: auto; margin-top: 10px; }
    .response-form { margin-top: 10px; }
    .response { background-color: #e9e9e9; margin-top: 10px; padding: 10px; }
    .button-inline { display: inline-block; margin-right: 10px; }
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

<div class="post-form">
  <form method="GET" action="community.php" class="search-form">
    <input type="text" name="search" placeholder="Search questions..." 
           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" 
           style="flex: 1; padding: 12px; border-radius: 6px; border: 1px solid #ccc; font-size: 16px;">
           
    <button type="submit" class="Search-button">Search</button>
    <a href="community.php" class="clear-button">Clear</a>
  </form>
</div>

<div class="post-form">
  <h2>Ask a Question or Share an Idea</h2>
  <form action="post_question.php" method="POST" enctype="multipart/form-data">
    <input type="text" name="title" placeholder="Title" required><br><br>
    <textarea name="content" placeholder="Write your question or idea" rows="4" required></textarea><br><br>
    <input type="file" name="image" accept="image/*"><br><br>
    <button type="submit">Post</button>
  </form>
</div>

<?php
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = '%' . trim($_GET['search']) . '%';
    $stmt = $pdo->prepare("SELECT q.*, u.username FROM Questions q JOIN users u ON q.user_id = u.user_id WHERE q.title LIKE ? OR q.content LIKE ? ORDER BY q.created_at DESC");
    $stmt->execute([$search, $search]);
} else {
    $stmt = $pdo->query("SELECT q.*, u.username FROM Questions q JOIN users u ON q.user_id = u.user_id ORDER BY q.created_at DESC");
}
$questions = $stmt->fetchAll();

foreach ($questions as $question):
?>
  <div class="question">
    <h3><?php echo htmlspecialchars($question['title']); ?></h3>
    <p><strong><?php echo htmlspecialchars($question['username']); ?></strong> on <?php echo date("F j, Y", strtotime($question['created_at'])); ?></p>
    <p><?php echo nl2br(htmlspecialchars($question['content'])); ?></p>
    <?php if (!empty($question['image_data'])): ?>
      <img src="data:<?php echo htmlspecialchars($question['image_type']); ?>;base64,<?php echo base64_encode($question['image_data']); ?>" alt="Question Image">
    <?php endif; ?>

    <?php
      $likeCount = $pdo->prepare("SELECT COUNT(*) FROM Likes WHERE question_id = ?");
      $likeCount->execute([$question['question_id']]);
      $count = $likeCount->fetchColumn();
    ?>
    <form class="like-form" data-question-id="<?php echo $question['question_id']; ?>" style="margin-top: 10px; display: inline-flex; align-items: center; gap: 8px;">
      <button type="submit" style="background: none; border: none; cursor: pointer; padding: 0;">
        <img src="images/thumb-up.png" alt="Like" width="24" height="24">
      </button>
      <span class="like-count"><?php echo $count; ?></span>
    </form>

    <?php if ($question['user_id'] == $_SESSION['user_id']): ?>
      <form action="edit_question.php" method="GET" class="button-inline">
        <input type="hidden" name="question_id" value="<?php echo $question['question_id']; ?>">
        <button type="submit">Edit</button>
      </form>
      <form action="delete_question.php" method="POST" class="button-inline" onsubmit="return confirm('Are you sure you want to delete this question?');">
        <input type="hidden" name="question_id" value="<?php echo $question['question_id']; ?>">
        <button type="submit">Delete</button>
      </form>
    <?php endif; ?>

    <div class="response-form">
      <form action="post_response.php" method="POST">
        <input type="hidden" name="question_id" value="<?php echo $question['question_id']; ?>">
        <textarea name="response_text" placeholder="Write a response..." rows="2" required></textarea><br>
        <button type="submit">Respond</button>
      </form>
    </div>

    <?php
      $stmt2 = $pdo->prepare("SELECT r.*, u.username FROM Responses r JOIN users u ON r.user_id = u.user_id WHERE r.question_id = ? ORDER BY r.created_at ASC");
      $stmt2->execute([$question['question_id']]);
      $responses = $stmt2->fetchAll();
      foreach ($responses as $response):
    ?>
      <div class="response">
        <p><strong><?php echo htmlspecialchars($response['username']); ?>:</strong> <?php echo nl2br(htmlspecialchars($response['content'])); ?></p>
      </div>
    <?php endforeach; ?>
  </div>
<?php endforeach; ?>

<script>
document.querySelectorAll(".like-form").forEach(form => {
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const questionId = form.dataset.questionId;

    const res = await fetch("like_post.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body: "question_id=" + encodeURIComponent(questionId)
    });

    const count = await res.text();
    form.querySelector(".like-count").textContent = count;
  });
});
function confirmLogout() {
  if (confirm("Are you sure you want to log out?")) {
    window.location.href = "logout.php";
  }
}
</script>

</body>
</html>
