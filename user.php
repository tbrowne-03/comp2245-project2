<?php
require __DIR__ . '/includes/bootstrap.php';
require_admin();

$values = ['firstname' => '', 'lastname' => '', 'email' => '', 'role' => 'member'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  if (!csrf_valid())
    http_response_code(403);
    exit('Invalid CSRF token.');

  $values = [
    'firstname' => trim((string)$_POST['firstname'] ?? '')),
    'lastname' => trim((string)$_POST['lastname'] ?? '')),
    'email' => trim((string)$_POST['email'] ?? '')),
    'role' => (string)$_POST['role'] ?? '')
  ];
  $password = (string)$_POST['password'] ?? '');

  if (!filter_var($value['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email address.';
  else{
    $check = $pdo -> prepare('SELECT id FROM users WHERE email = ?');
    $check -> exicute([$values['email']]);
    if ($check -> fetch()) $errors['email'] = 'That email address is already taken.';
  }
  if (!in_array($values['role'], ['admin', 'member'], true)) $errors['role'] = 'Choose a role.';

  if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) $errors['password'] = 'Use at least 8 characters and a number.';

  if (!$errors){
    $hash = $password_hash($password, PASSWORD_DEFAULT);
    $insert = $pdo -> prepare('INSERT INTO users (firstname, lastname, email, password, role) VALUES (?, ?, ?, ?, ?)');
    $insert -> execute([$values['firstname'], $values['lastname'], $values['email'], $hash, $values['role']]);
    header('Location: user.php');
    exit;
  }
}
$user = $pdo -> query('SELECT id, firstname, lastname, email, role, created_at FROM user ORDER BY created_at DESC') -> fetchAll();
$pageTitle = 'Manage Users';
require __DIR__ . '/includes/header.php';
?>
