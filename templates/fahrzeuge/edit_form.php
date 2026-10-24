<section class="col-12 col-lg-7" aria-labelledby="fahrzeug-bearbeiten-titel">
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <h1 id="fahrzeug-bearbeiten-titel" class="h2 mb-3">Fahrzeug bearbeiten</h1>
        <p class="small text-body-secondary">Mit * markierte Felder sind Pflichtfelder.</p>
        <form action="update_fahrzeug.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="id" value="<?= e($fahrzeug['id']) ?>">
            <?php foreach (['marke' => 'Marke', 'modell' => 'Modell', 'kennzeichen' => 'Kennzeichen'] as $feld => $label): ?>
                <div class="mb-3"><label for="<?= $feld ?>" class="form-label"><?= $label ?> *</label>
                    <input type="text" class="form-control" id="<?= $feld ?>" name="<?= $feld ?>" maxlength="<?= $feld === 'kennzeichen' ? 20 : 50 ?>" required value="<?= e($eingaben[$feld] ?? '') ?>"></div>
            <?php endforeach; ?>
            <div class="mb-3"><label for="baujahr" class="form-label">Baujahr *</label>
                <input type="number" class="form-control" id="baujahr" name="baujahr" min="1885" max="<?= date('Y') ?>" step="1" required value="<?= e($eingaben['baujahr'] ?? '') ?>"></div>

            <fieldset class="mb-4">
                <legend class="h6">Kundenzuordnung</legend>
                <p class="small text-body-secondary">Mehrere Kunden sind möglich. Ohne Auswahl bleibt das Fahrzeug ohne Kundenzuordnung.</p>
                <?php if (empty($kunden)): ?>
                    <p class="text-body-secondary mb-0">Noch keine Kunden vorhanden.</p>
                <?php else: ?>
                    <?php foreach ($kunden as $kunde): ?>
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="kunden_ids[]" value="<?= e($kunde['id']) ?>" id="kunde_<?= e($kunde['id']) ?>"<?= in_array((string) $kunde['id'], $ausgewaehlteKundenIds, true) ? ' checked' : '' ?>><label class="form-check-label" for="kunde_<?= e($kunde['id']) ?>"><?= e($kunde['nachname'] . ', ' . $kunde['vorname'] . ' (ID ' . $kunde['id'] . ')') ?></label></div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </fieldset>
            <div class="d-flex flex-wrap gap-2"><button type="submit" class="btn btn-primary">Fahrzeugdaten speichern</button><a href="fahrzeuge.php" class="btn btn-outline-secondary">Abbrechen</a></div>
        </form>
    </div></div>
</section>
