<section class="col-12 col-lg-7" aria-labelledby="wartung-bearbeiten-titel">
    <div class="card">
        <div class="card-body">
            <h1 id="wartung-bearbeiten-titel" class="h2 mb-3">Wartung bearbeiten</h1>
            <p class="small text-body-secondary">Alle Felder sind Pflichtfelder.</p>

            <form action="update_wartung.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="id" value="<?= e($wartung['id']) ?>">
                <?php if ($fahrzeugFilter !== ''): ?>
                    <input
                        type="hidden"
                        name="filter_fahrzeug_id"
                        value="<?= e($fahrzeugFilter) ?>"
                    >
                <?php endif; ?>

                <div class="mb-3">
                    <label for="fahrzeug_id" class="form-label">Fahrzeug</label>
                    <select class="form-select" id="fahrzeug_id" name="fahrzeug_id" required>
                        <option value="">Bitte auswählen</option>
                        <?php foreach ($fahrzeuge as $fahrzeug): ?>
                            <option
                                value="<?= e($fahrzeug['id']) ?>"
                                <?= (string) ($eingaben['fahrzeug_id'] ?? '') === (string) $fahrzeug['id']
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= e($fahrzeug['marke'] . ' ' . $fahrzeug['modell'] . ' – ' . $fahrzeug['kennzeichen']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="datum" class="form-label">Datum</label>
                    <input
                        type="date"
                        class="form-control"
                        id="datum"
                        name="datum"
                        required
                        value="<?= e($eingaben['datum'] ?? '') ?>"
                    >
                </div>

                <div class="mb-3">
                    <label for="km_stand" class="form-label">Kilometerstand</label>
                    <input
                        type="number"
                        class="form-control"
                        id="km_stand"
                        name="km_stand"
                        min="0"
                        max="2147483647"
                        step="1"
                        required
                        value="<?= e($eingaben['km_stand'] ?? '') ?>"
                    >
                </div>

                <div class="mb-3">
                    <label for="beschreibung" class="form-label">Beschreibung der Arbeiten</label>
                    <textarea
                        class="form-control"
                        id="beschreibung"
                        name="beschreibung"
                        maxlength="5000"
                        rows="5"
                        required
                    ><?= e($eingaben['beschreibung'] ?? '') ?></textarea>
                </div>

                <div class="mb-4">
                    <label for="kosten" class="form-label">Kosten in Euro</label>
                    <input
                        type="text"
                        inputmode="decimal"
                        class="form-control"
                        id="kosten"
                        name="kosten"
                        maxlength="12"
                        placeholder="z. B. 89,90"
                        required
                        value="<?= e($eingaben['kosten'] ?? '') ?>"
                    >
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">
                        <?= motologIcon('floppy', 'icon') ?>
                        <span>Wartung speichern</span>
                    </button>
                    <a href="wartungen.php<?= $filterSuffix ?>" class="btn btn-outline-secondary">
                        Abbrechen
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>
