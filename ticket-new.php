<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();

$people = assignable_users($pdo);
$values = ['title' => '', 'description' => '', 'priority' => '', 'assigned_to' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }

    // Keep exactly what the user typed, so we can show it again on errors
    foreach ($values as $field => $unused) {
        $values[$field] = (string) ($_POST[$field] ?? '');
    }

    [$clean, $errors] = validate_ticket_input($_POST, $pdo);

    if (!$errors) {
        $stmt = $pdo->prepare(
            'INSERT INTO tickets (title, description, priority, created_by, assigned_to)
             VALUES (?, ?, ?, ?, ?)'
        );
        // created_by comes from the session, never from the form
        $stmt->execute([
            $clean['title'],
            $clean['description'],
            $clean['priority'],
            (int) current_user()['id'],
            $clean['assigned_to'],
        ]);

        set_flash('Ticket created.');
        redirect('ticket.php?id=' . (int) $pdo->lastInsertId());
    }
}

$pageTitle   = 'New ticket';
$formAction  = 'ticket-new.php';
$submitLabel = 'Create ticket';
$cancelUrl   = 'index.php';
require __DIR__ . '/includes/header.php';
?>
<h1>New ticket</h1>
<?php require __DIR__ . '/includes/ticket-form.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
