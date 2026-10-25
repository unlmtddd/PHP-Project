<form
    method="get"
    action="fahrzeuge.php"
    class="filter-form row g-2 align-items-end mb-4"
    aria-label="Fahrzeugsuche und Kundenfilter"
>
    <div class="col-12 col-lg">
        <label for="fahrzeugsuche" class="form-label">Suche</label>
        <input
            type="search"
            class="form-control"
            id="fahrzeugsuche"
            name="suche"
            maxlength="100"
            value="<?= e($fahrzeugsuche) ?>"
            placeholder="Marke, Modell oder Kennzeichen"
        >
    </div>

    <div class="col-12 col-lg">
        <label for="kundenfilter" class="form-label">Kunde</label>
        <select class="form-select" id="kundenfilter" name="kunde_id">
            <option value="">Alle Kunden</option>
            <option value="ohne"<?= $kundenFilter === 'ohne' ? ' selected' : '' ?>>
                Ohne Kundenzuordnung
            </option>
            <?php foreach ($kunden as $kunde): ?>
                <option
                    value="<?= e($kunde['id']) ?>"
                    <?= $kundenFilter !== ''
                        && $kundenFilter !== 'ohne'
                        && $kundenFilter === (string) $kunde['id']
                        ? 'selected'
                        : '' ?>
                >
                    <?= e($kunde['nachname'] . ', ' . $kunde['vorname'] . ' (ID ' . $kunde['id'] . ')') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-12 col-sm-auto filter-actions">
        <button type="submit" class="btn btn-primary">
            <?= motologIcon('search', 'icon') ?>
            <span>Suchen</span>
        </button>
        <a href="fahrzeuge.php" class="btn btn-outline-secondary">
            <?= motologIcon('refresh', 'icon') ?>
            <span>Zurücksetzen</span>
        </a>
    </div>
</form>

<?php if ($fahrzeugsuche !== ''): ?>
    <p class="filter-result small text-body-secondary">
        Suchbegriff: „<?= e($fahrzeugsuche) ?>“ ·
        <?= count($fahrzeuge) ?> <?= count($fahrzeuge) === 1 ? 'Fahrzeug' : 'Fahrzeuge' ?> gefunden
    </p>
<?php else: ?>
    <p class="filter-result small text-body-secondary">
        <?= count($fahrzeuge) ?> <?= count($fahrzeuge) === 1 ? 'Fahrzeug' : 'Fahrzeuge' ?> gefunden
    </p>
<?php endif; ?>
