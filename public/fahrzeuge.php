<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/functions.php';

// Meldung und bisherige Eingaben einmalig aus der Sitzung übernehmen.
$meldung = $_SESSION['fahrzeug_meldung'] ?? null;
$eingaben = $_SESSION['fahrzeug_eingaben'] ?? [];
unset($_SESSION['fahrzeug_meldung'], $_SESSION['fahrzeug_eingaben']);

// Kunden für das Auswahlfeld laden.
$kunden = $pdo->query(
    'SELECT id, vorname, nachname FROM kunden ORDER BY nachname, vorname'
)->fetchAll();

// Fahrzeuge und ihre Kundenzuordnungen laden.
$fahrzeuge = $pdo->query(
    'SELECT id, marke, modell, kennzeichen, baujahr
     FROM fahrzeuge ORDER BY marke, modell, id'
)->fetchAll();
$zuordnungen = $pdo->query(
    'SELECT kf.fahrzeug_id, k.vorname, k.nachname
     FROM kunde_fahrzeug AS kf
     INNER JOIN kunden AS k ON k.id = kf.kunde_id
     ORDER BY k.nachname, k.vorname'
)->fetchAll();

$kundenProFahrzeug = [];
foreach ($zuordnungen as $zuordnung) {
    $kundenProFahrzeug[$zuordnung['fahrzeug_id']][] =
        $zuordnung['vorname'] . ' ' . $zuordnung['nachname'];
}

$seitentitel = 'MotoLog – Fahrzeuge';
$aktuelleSeite = 'fahrzeuge';
require __DIR__ . '/../templates/header.php';
?>
<main class="container py-4">
    <h1 class="h2">Fahrzeugübersicht</h1>
    <p class="text-body-secondary mb-4">Fahrzeuge erfassen und Kunden zuordnen.</p>
    <?php require __DIR__ . '/../templates/message.php'; ?>
    <div class="row g-4">
        <?php require __DIR__ . '/../templates/fahrzeuge/form.php'; ?>
        <?php require __DIR__ . '/../templates/fahrzeuge/table.php'; ?>
    </div>
</main>
<?php require __DIR__ . '/../templates/footer.php'; ?>
