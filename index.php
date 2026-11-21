<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();

$pageTitle   = 'Tickets';
$pageScripts = ['js/list.js'];
require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
    <h1>Tickets</h1>
    <a class="btn btn-primary" href="ticket-new.php">New ticket</a>
</div>

<div class="filters" role="group" aria-label="Filter tickets">
    <button type="button" class="chip active" data-filter="all" aria-pressed="true">All</button>
    <button type="button" class="chip" data-filter="open" aria-pressed="false">Open</button>
    <button type="button" class="chip" data-filter="mine" aria-pressed="false">My tickets</button>
</div>

<p id="ticket-count" class="muted" aria-live="polite">Loading…</p>
<p id="list-error" class="error" role="alert" hidden></p>

<div class="card table-card">
    <table class="tickets">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Title</th>
                <th scope="col">Priority</th>
                <th scope="col">Status</th>
                <th scope="col">Created by</th>
                <th scope="col">Assigned to</th>
                <th scope="col">Created</th>
            </tr>
        </thead>
        <tbody id="ticket-body"></tbody>
    </table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
