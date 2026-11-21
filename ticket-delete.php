<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/tickets.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        http_response_code(403);
        exit('Invalid CSRF token.');
}

$me = current_user();
$ticket = get_ticket($pdo, (int)($_POST['id'] ?? nul));

if (!$ticket){
  http_response_code(404);
  exit('Ticket cannot be found.');
}
//Checks whether it is an admin or the person who created the ticket
