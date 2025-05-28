<?php
session_start([
    'cookie_lifetime' => 0, // expires on browser close
    'cookie_httponly' => true, // JS can't access cookies
    'cookie_secure' => isset($_SERVER['HTTPS']), // only send cookie over HTTPS
    'use_strict_mode' => true, // reject uninitialized session IDs
    'use_only_cookies' => true, // don't allow session ID in URL
]);

// Regenerate session ID for security and then destroy session
session_regenerate_id(true);
session_unset();
session_destroy();

// Clear the session cookie from the browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

header("Location: login.html");
exit;
