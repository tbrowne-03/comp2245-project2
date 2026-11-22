<?php
require __DIR__ . '/includes/bootstrap.php';
require_admin();

$values = ['firstname' => '', 'lastname' => '', 'email' => '', 'role' => 'member'];
$errors = [];
$form = $values;
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  if (!csrf_valid()) {
    http_response_code(403);
    exit('Invalid CSRF token.');
}

  $values = [
    'firstname' => trim((string)($_POST['firstname'] ?? '')),
    'lastname'  => trim((string)($_POST['lastname'] ?? '')),
    'email'     => trim((string)($_POST['email'] ?? '')),
    'role'      => (string)($_POST['role'] ?? 'member'),
];
  $password = (string)($_POST['password'] ?? '');

  if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email address.';
  else{
    $check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $check->execute([$values['email']]);
    if ($check -> fetch()) $errors['email'] = 'That email address is already taken.';
  }
  if (!in_array($values['role'], ['admin', 'member'], true)) $errors['role'] = 'Choose a role.';

  if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) $errors['password'] = 'Use at least 8 characters and a number.';

  if (!$errors){
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $pdo -> prepare('INSERT INTO users (firstname, lastname, email, password, role) VALUES (?, ?, ?, ?, ?)');
    $insert -> execute([$values['firstname'], $values['lastname'], $values['email'], $hash, $values['role']]);
    header('Location: user.php');
    exit;
  }
}
$users = $pdo->query(
    'SELECT id, firstname, lastname, email, role, created_at
     FROM users
     ORDER BY created_at DESC'
)->fetchAll();
$pageTitle = 'Manage Users';
require __DIR__ . '/includes/header.php';
?>

<main class = "container">
  <div class = "page-heading">
    <div>
      <div class = "eyebrow"> ADMINISTRATION </div>
      <h1> Manage Users </h1>
      <p class = "muted">
        Add team membersand manage access to the Fix-It Desk.
      </p>
    </div>
  </div>
  
  <?php if (!empty($_GET['added'])): ?>
    <p class="flash">User added.</p>
<?php endif; ?>
  <div class = "users-layout">

    <!-- Add User From -->
    <section class = "panel">
      <h2> Add a User </h2>

      <?php if (!empty($errors['form'])): ?>
        <div class = "Alert Error">
          <?= e($errors['form']) ?>
        </div>
       <?php endif; ?>

            <form method="post" class="stack-form">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e(csrf_token()) ?>"
                >

                <label>
                    First Name
                    <input
                        type="text"
                        name="firstname"
                        value="<?= e($form['firstname']) ?>"
                        required
                    >

                    <?php if (isset($errors['firstname'])): ?>
                        <small class="field-error">
                            <?= e($errors['firstname']) ?>
                        </small>
                    <?php endif; ?>
                </label>

                <label>
                    Last Name
                    <input
                        type="text"
                        name="lastname"
                        value="<?= e($form['lastname']) ?>"
                        required
                    >

                    <?php if (isset($errors['lastname'])): ?>
                        <small class="field-error">
                            <?= e($errors['lastname']) ?>
                        </small>
                    <?php endif; ?>
                </label>

                <label>
                    Email Address
                    <input
                        type="email"
                        name="email"
                        value="<?= e($form['email']) ?>"
                        required
                    >

                    <?php if (isset($errors['email'])): ?>
                        <small class="field-error">
                            <?= e($errors['email']) ?>
                        </small>
                    <?php endif; ?>
                </label>

                <label>
                    Password
                    <input
                        type="password"
                        name="password"
                        required
                    >

                    <?php if (isset($errors['password'])): ?>
                        <small class="field-error">
                            <?= e($errors['password']) ?>
                        </small>
                    <?php endif; ?>
                </label>

                <label>
                    Role
                    <select name="role">

                        <option
                            value="member"
                            <?= $form['role'] === 'member' ? 'selected' : '' ?>
                        >
                            Member
                        </option>

                        <option
                            value="admin"
                            <?= $form['role'] === 'admin' ? 'selected' : '' ?>
                        >
                            Admin
                        </option>

                    </select>

                    <?php if (isset($errors['role'])): ?>
                        <small class="field-error">
                            <?= e($errors['role']) ?>
                        </small>
                    <?php endif; ?>
                </label>

                <button class="button" type="submit">
                    Add User
                </button>

            </form>

        </section>

        <!-- Team Members Table -->
        <section class="panel">

            <div class="section-heading">
                <h2>Team Members</h2>

                <span class="count-pill">
                    <?= count($users) ?>
                </span>
            </div>

            <div class="table-wrap">

                <table>

                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Added</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($users as $person): ?>

                            <tr>

                                <td>
                                    <?= e($person['firstname'] . ' ' . $person['lastname']) ?>
                                </td>

                                <td>
                                    <?= e($person['email']) ?>
                                </td>

                                <td>
                                    <span class="role-pill">
                                        <?= e(ucfirst($person['role'])) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= e(date('M j, Y', strtotime($person['created_at']))) ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </div>

</main>
