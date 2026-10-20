<section class="col-12 col-lg-4" aria-labelledby="formular-titel">
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <h2 id="formular-titel" class="h5">Fahrzeug anlegen</h2>
        <?php if (empty($kunden)): ?>
            <div class="alert alert-info mb-0">Bitte zuerst einen <a href="index.php" class="alert-link">Kunden anlegen</a>.</div>
        <?php else: ?>
            <p class="small text-body-secondary">Alle Felder sind Pflichtfelder.</p>
            <form action="add_fahrzeug.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <?php foreach (['marke' => 'Marke', 'modell' => 'Modell', 'kennzeichen' => 'Kennzeichen'] as $feld => $label): ?>
                    <div class="mb-3"><label for="<?= $feld ?>" class="form-label"><?= $label ?></label>
                        <input type="text" class="form-control" id="<?= $feld ?>" name="<?= $feld ?>" maxlength="<?= $feld === 'kennzeichen' ? 20 : 50 ?>" required value="<?= e($eingaben[$feld] ?? '') ?>"></div>
                <?php endforeach; ?>
                <div class="mb-3"><label for="baujahr" class="form-label">Baujahr</label>
                    <input type="number" class="form-control" id="baujahr" name="baujahr" min="1885" max="<?= date('Y') ?>" step="1" required value="<?= e($eingaben['baujahr'] ?? '') ?>"></div>
                <div class="mb-4"><label for="kunde_id" class="form-label">Kunde</label>
                    <select class="form-select" id="kunde_id" name="kunde_id" required><option value="">Bitte auswählen</option>
                        <?php foreach ($kunden as $kunde): ?><option value="<?= e($kunde['id']) ?>"<?= (string) ($eingaben['kunde_id'] ?? '') === (string) $kunde['id'] ? ' selected' : '' ?>><?= e($kunde['nachname'] . ', ' . $kunde['vorname'] . ' (ID ' . $kunde['id'] . ')') ?></option><?php endforeach; ?>
                    </select></div>
                <button type="submit" class="btn btn-primary w-100">Fahrzeug speichern</button>
            </form>
        <?php endif; ?>
    </div></div>
</section>
