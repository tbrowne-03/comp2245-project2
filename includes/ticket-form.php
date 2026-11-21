<?php
// The form used by both ticket-new.php and ticket-edit.php.
// Expects: $formAction, $submitLabel, $cancelUrl, $values, $errors, $people
?>
<form method="post" action="<?= e($formAction) ?>" class="card form" novalidate>
    <?= csrf_field() ?>

    <label for="title">Title</label>
    <input type="text" id="title" name="title" value="<?= e($values['title']) ?>"
           class="<?= isset($errors['title']) ? 'invalid' : '' ?>"
           <?= isset($errors['title']) ? 'aria-invalid="true" aria-describedby="title-error"' : '' ?>>
    <?php if (isset($errors['title'])): ?>
        <p class="field-error" id="title-error"><?= e($errors['title']) ?></p>
    <?php endif; ?>

    <label for="description">Description</label>
    <textarea id="description" name="description" rows="6"
              class="<?= isset($errors['description']) ? 'invalid' : '' ?>"
              <?= isset($errors['description']) ? 'aria-invalid="true" aria-describedby="description-error"' : '' ?>><?= e($values['description']) ?></textarea>
    <?php if (isset($errors['description'])): ?>
        <p class="field-error" id="description-error"><?= e($errors['description']) ?></p>
    <?php endif; ?>

    <div class="form-row">
        <div>
            <label for="priority">Priority</label>
            <select id="priority" name="priority"
                    class="<?= isset($errors['priority']) ? 'invalid' : '' ?>"
                    <?= isset($errors['priority']) ? 'aria-invalid="true" aria-describedby="priority-error"' : '' ?>>
                <option value="">Choose…</option>
                <?php foreach (PRIORITIES as $p): ?>
                    <option value="<?= e($p) ?>"<?= $values['priority'] === $p ? ' selected' : '' ?>><?= e($p) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['priority'])): ?>
                <p class="field-error" id="priority-error"><?= e($errors['priority']) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="assigned_to">Assign to</label>
            <select id="assigned_to" name="assigned_to"
                    class="<?= isset($errors['assigned_to']) ? 'invalid' : '' ?>"
                    <?= isset($errors['assigned_to']) ? 'aria-invalid="true" aria-describedby="assigned_to-error"' : '' ?>>
                <option value="">Nobody yet</option>
                <?php foreach ($people as $person): ?>
                    <option value="<?= e($person['id']) ?>"<?= (string) $values['assigned_to'] === (string) $person['id'] ? ' selected' : '' ?>>
                        <?= e($person['firstname'] . ' ' . $person['lastname']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['assigned_to'])): ?>
                <p class="field-error" id="assigned_to-error"><?= e($errors['assigned_to']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= e($submitLabel) ?></button>
        <a href="<?= e($cancelUrl) ?>">Cancel</a>
    </div>
</form>
