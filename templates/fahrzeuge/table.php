<section class="col-12 col-lg-8" aria-labelledby="liste-titel">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <?php require __DIR__ . '/filter.php'; ?>

            <div class="d-flex justify-content-between mb-3">
                <h2 id="liste-titel" class="h5 mb-0">Fahrzeugliste</h2>
                <span class="badge text-bg-secondary">
                    <?= count($fahrzeuge) ?> <?= count($fahrzeuge) === 1 ? 'Fahrzeug' : 'Fahrzeuge' ?>
                </span>
            </div>

            <?php if (empty($fahrzeuge) && ($fahrzeugsuche !== '' || $kundenFilter !== '')): ?>
                <p class="text-body-secondary">Keine Fahrzeuge für die aktuellen Filter gefunden.</p>
            <?php elseif (empty($fahrzeuge)): ?>
                <p class="text-body-secondary">Noch keine Fahrzeuge vorhanden.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Fahrzeug</th>
                                <th scope="col">Kennzeichen</th>
                                <th scope="col">Baujahr</th>
                                <th scope="col">Kunden</th>
                                <th scope="col">Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fahrzeuge as $fahrzeug): ?>
                                <tr>
                                    <td><?= e($fahrzeug['marke'] . ' ' . $fahrzeug['modell']) ?></td>
                                    <td><?= e($fahrzeug['kennzeichen']) ?></td>
                                    <td><?= e($fahrzeug['baujahr']) ?></td>
                                    <td>
                                        <?= e(implode(
                                            ', ',
                                            $kundenProFahrzeug[$fahrzeug['id']] ?? ['Keine Zuordnung']
                                        )) ?>
                                    </td>
                                    <td class="text-nowrap">
                                        <a
                                            href="edit_fahrzeug.php?id=<?= e($fahrzeug['id']) ?>"
                                            class="btn btn-sm btn-outline-primary"
                                        >Bearbeiten</a>
                                        <a
                                            href="delete_fahrzeug.php?id=<?= e($fahrzeug['id']) ?>"
                                            class="btn btn-sm btn-outline-danger"
                                        >Löschen</a>
                                        <a
                                            href="wartungen.php?fahrzeug_id=<?= e($fahrzeug['id']) ?>"
                                            class="btn btn-sm btn-outline-secondary"
                                        >Wartungen</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
