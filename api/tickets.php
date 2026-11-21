<?php
// GET api/tickets.php?filter=all|open|mine  ->  the ticket list as JSON
require __DIR__ . '/../includes/bootstrap.php';

require_login_json();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['ok' => false, 'error' => 'GET required.'], 405);
}

$filter = $_GET['filter'] ?? 'all';
if (!in_array($filter, ['all', 'open', 'mine'], true)) {
    json_response(['ok' => false, 'error' => 'Unknown filter.'], 400);
}

// Two joins on users: one for who created it, one for who it is assigned to.
// The second is a LEFT JOIN so tickets assigned to nobody are still listed.
$sql = "SELECT t.id, t.title, t.priority, t.status, t.created_at,
               CONCAT(c.firstname, ' ', c.lastname) AS created_by_name,
               CONCAT(a.firstname, ' ', a.lastname) AS assigned_to_name
        FROM tickets t
        JOIN users c      ON c.id = t.created_by
        LEFT JOIN users a ON a.id = t.assigned_to";
$params = [];

if ($filter === 'open') {
    $sql .= " WHERE t.status <> 'Closed'";
} elseif ($filter === 'mine') {
    $sql .= " WHERE t.assigned_to = ?";
    $params[] = (int) current_user()['id'];
}

$sql .= " ORDER BY t.created_at DESC, t.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$tickets = [];
foreach ($stmt->fetchAll() as $row) {
    $tickets[] = [
        'id'               => (int) $row['id'],
        'title'            => $row['title'],
        'priority'         => $row['priority'],
        'status'           => $row['status'],
        'created_by_name'  => $row['created_by_name'],
        'assigned_to_name' => $row['assigned_to_name'],   // null = nobody
        'created_at'       => fmt_datetime($row['created_at']),
    ];
}

json_response(['ok' => true, 'filter' => $filter, 'count' => count($tickets), 'tickets' => $tickets]);
