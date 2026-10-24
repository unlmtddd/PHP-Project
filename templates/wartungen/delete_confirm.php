<section class="col-12 col-lg-7" aria-labelledby="wartung-loeschen-titel">
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <h1 id="wartung-loeschen-titel" class="h2">Wartung löschen</h1>
        <p>Möchten Sie den Wartungseintrag für <strong><?= e($wartung['marke'] . ' ' . $wartung['modell']) ?></strong> mit dem Kennzeichen <strong><?= e($wartung['kennzeichen']) ?></strong> wirklich löschen?</p>
        <dl class="row"><dt class="col-sm-3">Datum</dt><dd class="col-sm-9"><?= e(date('d.m.Y', strtotime($wartung['datum']))) ?></dd><dt class="col-sm-3">Beschreibung</dt><dd class="col-sm-9"><?= nl2br(e($wartung['beschreibung'])) ?></dd><dt class="col-sm-3">Kosten</dt><dd class="col-sm-9"><?= e(number_format((float) $wartung['kosten'], 2, ',', '.')) ?> €</dd></dl>
        <div class="alert alert-warning">Dieser Wartungseintrag wird dauerhaft gelöscht.<br>Das Fahrzeug und seine Kundenzuordnungen bleiben erhalten.</div>
        <form method="post" class="d-flex flex-wrap gap-2">
            <input type="hidden" name="id" value="<?= e($wartung['id']) ?>">
            <?php if ($fahrzeugFilter !== ''): ?><input type="hidden" name="fahrzeug_id" value="<?= e($fahrzeugFilter) ?>"><?php endif; ?>
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            <button type="submit" class="btn btn-danger">Endgültig löschen</button>
            <a href="wartungen.php<?= $filterSuffix ?>" class="btn btn-outline-secondary">Abbrechen</a>
        </form>
    </div></div>
</section>
