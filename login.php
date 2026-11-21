<?php
require __DIR__ . '/includes/bootstrap.php';

//Go to ticket list if already logged in.
if (current_user()) {
    header('Location: /index.php');
    exit;
}

$error = null;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        http_response_code(422);
        $error = 'Your session expired. Please try again.';
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $error = "That email and password don't match an account.";
        } else {
            $stmt = $pdo->prepare(
                'SELECT id, firstname, lastname, email, password, role FROM users WHERE email = ? LIMIT 1'
            );
            $stmt->execute([$email]);
            $row = $stmt->fetch();

            if ($row && password_verify($password, $row['password'])) {
                session_regenerate_id(true);

                $_SESSION['user'] = [
                    'id'        => (int) $row['id'],
                    'firstname' => $row['firstname'],
                    'lastname'  => $row['lastname'],
                    'name'      => $row['firstname'] . ' ' . $row['lastname'], // for header.php
                    'email'     => $row['email'],
                    'role'      => $row['role'],
                ];

                // New session -> new CSRF token.
                unset($_SESSION['csrf_token']);
                csrf_token();

                header('Location: /index.php');
                exit;
            }

            $error = "That email and password don't match an account.";
        }
    }
}

$pageTitle = 'Log in';
$hideNav   = true;
require __DIR__ . '/includes/header.php';
?>
<section class="auth-card">
    <h1>Fix-It Desk</h1>
    <p class="muted">Log in to see and update help desk tickets.</p>

    <?php if ($error !== null): ?>
        <p class="error" role="alert"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post" action="login.php" novalidate>
        <?= csrf_field() ?>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" autocomplete="username" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" autocomplete="current-password" required>

        <button type="submit">Log in</button>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>