<section class="col-12 col-lg-7" aria-labelledby="kunden-bearbeiten-titel">
    <div class="card">
        <div class="card-body">
            <h1 id="kunden-bearbeiten-titel" class="h2 mb-3">Kundendaten bearbeiten</h1>
            <p class="small text-body-secondary">Mit * markierte Felder sind Pflichtfelder.</p>

            <form action="update_kunde.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="id" value="<?= e($kunde['id']) ?>">

                <div class="mb-3">
                    <label for="vorname" class="form-label">Vorname *</label>
                    <input
                        type="text"
                        class="form-control"
                        id="vorname"
                        name="vorname"
                        value="<?= e($eingaben['vorname'] ?? '') ?>"
                        autocomplete="given-name"
                        maxlength="50"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label for="nachname" class="form-label">Nachname *</label>
                    <input
                        type="text"
                        class="form-control"
                        id="nachname"
                        name="nachname"
                        value="<?= e($eingaben['nachname'] ?? '') ?>"
                        autocomplete="family-name"
                        maxlength="50"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label for="telefon" class="form-label">Telefon</label>
                    <input
                        type="tel"
                        class="form-control"
                        id="telefon"
                        name="telefon"
                        value="<?= e($eingaben['telefon'] ?? '') ?>"
                        autocomplete="tel"
                        maxlength="30"
                    >
                </div>

                <div class="mb-4">
                    <label for="email" class="form-label">E-Mail</label>
                    <input
                        type="email"
                        class="form-control"
                        id="email"
                        name="email"
                        value="<?= e($eingaben['email'] ?? '') ?>"
                        autocomplete="email"
                        maxlength="100"
                    >
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">
                        <?= motologIcon('floppy', 'icon') ?>
                        Kundendaten speichern
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary">Abbrechen</a>
                </div>
            </form>
        </div>
    </div>
</section>
