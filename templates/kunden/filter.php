<form
    method="get"
    action="index.php"
    class="filter-form row g-2 align-items-end mb-4"
    aria-label="Kundensuche"
>
    <div class="col-12 col-md">
        <label for="kundensuche" class="form-label">Suche</label>
        <input
            type="search"
            class="form-control"
            id="kundensuche"
            name="suche"
            maxlength="100"
            value="<?= e($kundensuche) ?>"
            placeholder="Name, Telefon oder E-Mail"
        >
    </div>
    <div class="col-12 col-sm-auto filter-actions">
        <button type="submit" class="btn btn-primary">
            <?= motologIcon('search', 'icon') ?>
            <span>Suchen</span>
        </button>
        <a href="index.php" class="btn btn-outline-secondary">
            <?= motologIcon('refresh', 'icon') ?>
            <span>Zurücksetzen</span>
        </a>
    </div>
</form>

<?php if ($kundensuche !== ''): ?>
    <p class="filter-result small text-body-secondary">
        Suchbegriff: „<?= e($kundensuche) ?>“ ·
        <?= count($kunden) ?> <?= count($kunden) === 1 ? 'Kunde' : 'Kunden' ?> gefunden
    </p>
<?php else: ?>
    <p class="filter-result small text-body-secondary">
        <?= count($kunden) ?> <?= count($kunden) === 1 ? 'Kunde' : 'Kunden' ?> gefunden
    </p>
<?php endif; ?>
