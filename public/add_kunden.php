<?php
session_start();

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
) {
    http_response_code(403);
    exit('Ungültige Anfrage. Bitte das Formular erneut öffnen.');
}

// Formulardaten einlesen und bereinigen
$daten = [];

foreach (['vorname', 'nachname', 'telefon', 'email'] as $feld) {
    $wert = $_POST[$feld] ?? '';

    if (!is_string($wert)) {
        http_response_code(400);
        exit('Ungültige Eingabe.');
    }

    $daten[$feld] = trim($wert);
}

// Pflichtfelder prüfen
if ($daten['vorname'] === '' || $daten['nachname'] === '') {
    http_response_code(422);
    exit('Bitte Vorname und Nachname eingeben.');
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
        http_response_code(422);
        exit('Eine Eingabe ist zu lang.');
    }
}

// Optionale E-Mail-Adresse prüfen
if (
    $daten['email'] !== ''
    && filter_var($daten['email'], FILTER_VALIDATE_EMAIL) === false
) {
    http_response_code(422);
    exit('Bitte eine gültige E-Mail-Adresse eingeben.');
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
    http_response_code(500);
    exit('Speichern fehlgeschlagen. Bitte später erneut versuchen.');
}

// Nach dem Speichern zur Übersicht wechseln
header('Location: index.php', true, 303);
exit;