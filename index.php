<?php
require __DIR__ . '/includes/bootstrap.php';

if (!current_user()) {
    header('Location: /login.php');
    exit;
}
$pageTitle = 'Tickets';
require __DIR__ . '/includes/header.php';
?>
<h1>Tickets</h1>
<p class="muted">Ticket list coming soon.</p>
<?php require __DIR__ . '/includes/footer.php'; ?>