<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/functions.php';

// Meldung und Eingaben aus dem POST-Redirect-GET-Ablauf einmalig übernehmen.
$meldung = $_SESSION['wartung_meldung'] ?? null;
$eingaben = $_SESSION['wartung_eingaben'] ?? [];
unset($_SESSION['wartung_meldung'], $_SESSION['wartung_eingaben']);

// Nur eine positive Ganzzahl wird als Fahrzeugfilter akzeptiert; sonst werden alle Fahrzeuge angezeigt.
$fahrzeugFilter = $_GET['fahrzeug_id'] ?? '';
if (!is_string($fahrzeugFilter) || filter_var($fahrzeugFilter, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
    $fahrzeugFilter = '';
}

$fahrzeuge = [];
$wartungen = [];
$gesamtkosten = '0.00';

try {
    // Fahrzeuge für das Formular und den Filter laden.
    $fahrzeuge = $pdo->query(
        'SELECT id, marke, modell, kennzeichen
         FROM fahrzeuge ORDER BY marke, modell, kennzeichen, id'
    )->fetchAll();

    $where = '';
    $parameter = [];
    if ($fahrzeugFilter !== '') {
        $where = ' WHERE w.fahrzeug_id = :fahrzeug_id';
        $parameter['fahrzeug_id'] = (int) $fahrzeugFilter;
    }

    // Der JOIN ergänzt jede Wartung um die Fahrzeugdaten; die Sortierung zeigt neue Einträge zuerst.
    $stmt = $pdo->prepare(
        'SELECT w.id, w.datum, w.km_stand, w.beschreibung, w.kosten,
                f.marke, f.modell, f.kennzeichen
         FROM wartungen AS w
         INNER JOIN fahrzeuge AS f ON f.id = w.fahrzeug_id'
         . $where .
        ' ORDER BY w.datum DESC, w.id DESC'
    );
    $stmt->execute($parameter);
    $wartungen = $stmt->fetchAll();

    // SUM und COALESCE bilden die Kostenübersicht passend zum aktuellen Filter, auch bei null Treffern.
    $stmt = $pdo->prepare(
        'SELECT COALESCE(SUM(w.kosten), 0.00)
         FROM wartungen AS w' . $where
    );
    $stmt->execute($parameter);
    $gesamtkosten = $stmt->fetchColumn();
} catch (PDOException $e) {
    // Technische Datenbankfehler werden protokolliert und als neutrale Meldung angezeigt.
    error_log($e->getMessage());
    $meldung = ['typ' => 'danger', 'text' => 'Die Wartungsdaten konnten nicht geladen werden. Bitte später erneut versuchen.'];
}

$seitentitel = 'MotoLog – Wartungen';
$aktuelleSeite = 'wartungen';
require __DIR__ . '/../templates/header.php';
?>
<main class="container py-4">
    <div class="mb-4">
        <h1 class="h2 mb-1">Wartungsübersicht</h1>
        <p class="text-body-secondary mb-0">Wartungen erfassen und Kosten je Fahrzeug nachvollziehen.</p>
    </div>
    <?php require __DIR__ . '/../templates/message.php'; ?>
    <div class="row g-4">
        <?php require __DIR__ . '/../templates/wartungen/form.php'; ?>
        <?php require __DIR__ . '/../templates/wartungen/table.php'; ?>
    </div>
</main>
<?php require __DIR__ . '/../templates/footer.php'; ?>
