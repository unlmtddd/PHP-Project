<form method="get" action="index.php" class="row g-2 align-items-end mb-4" aria-label="Kundensuche">
    <div class="col-12 col-md-8"><label for="kundensuche" class="form-label">Suche</label>
        <input type="search" class="form-control" id="kundensuche" name="suche" maxlength="100" value="<?= e($kundensuche) ?>" placeholder="Name, Telefon oder E-Mail"></div>
    <div class="col-6 col-md-2"><button type="submit" class="btn btn-primary w-100">Suchen</button></div>
    <div class="col-6 col-md-2"><a href="index.php" class="btn btn-outline-secondary w-100">Zurücksetzen</a></div>
</form>
<p class="small text-body-secondary">Suchbegriff: „<?= e($kundensuche === '' ? 'leer' : $kundensuche) ?>“ · <?= count($kunden) ?> <?= count($kunden) === 1 ? 'Kunde' : 'Kunden' ?> gefunden</p>
