<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/functions.php';

// Meldung und bisherige Eingaben einmalig aus der Sitzung übernehmen.
$meldung = $_SESSION['kunden_meldung'] ?? null;
$eingaben = $_SESSION['kunden_eingaben'] ?? [];
unset($_SESSION['kunden_meldung'], $_SESSION['kunden_eingaben']);

// Suchbegriff als einfache GET-Zeichenkette prüfen und auf 100 Zeichen begrenzen.
$kundensuche = $_GET['suche'] ?? '';
if (!is_string($kundensuche)) {
    $kundensuche = '';
}
$kundensuche = mb_substr(trim($kundensuche), 0, 100, 'UTF-8');

$kundenBedingungen = [];
$kundenParameter = [];
if ($kundensuche !== '') {
    // %, _ und ! werden escaped, damit die Suche keine LIKE-Muster vom Benutzer ausführt.
    $suchmuster = '%' . strtr($kundensuche, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
    $kundenBedingungen[] =
        "(vorname LIKE :suche_vorname ESCAPE '!'\n"
        . " OR nachname LIKE :suche_nachname ESCAPE '!'\n"
        . " OR COALESCE(telefon, '') LIKE :suche_telefon ESCAPE '!'\n"
        . " OR COALESCE(email, '') LIKE :suche_email ESCAPE '!')";
    // Bei deaktivierten emulierten Prepared Statements erhält jedes LIKE ein eigenes Binding.
    $kundenParameter = [
        'suche_vorname' => $suchmuster,
        'suche_nachname' => $suchmuster,
        'suche_telefon' => $suchmuster,
        'suche_email' => $suchmuster,
    ];
}

$kundenSql = 'SELECT id, vorname, nachname, telefon, email FROM kunden';
if ($kundenBedingungen !== []) {
    $kundenSql .= ' WHERE ' . implode(' AND ', $kundenBedingungen);
}
$kundenSql .= ' ORDER BY nachname, vorname';
$stmt = $pdo->prepare($kundenSql);
$stmt->execute($kundenParameter);
$kunden = $stmt->fetchAll();

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
