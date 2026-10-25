<section class="col-12 col-lg-8" aria-labelledby="wartung-liste-titel">
    <div class="card">
        <div class="card-body">
            <div class="list-heading mb-3">
                <h2 id="wartung-liste-titel" class="h5 mb-0">Wartungshistorie</h2>
                <span class="badge text-bg-secondary">
                    <?= count($wartungen) ?>
                    <?= count($wartungen) === 1 ? 'Eintrag' : 'Einträge' ?>
                </span>
            </div>

            <form method="get" class="filter-form row g-2 align-items-end mb-4">
                <div class="col-12 col-md">
                    <label for="filter-fahrzeug" class="form-label">Nach Fahrzeug filtern</label>
                    <select class="form-select" id="filter-fahrzeug" name="fahrzeug_id">
                        <option value="">Alle Fahrzeuge</option>
                        <?php foreach ($fahrzeuge as $fahrzeug): ?>
                            <option
                                value="<?= e($fahrzeug['id']) ?>"
                                <?= $fahrzeugFilter !== ''
                                    && (string) $fahrzeugFilter === (string) $fahrzeug['id']
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= e($fahrzeug['marke'] . ' ' . $fahrzeug['modell'] . ' – ' . $fahrzeug['kennzeichen']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-auto">
                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <?= motologIcon('search', 'icon') ?>
                        Filter anwenden
                    </button>
                </div>
            </form>

            <div class="alert alert-light border d-flex justify-content-between align-items-center">
                <span>Gesamtkosten</span>
                <strong><?= e(number_format((float) $gesamtkosten, 2, ',', '.')) ?> €</strong>
            </div>

            <?php if (empty($wartungen)): ?>
                <p class="text-body-secondary">
                    Noch keine Wartungen für den aktuellen Filter vorhanden.
                </p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th scope="col">Datum</th>
                                <th scope="col">Fahrzeug</th>
                                <th scope="col">Kilometerstand</th>
                                <th scope="col">Beschreibung</th>
                                <th scope="col" class="text-end">Kosten</th>
                                <th scope="col">Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($wartungen as $wartung): ?>
                                <tr>
                                    <td class="text-nowrap">
                                        <?= e(date('d.m.Y', strtotime($wartung['datum']))) ?>
                                    </td>
                                    <td>
                                        <span class="vehicle-make"><?= e($wartung['marke']) ?></span>
                                        <span class="vehicle-model"><?= e($wartung['modell']) ?></span>
                                        <small class="vehicle-plate mt-1">
                                            <?= e($wartung['kennzeichen']) ?>
                                        </small>
                                    </td>
                                    <td class="text-nowrap">
                                        <?= e(number_format((int) $wartung['km_stand'], 0, ',', '.')) ?> km
                                    </td>
                                    <td><?= nl2br(e($wartung['beschreibung'])) ?></td>
                                    <td class="text-end text-nowrap">
                                        <?= e(number_format((float) $wartung['kosten'], 2, ',', '.')) ?> €
                                    </td>
                                    <td class="actions-cell">
                                        <a
                                            href="edit_wartung.php?id=<?= e($wartung['id']) ?><?= e($filterSuffix) ?>"
                                            class="btn btn-sm btn-outline-primary"
                                        ><?= motologIcon('edit', 'icon') ?>Bearbeiten</a>
                                        <a
                                            href="delete_wartung.php?id=<?= e($wartung['id']) ?><?= e($filterSuffix) ?>"
                                            class="btn btn-sm btn-outline-danger"
                                        ><?= motologIcon('trash', 'icon') ?>Löschen</a>
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
