<section class="col-12 col-lg-7" aria-labelledby="fahrzeug-loeschen-titel">
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <h1 id="fahrzeug-loeschen-titel" class="h2">Fahrzeug löschen</h1>
        <p>Möchten Sie <strong><?= e($fahrzeug['marke'] . ' ' . $fahrzeug['modell']) ?></strong> mit dem Kennzeichen <strong><?= e($fahrzeug['kennzeichen']) ?></strong> wirklich löschen?</p>
        <p>Für dieses Fahrzeug sind <?= e($wartungsAnzahl) ?> <?= $wartungsAnzahl === 1 ? 'Wartungseintrag' : 'Wartungseinträge' ?> gespeichert.</p>
        <div class="alert alert-warning">Dieses Fahrzeug, seine Kundenzuordnungen und alle zugehörigen Wartungseinträge werden dauerhaft gelöscht. Kunden bleiben erhalten.</div>
        <form method="post" class="d-flex flex-wrap gap-2">
            <input type="hidden" name="id" value="<?= e($fahrzeug['id']) ?>">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            <button type="submit" class="btn btn-danger">Endgültig löschen</button>
            <a href="fahrzeuge.php" class="btn btn-outline-secondary">Abbrechen</a>
        </form>
    </div></div>
</section>
