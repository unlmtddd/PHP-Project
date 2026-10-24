<section class="col-12 col-lg-8" aria-labelledby="kundenliste-titel">
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
            <h2 id="kundenliste-titel" class="h5 mb-0">Kundenliste</h2><span class="badge text-bg-secondary"><?= count($kunden) ?> Kunden</span>
        </div>
        <?php if (empty($kunden)): ?>
            <div class="text-center py-5"><p class="fw-semibold mb-1">Noch keine Kunden vorhanden.</p><p class="text-body-secondary mb-0">Legen Sie Ihren ersten Kunden über das Formular an.</p></div>
        <?php else: ?>
            <div class="table-responsive"><table class="table table-hover align-middle">
                <caption>Gespeicherte Kunden mit Kontaktdaten</caption>
                <thead class="table-light"><tr><th scope="col">Vorname</th><th scope="col">Nachname</th><th scope="col">Telefon</th><th scope="col">E-Mail</th><th scope="col">Aktionen</th></tr></thead>
                <tbody><?php foreach ($kunden as $kunde): ?><tr>
                    <td><?= e($kunde['vorname']) ?></td><td><?= e($kunde['nachname']) ?></td><td><?= e($kunde['telefon'] ?? '—') ?></td><td><?= e($kunde['email'] ?? '—') ?></td>
                    <td class="text-nowrap"><a href="edit_kunde.php?id=<?= e($kunde['id']) ?>" class="btn btn-sm btn-outline-primary">Bearbeiten</a> <a href="delete_kunde.php?id=<?= e($kunde['id']) ?>" class="btn btn-sm btn-outline-danger">Löschen</a></td>
                </tr><?php endforeach; ?></tbody>
            </table></div>
        <?php endif; ?>
    </div></div>
</section>
