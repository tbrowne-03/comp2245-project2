<?php
$pageTitle = $pageTitle ?? 'Fix-It Desk';
$hideNav   = $hideNav ?? false;
$user      = current_user();
$currentPage = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($pageTitle) ?> | Fix-It Desk</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php if (!$hideNav && $user): ?>
    <header class="site-header">
        <a class="brand" href="index.php">Fix-It Desk</a>
        <nav>
            <a href="index.php"<?= $currentPage === 'index.php' ? ' class="active"' : '' ?>>Tickets</a>
            <a href="ticket-new.php"<?= $currentPage === 'ticket-new.php' ? ' class="active"' : '' ?>>New ticket</a>
            <?php if (is_admin()): ?>
                <a href="users.php"<?= $currentPage === 'users.php' ? ' class="active"' : '' ?>>Users</a>
            <?php endif; ?>
        </nav>
        <div class="user-box">
            <span><?= e($user['name']) ?></span>
            <form method="post" action="logout.php">
                <?= csrf_field() ?>
                <button type="submit">Log out</button>
            </form>
        </div>
    </header>
<?php endif; ?>
<main class="container">