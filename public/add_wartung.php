<?php
require_once __DIR__ . '/../includes/init.php';

// Fehlermeldungen und Eingaben bleiben über den Redirect auf der Wartungsseite erhalten.
function zurueckZuWartungen($typ, $text, $eingaben = [])
{
    $_SESSION['wartung_meldung'] = ['typ' => $typ, 'text' => $text];
    $_SESSION['wartung_eingaben'] = $eingaben;
    header('Location: wartungen.php', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: wartungen.php', true, 303);
    exit;
}

// Nur ein unveränderter Sitzungstoken erlaubt das Speichern.
$token = $_POST['csrf_token'] ?? '';
if (!is_string($token) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    zurueckZuWartungen('danger', 'Die Formularprüfung ist fehlgeschlagen. Bitte erneut versuchen.');
}

// Alle Formularwerte müssen Zeichenketten sein und werden vor der Validierung getrimmt.
$daten = [];
foreach (['fahrzeug_id', 'datum', 'km_stand', 'beschreibung', 'kosten'] as $feld) {
    $wert = $_POST[$feld] ?? '';
    if (!is_string($wert)) {
        zurueckZuWartungen('danger', 'Ungültige Eingabe.');
    }
    $daten[$feld] = trim($wert);
}

if (in_array('', $daten, true)) {
    zurueckZuWartungen('danger', 'Bitte alle Pflichtfelder ausfüllen.', $daten);
}

$fahrzeugId = filter_var($daten['fahrzeug_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$kmStand = filter_var($daten['km_stand'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 2147483647]]);

// Das Datum wird streng geparst und zusätzlich auf Kalenderfehler geprüft.
$datumObjekt = DateTime::createFromFormat('!Y-m-d', $daten['datum']);
$datumFehler = DateTime::getLastErrors();
$datumGueltig = $datumObjekt !== false
    && ($datumFehler === false || ($datumFehler['warning_count'] === 0 && $datumFehler['error_count'] === 0))
    && $datumObjekt->format('Y-m-d') === $daten['datum'];

// Kosten bleiben als validierte Dezimalzeichenkette erhalten, damit kein float-Rundungsfehler entsteht.
$kosten = str_replace(',', '.', $daten['kosten']);
$kostenGueltig = preg_match('/^(?:0|[1-9]\d{0,7})(?:\.\d{1,2})?$/', $kosten) === 1;
if ($kostenGueltig) {
    [$kostenGanzzahl, $kostenNachkomma] = array_pad(explode('.', $kosten, 2), 2, '');
    $kostenGueltig = (int) $kostenGanzzahl <= 99999999;
    $kosten = $kostenGanzzahl . '.' . str_pad($kostenNachkomma, 2, '0');
}

if ($fahrzeugId === false || $kmStand === false || !$datumGueltig || mb_strlen($daten['beschreibung'], 'UTF-8') > 5000 || !$kostenGueltig) {
    zurueckZuWartungen('danger', 'Bitte Datum, Fahrzeug, nichtnegativen Kilometerstand, Beschreibung und Kosten korrekt eingeben.', $daten);
}

try {
    // Die ID wird serverseitig geprüft, weil ein Formularwert allein kein existierendes Fahrzeug garantiert.
    $stmt = $pdo->prepare('SELECT id FROM fahrzeuge WHERE id = :id');
    $stmt->execute(['id' => $fahrzeugId]);
    if ($stmt->fetchColumn() === false) {
        zurueckZuWartungen('danger', 'Das ausgewählte Fahrzeug existiert nicht mehr.', $daten);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO wartungen (fahrzeug_id, datum, km_stand, beschreibung, kosten)
         VALUES (:fahrzeug_id, :datum, :km_stand, :beschreibung, :kosten)'
    );
    $stmt->execute([
        'fahrzeug_id' => $fahrzeugId,
        'datum' => $daten['datum'],
        'km_stand' => $kmStand,
        'beschreibung' => $daten['beschreibung'],
        'kosten' => $kosten,
    ]);
} catch (PDOException $e) {
    // Technische Details werden protokolliert, aber nicht an die Benutzeroberfläche weitergegeben.
    error_log($e->getMessage());
    zurueckZuWartungen('danger', 'Speichern fehlgeschlagen. Bitte später erneut versuchen.', $daten);
}

zurueckZuWartungen('success', 'Wartung erfolgreich gespeichert.');
