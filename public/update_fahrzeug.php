<?php
require_once __DIR__ . '/../includes/init.php';

// Fehler, Fahrzeugwerte und ausgewählte Kunden über den Redirect erhalten.
function zurueckZurFahrzeugbearbeitung($fahrzeugId, $typ, $text, $eingaben = [], $kundenIds = [])
{
    $_SESSION['fahrzeug_edit_meldung'] = ['typ' => $typ, 'text' => $text];
    $_SESSION['fahrzeug_edit_eingaben'] = $eingaben;
    $_SESSION['fahrzeug_edit_kunden_ids'] = array_map('strval', $kundenIds);
    header('Location: edit_fahrzeug.php?id=' . rawurlencode((string) $fahrzeugId), true, 303);
    exit;
}

function zurueckZurFahrzeugliste($typ, $text)
{
    $_SESSION['fahrzeug_meldung'] = ['typ' => $typ, 'text' => $text];
    header('Location: fahrzeuge.php', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: fahrzeuge.php', true, 303);
    exit;
}

$idWert = $_POST['id'] ?? '';
$fahrzeugId = is_string($idWert) ? filter_var($idWert, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
if ($fahrzeugId === false) {
    zurueckZurFahrzeugliste('danger', 'Die Anfrage enthält keine gültige Fahrzeug-ID.');
}

// Nur ein passender Sitzungstoken erlaubt die Änderung des Fahrzeugs und seiner Zuordnungen.
$token = $_POST['csrf_token'] ?? '';
if (!is_string($token) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    zurueckZurFahrzeugbearbeitung($fahrzeugId, 'danger', 'Die Formularprüfung ist fehlgeschlagen. Bitte erneut versuchen.');
}

$daten = [];
foreach (['marke', 'modell', 'kennzeichen', 'baujahr'] as $feld) {
    $wert = $_POST[$feld] ?? '';
    if (!is_string($wert)) {
        zurueckZurFahrzeugbearbeitung($fahrzeugId, 'danger', 'Ungültige Eingabe.');
    }
    $daten[$feld] = trim($wert);
}

// Das Kennzeichen wird wie beim Anlegen in Großbuchstaben vereinheitlicht.
$daten['kennzeichen'] = mb_strtoupper($daten['kennzeichen'], 'UTF-8');
$kundenIds = [];
$kundenQuelle = $_POST['kunden_ids'] ?? [];

// Fehlende Checkboxen bedeuten bewusst: keine Kundenzuordnung.
if (!is_array($kundenQuelle)) {
    zurueckZurFahrzeugbearbeitung($fahrzeugId, 'danger', 'Ungültige Kundenzuordnung.', $daten);
}

foreach ($kundenQuelle as $kundenIdWert) {
    // Verschachtelte Arrays und andere Nicht-Strings werden ausdrücklich abgelehnt.
    if (!is_string($kundenIdWert)) {
        zurueckZurFahrzeugbearbeitung($fahrzeugId, 'danger', 'Ungültige Kundenzuordnung.', $daten, $kundenIds);
    }

    $kundenId = filter_var($kundenIdWert, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($kundenId === false) {
        zurueckZurFahrzeugbearbeitung($fahrzeugId, 'danger', 'Ungültige Kundenzuordnung.', $daten, $kundenIds);
    }

    $kundenIds[(string) $kundenId] = $kundenId;
}
$kundenIds = array_values($kundenIds);

if (in_array('', $daten, true)) {
    zurueckZurFahrzeugbearbeitung($fahrzeugId, 'danger', 'Bitte alle Pflichtfelder ausfüllen.', $daten, $kundenIds);
}

foreach (['marke' => 50, 'modell' => 50, 'kennzeichen' => 20] as $feld => $maximum) {
    if (mb_strlen($daten[$feld], 'UTF-8') > $maximum) {
        zurueckZurFahrzeugbearbeitung($fahrzeugId, 'danger', 'Eine Eingabe ist zu lang.', $daten, $kundenIds);
    }
}

$baujahr = filter_var(
    $daten['baujahr'],
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1885, 'max_range' => (int) date('Y')]]
);
if ($baujahr === false) {
    zurueckZurFahrzeugbearbeitung($fahrzeugId, 'danger', 'Bitte ein gültiges Baujahr eingeben.', $daten, $kundenIds);
}

try {
    // Alle Fahrzeug- und Beziehungsänderungen bleiben in einer gemeinsamen Transaktion.
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT id FROM fahrzeuge WHERE id = :id');
    $stmt->execute(['id' => $fahrzeugId]);
    if ($stmt->fetchColumn() === false) {
        $pdo->rollBack();
        zurueckZurFahrzeugliste('danger', 'Fahrzeug nicht gefunden.');
    }

    // Ein Kennzeichen darf nur zu einem anderen Fahrzeug nicht bereits gehören.
    $stmt = $pdo->prepare(
        'SELECT id FROM fahrzeuge
         WHERE kennzeichen = :kennzeichen AND id <> :id'
    );
    $stmt->execute(['kennzeichen' => $daten['kennzeichen'], 'id' => $fahrzeugId]);
    if ($stmt->fetchColumn() !== false) {
        $pdo->rollBack();
        zurueckZurFahrzeugbearbeitung(
            $fahrzeugId,
            'danger',
            'Dieses Kennzeichen ist bereits einem anderen Fahrzeug zugeordnet.',
            $daten,
            $kundenIds
        );
    }

    if ($kundenIds !== []) {
        $platzhalter = implode(', ', array_fill(0, count($kundenIds), '?'));
        $stmt = $pdo->prepare('SELECT id FROM kunden WHERE id IN (' . $platzhalter . ')');
        $stmt->execute($kundenIds);
        $vorhandeneKunden = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        $fehlendeKunden = array_diff($kundenIds, $vorhandeneKunden);
        if ($fehlendeKunden !== []) {
            $pdo->rollBack();
            zurueckZurFahrzeugbearbeitung(
                $fahrzeugId,
                'danger',
                'Mindestens ein ausgewählter Kunde existiert nicht mehr.',
                $daten,
                $kundenIds
            );
        }
    }

    $stmt = $pdo->prepare(
        'UPDATE fahrzeuge
         SET marke = :marke, modell = :modell, kennzeichen = :kennzeichen, baujahr = :baujahr
         WHERE id = :id'
    );
    $stmt->execute([
        'marke' => $daten['marke'],
        'modell' => $daten['modell'],
        'kennzeichen' => $daten['kennzeichen'],
        'baujahr' => $baujahr,
        'id' => $fahrzeugId,
    ]);

    // Die alten Links werden nur für dieses Fahrzeug ersetzt; Kunden und Wartungen bleiben unberührt.
    $stmt = $pdo->prepare('DELETE FROM kunde_fahrzeug WHERE fahrzeug_id = :fahrzeug_id');
    $stmt->execute(['fahrzeug_id' => $fahrzeugId]);

    $stmt = $pdo->prepare(
        'INSERT INTO kunde_fahrzeug (kunde_id, fahrzeug_id)
         VALUES (:kunde_id, :fahrzeug_id)'
    );
    foreach ($kundenIds as $kundenId) {
        $stmt->execute(['kunde_id' => $kundenId, 'fahrzeug_id' => $fahrzeugId]);
    }

    $pdo->commit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());
    $text = (($e->errorInfo[1] ?? null) === 1062)
        ? 'Dieses Kennzeichen ist bereits einem anderen Fahrzeug zugeordnet.'
        : 'Speichern fehlgeschlagen. Bitte später erneut versuchen.';
    zurueckZurFahrzeugbearbeitung($fahrzeugId, 'danger', $text, $daten, $kundenIds);
}

zurueckZurFahrzeugliste('success', 'Fahrzeugdaten erfolgreich aktualisiert.');
