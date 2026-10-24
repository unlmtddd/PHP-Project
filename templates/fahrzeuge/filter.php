<form
    method="get"
    action="fahrzeuge.php"
    class="row g-2 align-items-end mb-4"
    aria-label="Fahrzeugsuche und Kundenfilter"
>
    <div class="col-12 col-md-5">
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

    <div class="col-12 col-md-5">
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

    <div class="col-6 col-md-1">
        <button type="submit" class="btn btn-primary w-100">Suchen</button>
    </div>
    <div class="col-6 col-md-1">
        <a href="fahrzeuge.php" class="btn btn-outline-secondary w-100">Zurücksetzen</a>
    </div>
</form>

<p class="small text-body-secondary">
    Suchbegriff: „<?= e($fahrzeugsuche === '' ? 'leer' : $fahrzeugsuche) ?>“ ·
    <?= count($fahrzeuge) ?> <?= count($fahrzeuge) === 1 ? 'Fahrzeug' : 'Fahrzeuge' ?> gefunden
</p>
