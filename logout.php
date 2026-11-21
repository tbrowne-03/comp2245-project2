<?php
require __DIR__ . '/includes/bootstrap.php';

require_login();
require_post_csrf();   // 405 if not POST, 403 if CSRF fails

// Empty the session, delete the cookie, destroy the session.
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        [
            'expires'  => time() - 42000,
            'path'     => $p['path'],
            'domain'   => $p['domain'],
            'secure'   => $p['secure'],
            'httponly' => $p['httponly'],
            'samesite' => $p['samesite'] ?? 'Lax',
        ]
    );
}

session_destroy();

header('Location: /login.php');
exit;