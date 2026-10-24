<?php
require_once __DIR__ . '/../includes/init.php';

// Fehler und Eingaben für den nächsten Aufruf der Bearbeitungsseite speichern.
function zurueckZurKundenbearbeitung($kundeId, $typ, $text, $eingaben = [])
{
    $_SESSION['kunden_edit_meldung'] = ['typ' => $typ, 'text' => $text];
    $_SESSION['kunden_edit_eingaben'] = $eingaben;
    header('Location: edit_kunde.php?id=' . rawurlencode((string) $kundeId), true, 303);
    exit;
}

function zurueckZurKundenliste($typ, $text)
{
    $_SESSION['kunden_meldung'] = ['typ' => $typ, 'text' => $text];
    header('Location: index.php', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php', true, 303);
    exit;
}

$idWert = $_POST['id'] ?? '';
$kundeId = is_string($idWert) ? filter_var($idWert, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
if ($kundeId === false) {
    zurueckZurKundenliste('danger', 'Die Anfrage enthält keine gültige Kunden-ID.');
}

// Nur ein passender Sitzungstoken erlaubt die Änderung von Kundendaten.
$token = $_POST['csrf_token'] ?? '';
if (!is_string($token) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    zurueckZurKundenbearbeitung($kundeId, 'danger', 'Die Formularprüfung ist fehlgeschlagen. Bitte erneut versuchen.');
}

$daten = [];
foreach (['vorname', 'nachname', 'telefon', 'email'] as $feld) {
    $wert = $_POST[$feld] ?? '';
    if (!is_string($wert)) {
        zurueckZurKundenbearbeitung($kundeId, 'danger', 'Ungültige Eingabe.');
    }
    $daten[$feld] = trim($wert);
}

// Die Prüfungen entsprechen dem vorhandenen Handler zum Anlegen von Kunden.
if ($daten['vorname'] === '' || $daten['nachname'] === '') {
    zurueckZurKundenbearbeitung($kundeId, 'danger', 'Bitte Vorname und Nachname eingeben.', $daten);
}

$maxLaengen = ['vorname' => 50, 'nachname' => 50, 'telefon' => 30, 'email' => 100];
foreach ($maxLaengen as $feld => $maximum) {
    if (mb_strlen($daten[$feld], 'UTF-8') > $maximum) {
        zurueckZurKundenbearbeitung($kundeId, 'danger', 'Eine Eingabe ist zu lang.', $daten);
    }
}

if ($daten['email'] !== '' && filter_var($daten['email'], FILTER_VALIDATE_EMAIL) === false) {
    zurueckZurKundenbearbeitung($kundeId, 'danger', 'Bitte eine gültige E-Mail-Adresse eingeben.', $daten);
}

try {
    // UPDATE ändert nur den Kunden mit genau dieser ID; fehlende IDs werden nicht stillschweigend angelegt.
    $stmt = $pdo->prepare(
        'UPDATE kunden
         SET vorname = :vorname, nachname = :nachname, telefon = :telefon, email = :email
         WHERE id = :id'
    );
    $stmt->execute([
        'vorname' => $daten['vorname'],
        'nachname' => $daten['nachname'],
        'telefon' => $daten['telefon'] === '' ? null : $daten['telefon'],
        'email' => $daten['email'] === '' ? null : $daten['email'],
        'id' => $kundeId,
    ]);

    if ($stmt->rowCount() === 0) {
        $check = $pdo->prepare('SELECT id FROM kunden WHERE id = :id');
        $check->execute(['id' => $kundeId]);
        if ($check->fetchColumn() === false) {
            zurueckZurKundenliste('danger', 'Kunde nicht gefunden.');
        }
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    zurueckZurKundenbearbeitung($kundeId, 'danger', 'Speichern fehlgeschlagen. Bitte später erneut versuchen.', $daten);
}

zurueckZurKundenliste('success', 'Kundendaten erfolgreich aktualisiert.');
