<?php
session_start();
// Meldung und Eingaben für den nächsten Seitenaufruf speichern.
function zurueckZumFormular($typ, $text, $eingaben = [])
{
    $_SESSION['kunden_meldung'] = [
        'typ' => $typ,
        'text' => $text,
    ];

    $_SESSION['kunden_eingaben'] = $eingaben;

    header('Location: index.php', true, 303);
    exit;
}
// Nur POST-Anfragen verarbeiten
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php', true, 303);
    exit;
}

// CSRF-Token prüfen
$token = $_POST['csrf_token'] ?? '';

if (
    !is_string($token)
    || !isset($_SESSION['csrf_token'])
    || !hash_equals($_SESSION['csrf_token'], $token)
) 
zurueckZumFormular(
    'danger',
    'Die Formularprüfung ist fehlgeschlagen. Bitte erneut versuchen.'
);

// Formulardaten einlesen und bereinigen
$daten = [];

foreach (['vorname', 'nachname', 'telefon', 'email'] as $feld) {
    $wert = $_POST[$feld] ?? '';

    if (!is_string($wert)) {
       zurueckZumFormular('danger', 'Ungültige Eingabe.');
    }

    $daten[$feld] = trim($wert);
}

// Pflichtfelder prüfen
if ($daten['vorname'] === '' || $daten['nachname'] === '') {
    zurueckZumFormular(
    'danger',
    'Bitte Vorname und Nachname eingeben.',
    $daten
);
}

// Maximale Feldlängen prüfen
$maxLaengen = [
    'vorname' => 50,
    'nachname' => 50,
    'telefon' => 30,
    'email' => 100,
];

foreach ($maxLaengen as $feld => $maximum) {
    if (mb_strlen($daten[$feld], 'UTF-8') > $maximum) {
       zurueckZumFormular(
    'danger',
    'Eine Eingabe ist zu lang.',
    $daten
);
    }
}

// Optionale E-Mail-Adresse prüfen
if (
    $daten['email'] !== ''
    && filter_var($daten['email'], FILTER_VALIDATE_EMAIL) === false
) {
    zurueckZumFormular(
    'danger',
    'Bitte eine gültige E-Mail-Adresse eingeben.',
    $daten
);
}

require_once __DIR__ . '/../config/db.php';

// Kunden speichern
try {
    $stmt = $pdo->prepare(
        'INSERT INTO kunden (vorname, nachname, telefon, email)
         VALUES (:vorname, :nachname, :telefon, :email)'
    );

    $stmt->execute([
        'vorname' => $daten['vorname'],
        'nachname' => $daten['nachname'],
        'telefon' => $daten['telefon'] === '' ? null : $daten['telefon'],
        'email' => $daten['email'] === '' ? null : $daten['email'],
    ]);
} catch (PDOException $e) {
    error_log($e->getMessage());

    zurueckZumFormular(
        'danger',
        'Speichern fehlgeschlagen. Bitte später erneut versuchen.',
        $daten
    );
}

// Nach erfolgreichem Speichern eine Meldung und ein leeres Formular zeigen.
zurueckZumFormular(
    'success',
    'Kunde erfolgreich gespeichert.'
);