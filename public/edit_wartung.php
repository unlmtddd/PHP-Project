<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/functions.php';

// Meldung und Eingaben aus dem Bearbeitungs-Redirect einmalig übernehmen.
$meldung = $_SESSION['wartung_edit_meldung'] ?? null;
$eingabenVorhanden = array_key_exists('wartung_edit_eingaben', $_SESSION);
$eingaben = $_SESSION['wartung_edit_eingaben'] ?? [];
unset($_SESSION['wartung_edit_meldung'], $_SESSION['wartung_edit_eingaben']);

$id = $_GET['id'] ?? '';
$wartungId = is_string($id) ? filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
$filterWert = $_GET['fahrzeug_id'] ?? '';
$fahrzeugFilter = '';
if ($filterWert !== '') {
    $fahrzeugFilter = is_string($filterWert) ? filter_var($filterWert, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
    if ($fahrzeugFilter === false) {
        $fahrzeugFilter = '';
    }
}
$filterSuffix = $fahrzeugFilter !== '' ? '?fahrzeug_id=' . rawurlencode((string) $fahrzeugFilter) : '';

if ($wartungId === false) {
    $_SESSION['wartung_meldung'] = ['typ' => 'danger', 'text' => 'Die Anfrage enthält keine gültige Wartungs-ID.'];
    header('Location: wartungen.php' . $filterSuffix, true, 303);
    exit;
}

$wartung = null;
$ladefehler = false;
$fahrzeuge = [];
try {
    // Wartung und Fahrzeugliste werden getrennt geladen, damit das Auswahlfeld aktuell bleibt.
    $stmt = $pdo->prepare(
        'SELECT id, fahrzeug_id, datum, km_stand, beschreibung, kosten
         FROM wartungen WHERE id = :id'
    );
    $stmt->execute(['id' => $wartungId]);
    $wartung = $stmt->fetch();

    if ($wartung !== false) {
        $fahrzeuge = $pdo->query(
            'SELECT id, marke, modell, kennzeichen
             FROM fahrzeuge ORDER BY marke, modell, kennzeichen, id'
        )->fetchAll();
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $ladefehler = true;
    $meldung = ['typ' => 'danger', 'text' => 'Die Wartungsdaten konnten nicht geladen werden. Bitte später erneut versuchen.'];
}

if (!$ladefehler && ($wartung === false || $wartung === null)) {
    $meldung = ['typ' => 'danger', 'text' => 'Wartung nicht gefunden.'];
    $wartung = null;
}

if ($wartung !== null && !$eingabenVorhanden) {
    $eingaben = [
        'fahrzeug_id' => $wartung['fahrzeug_id'],
        'datum' => $wartung['datum'],
        'km_stand' => $wartung['km_stand'],
        'beschreibung' => $wartung['beschreibung'],
        'kosten' => $wartung['kosten'],
    ];
}

$seitentitel = 'MotoLog – Wartung bearbeiten';
$aktuelleSeite = 'wartungen';
require __DIR__ . '/../templates/header.php';
?>
<main class="container py-4">
    <?php require __DIR__ . '/../templates/message.php'; ?>
    <?php if (!$ladefehler && $wartung !== null): ?>
        <?php require __DIR__ . '/../templates/wartungen/edit_form.php'; ?>
    <?php else: ?>
        <p><a href="wartungen.php<?= $filterSuffix ?>" class="btn btn-outline-secondary">Zur Wartungshistorie</a></p>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../templates/footer.php'; ?>
