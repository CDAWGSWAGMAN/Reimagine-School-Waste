<?php
session_start([
    'cookie_lifetime' => 0,
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'use_strict_mode' => true,
    'use_only_cookies' => true,
]);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'error' => 'Email and password are required.']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=LOOL", "root", "root", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $stmt = $pdo->prepare("SELECT user_id, username, password_hash FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        session_regenerate_id(true);
        echo json_encode(['success' => true, 'redirect' => 'community.php']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid email or password.']);
    }
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred.']);
}
?>
