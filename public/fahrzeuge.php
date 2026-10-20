<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Token für geschützte Formularanfragen erstellen.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Einmalige Meldung und bisherige Eingaben übernehmen.
$meldung = $_SESSION['fahrzeug_meldung'] ?? null;
$eingaben = $_SESSION['fahrzeug_eingaben'] ?? [];

unset(
    $_SESSION['fahrzeug_meldung'],
    $_SESSION['fahrzeug_eingaben']
);

// Kunden für das Auswahlfeld laden.
$kunden = $pdo->query(
    'SELECT id, vorname, nachname
     FROM kunden
     ORDER BY nachname, vorname'
)->fetchAll();

// Fahrzeuge unabhängig von ihren Zuordnungen laden.
$fahrzeuge = $pdo->query(
    'SELECT id, marke, modell, kennzeichen, baujahr
     FROM fahrzeuge
     ORDER BY marke, modell, id'
)->fetchAll();

// JOIN verbindet die Zwischentabelle mit den Kundendaten.
$zuordnungen = $pdo->query(
    'SELECT kf.fahrzeug_id, k.vorname, k.nachname
     FROM kunde_fahrzeug AS kf
     INNER JOIN kunden AS k ON k.id = kf.kunde_id
     ORDER BY k.nachname, k.vorname'
)->fetchAll();

// Kundennamen nach Fahrzeug-ID gruppieren.
$kundenProFahrzeug = [];

foreach ($zuordnungen as $zuordnung) {
    $kundenProFahrzeug[$zuordnung['fahrzeug_id']][] =
        $zuordnung['vorname'] . ' ' . $zuordnung['nachname'];
}

// Hilfsfunktion für die sichere Ausgabe in HTML.
function e($wert)
{
    return htmlspecialchars((string) $wert, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MotoLog – Fahrzeuge</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-body-tertiary">

<header class="navbar navbar-dark bg-dark">
    <div class="container py-2">
        <a class="navbar-brand fw-bold" href="index.php">MotoLog</a>

        <nav class="d-flex gap-3" aria-label="Hauptnavigation">
            <a class="text-white-50 text-decoration-none"
               href="index.php">Kunden</a>
            <a class="text-white text-decoration-none"
               href="fahrzeuge.php" aria-current="page">Fahrzeuge</a>
        </nav>
    </div>
</header>

<main class="container py-4">
    <h1 class="h2">Fahrzeugübersicht</h1>
    <p class="text-body-secondary mb-4">
        Fahrzeuge erfassen und Kunden zuordnen.
    </p>

    <?php if ($meldung !== null): ?>
        <div class="alert <?= $meldung['typ'] === 'success'
            ? 'alert-success' : 'alert-danger' ?>" role="alert">
            <?= e($meldung['text']) ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <section class="col-12 col-lg-4" aria-labelledby="formular-titel">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 id="formular-titel" class="h5">Fahrzeug anlegen</h2>

                    <?php if (empty($kunden)): ?>
                        <div class="alert alert-info mb-0">
                            Bitte zuerst einen
                            <a href="index.php" class="alert-link">
                                Kunden anlegen</a>.
                        </div>
                    <?php else: ?>
                        <p class="small text-body-secondary">
                            Alle Felder sind Pflichtfelder.
                        </p>

                        <form action="add_fahrzeug.php" method="post">
                            <input type="hidden" name="csrf_token"
                                   value="<?= e($_SESSION['csrf_token']) ?>">

                            <div class="mb-3">
                                <label for="marke" class="form-label">Marke</label>
                                <input type="text" class="form-control"
                                       id="marke" name="marke"
                                       maxlength="50" required
                                       value="<?= e($eingaben['marke'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label for="modell" class="form-label">Modell</label>
                                <input type="text" class="form-control"
                                       id="modell" name="modell"
                                       maxlength="50" required
                                       value="<?= e($eingaben['modell'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label for="kennzeichen" class="form-label">
                                    Kennzeichen
                                </label>
                                <input type="text" class="form-control"
                                       id="kennzeichen" name="kennzeichen"
                                       maxlength="20" required
                                       value="<?= e($eingaben['kennzeichen'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label for="baujahr" class="form-label">
                                    Baujahr
                                </label>
                                <input type="number" class="form-control"
                                       id="baujahr" name="baujahr"
                                       min="1885" max="<?= date('Y') ?>"
                                       step="1" required
                                       value="<?= e($eingaben['baujahr'] ?? '') ?>">
                            </div>

                            <!-- Die Kunden-ID wird übertragen, nicht der Name. -->
                            <div class="mb-4">
                                <label for="kunde_id" class="form-label">
                                    Kunde
                                </label>
                                <select class="form-select"
                                        id="kunde_id" name="kunde_id" required>
                                    <option value="">Bitte auswählen</option>

                                    <?php foreach ($kunden as $kunde): ?>
                                        <option
                                            value="<?= e($kunde['id']) ?>"
                                            <?= (string) ($eingaben['kunde_id'] ?? '')
                                                === (string) $kunde['id']
                                                ? 'selected' : '' ?>
                                        >
                                            <?= e(
                                                $kunde['nachname'] . ', '
                                                . $kunde['vorname']
                                                . ' (ID ' . $kunde['id'] . ')'
                                            ) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                Fahrzeug speichern
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="col-12 col-lg-8" aria-labelledby="liste-titel">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-3">
                        <h2 id="liste-titel" class="h5 mb-0">Fahrzeugliste</h2>
                        <span class="badge text-bg-secondary">
                            <?= count($fahrzeuge) ?> Fahrzeuge
                        </span>
                    </div>

                    <?php if (empty($fahrzeuge)): ?>
                        <p class="text-body-secondary">
                            Noch keine Fahrzeuge vorhanden.
                        </p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Fahrzeug</th>
                                        <th scope="col">Kennzeichen</th>
                                        <th scope="col">Baujahr</th>
                                        <th scope="col">Kunden</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($fahrzeuge as $fahrzeug): ?>
                                        <tr>
                                            <td>
                                                <?= e(
                                                    $fahrzeug['marke'] . ' '
                                                    . $fahrzeug['modell']
                                                ) ?>
                                            </td>
                                            <td><?= e($fahrzeug['kennzeichen']) ?></td>
                                            <td><?= e($fahrzeug['baujahr']) ?></td>
                                            <td>
                                                <?= e(implode(
                                                    ', ',
                                                    $kundenProFahrzeug[$fahrzeug['id']]
                                                        ?? ['Keine Zuordnung']
                                                )) ?>
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
    </div>
</main>

<footer class="container py-4 small text-body-secondary">
    MotoLog · Kunden, Fahrzeuge und Wartungen
</footer>
</body>
</html>