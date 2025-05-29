<?php
session_start([
    'cookie_lifetime' => 0,
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'use_strict_mode' => true,
    'use_only_cookies' => true,
]);

require_once 'profanity_filter.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($_SESSION['csrf_token']) || !isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'CSRF token validation failed.']);
        exit;
    }

    $host = 'localhost';
    $db   = 'LOOL';
    $user = 'root';
    $pass = 'root';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
        exit;
    }

    $username = trim($data['username'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $school_level = $data['school_level'] ?? '';
    $state = $data['state'] ?? '';

    if (!$username || !$email || !$password || !$school_level || !$state) {
        echo json_encode(['success' => false, 'error' => 'All fields are required.']);
        exit;
    }

    if (contains_profanity($username)) {
        echo json_encode(['success' => false, 'error' => 'Username contains inappropriate language.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Username or email already exists.']);
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, school_level, state) VALUES (?, ?, ?, ?, ?)");
    $success = $stmt->execute([$username, $email, $password_hash, $school_level, $state]);

    if ($success) {
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['username'] = $username;
        session_regenerate_id(true);
        echo json_encode(['success' => true, 'redirect' => 'community.php']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Signup failed.']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lunch Out of Landfills - Sign Up</title>
    <link rel="stylesheet" href="styles.css" />
    <style>
        @import url('https://fonts.googleapis.com/css?family=Poppins:400,700,900');
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
        <li><a href="Community.html">Community Forms</a></li>
    </ul>
    <div class="auth-buttons">
        <a href="login.html" class="button">Login</a>
    </div>
</nav>

<br>

<div class="signup-container">
    <h2>Create an Account</h2>
    <form id="signupForm">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" maxlength="50" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" maxlength="100" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <label for="school_level">School Level:</label>
        <select id="school_level" name="school_level" required>
            <option value="">Select a level</option>
            <option value="Elementary">Elementary</option>
            <option value="Middle">Middle</option>
            <option value="High">High</option>
            <option value="University">University</option>
        </select>

        <label for="state">State:</label>
        <select id="state" name="state" required>
            <option value="">Select your state</option>
            <?php
                $states = ["AL","AK","AZ","AR","CA","CO","CT","DE","FL","GA","HI","ID","IL","IN","IA","KS","KY","LA","ME",
                           "MD","MA","MI","MN","MS","MO","MT","NE","NV","NH","NJ","NM","NY","NC","ND","OH","OK","OR",
                           "PA","RI","SC","SD","TN","TX","UT","VT","VA","WA","WV","WI","WY"];
                foreach ($states as $st) {
                    echo "<option value=\"$st\">$st</option>";
                }
            ?>
        </select>

        <input type="hidden" id="csrf_token" value="<?php echo $csrf_token; ?>">

        <br>
        <button type="submit">Sign Up</button>
    </form>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {
    const signupForm = document.getElementById('signupForm');
    if (signupForm) {
        signupForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = {
                username: document.getElementById('username').value,
                email: document.getElementById('email').value,
                password: document.getElementById('password').value,
                school_level: document.getElementById('school_level').value,
                state: document.getElementById('state').value,
                csrf_token: document.getElementById('csrf_token').value
            };

            try {
                const res = await fetch('signup.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                const result = await res.json();
                if (result.success) {
                    alert('Account created successfully!');
                    window.location.href = result.redirect || 'login.html';
                } else {
                    alert(result.error || 'Signup failed.');
                }
            } catch (err) {
                console.error('Fetch error:', err);
                alert('Something went wrong. Try again.');
            }
        });
    }
});
</script>

<script src="app.js"></script>
</body>
</html>
