<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();

$id     = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$ticket = $id ? fetch_ticket($pdo, $id) : null;
if (!$ticket) {
    not_found_page('That ticket does not exist.');
}

$people = assignable_users($pdo);
$values = [
    'title'       => $ticket['title'],
    'description' => $ticket['description'],
    'priority'    => $ticket['priority'],
    'assigned_to' => (string) ($ticket['assigned_to'] ?? ''),
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }

    foreach ($values as $field => $unused) {
        $values[$field] = (string) ($_POST[$field] ?? '');
    }

    [$clean, $errors] = validate_ticket_input($_POST, $pdo);

    if (!$errors) {
        $stmt = $pdo->prepare(
            'UPDATE tickets SET title = ?, description = ?, priority = ?, assigned_to = ?
             WHERE id = ?'
        );
        $stmt->execute([
            $clean['title'],
            $clean['description'],
            $clean['priority'],
            $clean['assigned_to'],
            $ticket['id'],
        ]);

        set_flash('Ticket updated.');
        redirect('ticket.php?id=' . (int) $ticket['id']);
    }
}

$pageTitle   = 'Edit ticket #' . $ticket['id'];
$formAction  = 'ticket-edit.php?id=' . (int) $ticket['id'];
$submitLabel = 'Save changes';
$cancelUrl   = 'ticket.php?id=' . (int) $ticket['id'];
require __DIR__ . '/includes/header.php';
?>
<h1>Edit ticket #<?= e($ticket['id']) ?></h1>
<?php require __DIR__ . '/includes/ticket-form.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
