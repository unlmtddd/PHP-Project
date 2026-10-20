<?php if ($meldung !== null): ?>
    <div class="alert <?= ($meldung['typ'] ?? '') === 'success' ? 'alert-success' : 'alert-danger' ?>" role="alert">
        <?= e($meldung['text'] ?? '') ?>
    </div>
<?php endif; ?>
