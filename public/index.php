<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/functions.php';

// Meldung und bisherige Eingaben einmalig aus der Sitzung übernehmen.
$meldung = $_SESSION['kunden_meldung'] ?? null;
$eingaben = $_SESSION['kunden_eingaben'] ?? [];
unset($_SESSION['kunden_meldung'], $_SESSION['kunden_eingaben']);

// Kunden alphabetisch nach Nachname und Vorname laden.
$kunden = $pdo->query(
    'SELECT id, vorname, nachname, telefon, email
     FROM kunden
     ORDER BY nachname, vorname'
)->fetchAll();

$seitentitel = 'MotoLog – Kunden';
$aktuelleSeite = 'kunden';
require __DIR__ . '/../templates/header.php';
?>
<main class="container py-4">
    <div class="mb-4">
        <h1 class="h2 mb-1">Kundenübersicht</h1>
        <p class="text-body-secondary mb-0">Kunden anlegen und Kontaktdaten im Blick behalten.</p>
    </div>
    <?php require __DIR__ . '/../templates/message.php'; ?>
    <div class="row g-4">
        <?php require __DIR__ . '/../templates/kunden/form.php'; ?>
        <?php require __DIR__ . '/../templates/kunden/table.php'; ?>
    </div>
</main>
<?php require __DIR__ . '/../templates/footer.php'; ?>
