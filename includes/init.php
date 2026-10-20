<?php
// Sitzung starten, bevor Sitzungsdaten gelesen oder geschrieben werden.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Gemeinsame Datenbankverbindung laden.
require_once __DIR__ . '/../config/db.php';

// Pro Sitzung einen zufälligen CSRF-Token bereitstellen.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
