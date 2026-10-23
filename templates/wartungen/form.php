<section class="col-12 col-lg-4" aria-labelledby="wartung-formular-titel">
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <h2 id="wartung-formular-titel" class="h5">Wartung erfassen</h2>
        <?php if (empty($fahrzeuge)): ?>
            <div class="alert alert-info mb-0">Bitte zuerst ein <a href="fahrzeuge.php" class="alert-link">Fahrzeug anlegen</a>.</div>
        <?php else: ?>
            <p class="small text-body-secondary">Alle Felder sind Pflichtfelder.</p>
            <form action="add_wartung.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <div class="mb-3"><label for="fahrzeug_id" class="form-label">Fahrzeug</label>
                    <select class="form-select" id="fahrzeug_id" name="fahrzeug_id" required><option value="">Bitte auswählen</option>
                        <?php foreach ($fahrzeuge as $fahrzeug): ?><option value="<?= e($fahrzeug['id']) ?>"<?= (string) ($eingaben['fahrzeug_id'] ?? '') === (string) $fahrzeug['id'] ? ' selected' : '' ?>><?= e($fahrzeug['marke'] . ' ' . $fahrzeug['modell'] . ' – ' . $fahrzeug['kennzeichen']) ?></option><?php endforeach; ?>
                    </select></div>
                <div class="mb-3"><label for="datum" class="form-label">Datum</label>
                    <input type="date" class="form-control" id="datum" name="datum" required value="<?= e($eingaben['datum'] ?? '') ?>"></div>
                <div class="mb-3"><label for="km_stand" class="form-label">Kilometerstand</label>
                    <input type="number" class="form-control" id="km_stand" name="km_stand" min="0" max="2147483647" step="1" required value="<?= e($eingaben['km_stand'] ?? '') ?>"></div>
                <div class="mb-3"><label for="beschreibung" class="form-label">Beschreibung der Arbeiten</label>
                    <textarea class="form-control" id="beschreibung" name="beschreibung" maxlength="5000" rows="5" required><?= e($eingaben['beschreibung'] ?? '') ?></textarea></div>
                <div class="mb-4"><label for="kosten" class="form-label">Kosten in Euro</label>
                    <input type="text" inputmode="decimal" class="form-control" id="kosten" name="kosten" maxlength="12" placeholder="z. B. 89,90" required value="<?= e($eingaben['kosten'] ?? '') ?>"></div>
                <button type="submit" class="btn btn-primary w-100">Wartung speichern</button>
            </form>
        <?php endif; ?>
    </div></div>
</section>
