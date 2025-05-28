<?php
session_start([
    'cookie_lifetime' => 0,
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'use_strict_mode' => true,
    'use_only_cookies' => true,
]);

require_once 'profanity_filter.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

// CSRF Token validation
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

// Check for profanity
if (contains_profanity($username)) {
    echo json_encode(['success' => false, 'error' => 'Username contains inappropriate language.']);
    exit;
}

// Check for existing username or email
$stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
$stmt->execute([$username, $email]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Username or email already exists.']);
    exit;
}

$password_hash = password_hash($password, PASSWORD_BCRYPT);

// Insert into database
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
