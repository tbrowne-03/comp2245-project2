</main>
<script src="app.js"></script>
<?php foreach (($pageScripts ?? []) as $script): ?>
<script src="<?= e($script) ?>"></script>
<?php endforeach; ?>
</body>
</html>
