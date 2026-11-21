<?php
// Shared helper functions. Loaded at the end of bootstrap.php, so every page
// and API script can use them.

const PRIORITIES = ['Low', 'Medium', 'High'];
const STATUSES   = ['Open', 'In progress', 'Closed'];

// ---------- Dates ----------
function fmt_datetime(?string $value): string
{
    $ts = $value ? strtotime($value) : false;
    return $ts === false ? '' : date('j M Y, g:i a', $ts);
}

function fmt_date(?string $value): string
{
    $ts = $value ? strtotime($value) : false;
    return $ts === false ? '' : date('j M Y', $ts);
}

// ---------- Redirects and one-time messages ----------
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function set_flash(string $message): void
{
    $_SESSION['flash'] = $message;
}

// Returns the message once, then forgets it
function pull_flash(): ?string
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $message;
}

// Shows a simple "not found" page with the normal header and footer
function not_found_page(string $message = 'That page could not be found.'): void
{
    http_response_code(404);
    $pageTitle = 'Not found';
    require __DIR__ . '/header.php';
    echo '<h1>Not found</h1><p>' . e($message) . '</p>';
    echo '<p><a href="index.php">Back to all tickets</a></p>';
    require __DIR__ . '/footer.php';
    exit;
}

// ---------- Tickets ----------
// One ticket with the names of the person who created it and who it is assigned to.
// LEFT JOIN on the assignee keeps tickets that are assigned to nobody.
function fetch_ticket(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare(
        "SELECT t.id, t.title, t.description, t.priority, t.status,
                t.created_by, t.assigned_to, t.created_at, t.updated_at,
                CONCAT(c.firstname, ' ', c.lastname) AS created_by_name,
                CONCAT(a.firstname, ' ', a.lastname) AS assigned_to_name
         FROM tickets t
         JOIN users c      ON c.id = t.created_by
         LEFT JOIN users a ON a.id = t.assigned_to
         WHERE t.id = ?"
    );
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

// Only an admin, or the person who created the ticket, can delete it
function can_delete_ticket(array $ticket): bool
{
    $me = current_user();
    return $me !== null
        && (is_admin() || (int) $ticket['created_by'] === (int) $me['id']);
}

// Everyone who can be picked in the "Assign to" list
function assignable_users(PDO $pdo): array
{
    return $pdo->query(
        'SELECT id, firstname, lastname FROM users ORDER BY firstname, lastname'
    )->fetchAll();
}

// Checks the fields of the new / edit ticket form.
// Returns [cleanValues, errors]. errors is empty when everything is fine.
function validate_ticket_input(array $input, PDO $pdo): array
{
    $errors = [];

    $title       = trim((string) ($input['title'] ?? ''));
    $description = trim((string) ($input['description'] ?? ''));
    $priority    = (string) ($input['priority'] ?? '');
    $assignedRaw = trim((string) ($input['assigned_to'] ?? ''));

    if ($title === '') {
        $errors['title'] = 'Enter a title.';
    } elseif (mb_strlen($title) > 150) {
        $errors['title'] = 'The title must be 150 characters or fewer.';
    }

    if ($description === '') {
        $errors['description'] = 'Describe the problem.';
    }

    if (!in_array($priority, PRIORITIES, true)) {
        $errors['priority'] = 'Choose a priority.';
    }

    $assignedTo = null;
    if ($assignedRaw !== '') {
        $exists = false;
        if (ctype_digit($assignedRaw)) {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE id = ?');
            $stmt->execute([(int) $assignedRaw]);
            $exists = (bool) $stmt->fetch();
        }
        if ($exists) {
            $assignedTo = (int) $assignedRaw;
        } else {
            $errors['assigned_to'] = 'Choose someone from the list.';
        }
    }

    return [
        ['title' => $title, 'description' => $description,
         'priority' => $priority, 'assigned_to' => $assignedTo],
        $errors,
    ];
}
