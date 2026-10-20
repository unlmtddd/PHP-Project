<?php
require_once __DIR__ . '/../includes/init.php';

// Rückmeldung und Eingaben für die Fahrzeugseite speichern.
function zurueckZuFahrzeugen($typ, $text, $eingaben = [])
{
    $_SESSION['fahrzeug_meldung'] = [
        'typ' => $typ,
        'text' => $text,
    ];

    $_SESSION['fahrzeug_eingaben'] = $eingaben;

    header('Location: fahrzeuge.php', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: fahrzeuge.php', true, 303);
    exit;
}

// Nur Anfragen mit passendem Sitzungstoken akzeptieren.
$token = $_POST['csrf_token'] ?? '';

if (
    !is_string($token)
    || !isset($_SESSION['csrf_token'])
    || !hash_equals($_SESSION['csrf_token'], $token)
) {
    zurueckZuFahrzeugen(
        'danger',
        'Die Formularprüfung ist fehlgeschlagen. Bitte erneut versuchen.'
    );
}

// Eingaben einlesen und bereinigen.
$daten = [];

foreach (['marke', 'modell', 'kennzeichen', 'baujahr', 'kunde_id'] as $feld) {
    $wert = $_POST[$feld] ?? '';

    if (!is_string($wert)) {
        zurueckZuFahrzeugen('danger', 'Ungültige Eingabe.');
    }

    $daten[$feld] = trim($wert);
}

if (in_array('', $daten, true)) {
    zurueckZuFahrzeugen(
        'danger',
        'Bitte alle Pflichtfelder ausfüllen.',
        $daten
    );
}

// Kennzeichen einheitlich in Großbuchstaben speichern.
$daten['kennzeichen'] = mb_strtoupper($daten['kennzeichen'], 'UTF-8');

foreach (['marke' => 50, 'modell' => 50, 'kennzeichen' => 20] as $feld => $maximum) {
    if (mb_strlen($daten[$feld], 'UTF-8') > $maximum) {
        zurueckZuFahrzeugen(
            'danger',
            'Eine Eingabe ist zu lang.',
            $daten
        );
    }
}

// Für dieses Projekt: Baujahr von 1885 bis zum aktuellen Jahr.
$baujahr = filter_var(
    $daten['baujahr'],
    FILTER_VALIDATE_INT,
    ['options' => [
        'min_range' => 1885,
        'max_range' => (int) date('Y'),
    ]]
);

$kundeId = filter_var(
    $daten['kunde_id'],
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if ($baujahr === false || $kundeId === false) {
    zurueckZuFahrzeugen(
        'danger',
        'Bitte ein gültiges Baujahr und einen Kunden auswählen.',
        $daten
    );
}

try {
    // Eine übermittelte ID allein beweist nicht, dass der Kunde existiert.
    $stmt = $pdo->prepare('SELECT id FROM kunden WHERE id = :id');
    $stmt->execute(['id' => $kundeId]);

    if ($stmt->fetchColumn() === false) {
        zurueckZuFahrzeugen(
            'danger',
            'Der ausgewählte Kunde existiert nicht mehr.',
            $daten
        );
    }

    // Beide INSERT-Anweisungen müssen gemeinsam erfolgreich sein.
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'INSERT INTO fahrzeuge (marke, modell, kennzeichen, baujahr)
         VALUES (:marke, :modell, :kennzeichen, :baujahr)'
    );

    $stmt->execute([
        'marke' => $daten['marke'],
        'modell' => $daten['modell'],
        'kennzeichen' => $daten['kennzeichen'],
        'baujahr' => $baujahr,
    ]);

    // Automatisch erzeugte ID des neuen Fahrzeugs abfragen.
    $fahrzeugId = $pdo->lastInsertId();

    $stmt = $pdo->prepare(
        'INSERT INTO kunde_fahrzeug (kunde_id, fahrzeug_id)
         VALUES (:kunde_id, :fahrzeug_id)'
    );

    $stmt->execute([
        'kunde_id' => $kundeId,
        'fahrzeug_id' => $fahrzeugId,
    ]);

    // Fahrzeug und Kundenzuordnung gemeinsam bestätigen.
    $pdo->commit();
} catch (PDOException $e) {
    // Bei einem Fehler keine unvollständige Fahrzeuganlage zurücklassen.
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());

    if (($e->errorInfo[1] ?? null) === 1062) {
        $text = 'Dieses Kennzeichen ist bereits gespeichert.';
    } elseif (($e->errorInfo[1] ?? null) === 1452) {
        $text = 'Die Kundenzuordnung ist nicht mehr gültig. Bitte erneut auswählen.';
    } else {
        $text = 'Speichern fehlgeschlagen. Bitte später erneut versuchen.';
    }

    zurueckZuFahrzeugen('danger', $text, $daten);
}

zurueckZuFahrzeugen(
    'success',
    'Fahrzeug erfolgreich gespeichert.'
);
