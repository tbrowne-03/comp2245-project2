<?php
// Loaded at the top of every page and API script.

require __DIR__ . '/../config.php';

// Session setup
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// Database connection (PDO)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $ex) {
    http_response_code(500);
    exit('Database connection failed.');
}

// ---------- Output escaping ----------
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// ---------- Who is logged in ----------
// $_SESSION['user'] = ['id' => 1, 'name' => 'Admin User', 'role' => 'admin']
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_admin(): bool
{
    return (current_user()['role'] ?? '') === 'admin';
}

// For pages: send visitors who aren't logged in to the login page
function require_login(): void
{
    if (!current_user()) {
        header('Location: login.php');
        exit;
    }
}

// For pages: admin only, otherwise 403
function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        exit('403 Forbidden: this page is for admins only.');
    }
}

// ---------- JSON helpers for api/ scripts ----------
function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// For API scripts: answer 401 in JSON instead of redirecting
function require_login_json(): void
{
    if (!current_user()) {
        json_response(['ok' => false, 'error' => 'Not logged in.'], 401);
    }
}

// ---------- CSRF ----------
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Hidden field to put inside every form that changes data
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

// Checks a token from a form field, the X-CSRF-Token header, or a JSON body
function csrf_valid(): bool
{
    $sent = $_POST['csrf_token']
        ?? $_SERVER['HTTP_X_CSRF_TOKEN']
        ?? '';
    if ($sent === '') {
        $body = json_decode(file_get_contents('php://input'), true);
        $sent = is_array($body) ? ($body['csrf_token'] ?? '') : '';
    }
    return $sent !== '' && hash_equals(csrf_token(), (string) $sent);
}

// For pages: POST only, with a valid token
function require_post_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method not allowed.');
    }
    if (!csrf_valid()) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }
}

// For API scripts: POST only, with a valid token, errors in JSON
function require_post_csrf_json(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['ok' => false, 'error' => 'POST required.'], 405);
    }
    if (!csrf_valid()) {
        json_response(['ok' => false, 'error' => 'Invalid CSRF token.'], 403);
    }
}