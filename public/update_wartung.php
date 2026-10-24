<?php
require_once __DIR__ . '/../includes/init.php';

// Fehler und Eingaben für die Bearbeitungsseite über den Redirect erhalten.
function zurueckZurWartungsbearbeitung($wartungId, $fahrzeugFilter, $typ, $text, $eingaben = [])
{
    $_SESSION['wartung_edit_meldung'] = ['typ' => $typ, 'text' => $text];
    $_SESSION['wartung_edit_eingaben'] = $eingaben;
    $query = '?id=' . rawurlencode((string) $wartungId);
    if ($fahrzeugFilter !== '') {
        $query .= '&fahrzeug_id=' . rawurlencode((string) $fahrzeugFilter);
    }
    header('Location: edit_wartung.php' . $query, true, 303);
    exit;
}

function zurueckZurWartungsliste($fahrzeugFilter, $typ, $text)
{
    $_SESSION['wartung_meldung'] = ['typ' => $typ, 'text' => $text];
    $query = $fahrzeugFilter !== '' ? '?fahrzeug_id=' . rawurlencode((string) $fahrzeugFilter) : '';
    header('Location: wartungen.php' . $query, true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: wartungen.php', true, 303);
    exit;
}

$idWert = $_POST['id'] ?? '';
$wartungId = is_string($idWert) ? filter_var($idWert, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
$filterWert = $_POST['filter_fahrzeug_id'] ?? '';
$fahrzeugFilter = '';
if ($filterWert !== '') {
    $fahrzeugFilter = is_string($filterWert) ? filter_var($filterWert, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
    if ($fahrzeugFilter === false) {
        $fahrzeugFilter = '';
        zurueckZurWartungsliste('', 'danger', 'Der Fahrzeugfilter ist ungültig.');
    }
}

if ($wartungId === false) {
    zurueckZurWartungsliste($fahrzeugFilter, 'danger', 'Die Anfrage enthält keine gültige Wartungs-ID.');
}

// Nur ein passender Sitzungstoken erlaubt das Ändern der Wartung.
$token = $_POST['csrf_token'] ?? '';
if (!is_string($token) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    zurueckZurWartungsbearbeitung($wartungId, $fahrzeugFilter, 'danger', 'Die Formularprüfung ist fehlgeschlagen. Bitte erneut versuchen.');
}

$daten = [];
foreach (['fahrzeug_id', 'datum', 'km_stand', 'beschreibung', 'kosten'] as $feld) {
    $wert = $_POST[$feld] ?? '';
    if (!is_string($wert)) {
        zurueckZurWartungsbearbeitung($wartungId, $fahrzeugFilter, 'danger', 'Ungültige Eingabe.');
    }
    $daten[$feld] = trim($wert);
}

if (in_array('', $daten, true)) {
    zurueckZurWartungsbearbeitung($wartungId, $fahrzeugFilter, 'danger', 'Bitte alle Pflichtfelder ausfüllen.', $daten);
}

$fahrzeugId = filter_var($daten['fahrzeug_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$kmStand = filter_var($daten['km_stand'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 2147483647]]);

// Das Datum wird streng als Kalenderdatum geprüft, nicht nur als Zeichenkette.
$datumObjekt = DateTime::createFromFormat('!Y-m-d', $daten['datum']);
$datumFehler = DateTime::getLastErrors();
$datumGueltig = $datumObjekt !== false
    && ($datumFehler === false || ($datumFehler['warning_count'] === 0 && $datumFehler['error_count'] === 0))
    && $datumObjekt->format('Y-m-d') === $daten['datum'];

// Die DECIMAL-Eingabe bleibt eine Zeichenkette und wird nicht in float umgewandelt.
$kosten = str_replace(',', '.', $daten['kosten']);
$kostenGueltig = preg_match('/^(?:0|[1-9]\d{0,7})(?:\.\d{1,2})?$/', $kosten) === 1;
if ($kostenGueltig) {
    [$kostenGanzzahl, $kostenNachkomma] = array_pad(explode('.', $kosten, 2), 2, '');
    $kostenGueltig = (int) $kostenGanzzahl <= 99999999;
    $kosten = $kostenGanzzahl . '.' . str_pad($kostenNachkomma, 2, '0');
}

if ($fahrzeugId === false || $kmStand === false || !$datumGueltig || mb_strlen($daten['beschreibung'], 'UTF-8') > 5000 || !$kostenGueltig) {
    zurueckZurWartungsbearbeitung($wartungId, $fahrzeugFilter, 'danger', 'Bitte Datum, Fahrzeug, Kilometerstand, Beschreibung und Kosten korrekt eingeben.', $daten);
}

try {
    // Die vorhandene Wartung liefert die alte Maschine für die Filtermeldung.
    $stmt = $pdo->prepare('SELECT fahrzeug_id FROM wartungen WHERE id = :id');
    $stmt->execute(['id' => $wartungId]);
    $altesFahrzeugId = $stmt->fetchColumn();
    if ($altesFahrzeugId === false) {
        zurueckZurWartungsliste($fahrzeugFilter, 'danger', 'Wartung nicht gefunden.');
    }

    // Eine verschwundene Zielmaschine darf nicht als ungültiger Fremdschlüssel enden.
    $stmt = $pdo->prepare('SELECT id FROM fahrzeuge WHERE id = :id');
    $stmt->execute(['id' => $fahrzeugId]);
    if ($stmt->fetchColumn() === false) {
        zurueckZurWartungsbearbeitung($wartungId, $fahrzeugFilter, 'danger', 'Das ausgewählte Fahrzeug existiert nicht mehr.', $daten);
    }

    $stmt = $pdo->prepare(
        'UPDATE wartungen
         SET fahrzeug_id = :fahrzeug_id, datum = :datum, km_stand = :km_stand,
             beschreibung = :beschreibung, kosten = :kosten
         WHERE id = :id'
    );
    $stmt->execute([
        'fahrzeug_id' => $fahrzeugId,
        'datum' => $daten['datum'],
        'km_stand' => $kmStand,
        'beschreibung' => $daten['beschreibung'],
        'kosten' => $kosten,
        'id' => $wartungId,
    ]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    zurueckZurWartungsbearbeitung($wartungId, $fahrzeugFilter, 'danger', 'Speichern fehlgeschlagen. Bitte später erneut versuchen.', $daten);
}

$meldungText = 'Wartung erfolgreich aktualisiert.';
if ($fahrzeugFilter !== '' && (int) $altesFahrzeugId === (int) $fahrzeugFilter && (int) $fahrzeugId !== (int) $fahrzeugFilter) {
    $meldungText .= ' Der Eintrag gehört jetzt zu einem anderen Fahrzeug und ist im aktuellen Filter nicht sichtbar.';
}
zurueckZurWartungsliste($fahrzeugFilter, 'success', $meldungText);
