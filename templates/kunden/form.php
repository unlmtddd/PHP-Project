<section class="col-12 col-lg-4" aria-labelledby="kundenformular-titel">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h2 id="kundenformular-titel" class="h5 mb-2">Neuen Kunden anlegen</h2>
            <p class="small text-body-secondary">Mit * markierte Felder sind Pflichtfelder.</p>

            <form action="add_kunde.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

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

                <button type="submit" class="btn btn-primary w-100">Kunden speichern</button>
            </form>
        </div>
    </div>
</section>
