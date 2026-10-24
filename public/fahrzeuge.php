<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/functions.php';

// Meldung und bisherige Eingaben einmalig aus der Sitzung übernehmen.
$meldung = $_SESSION['fahrzeug_meldung'] ?? null;
$eingaben = $_SESSION['fahrzeug_eingaben'] ?? [];
unset($_SESSION['fahrzeug_meldung'], $_SESSION['fahrzeug_eingaben']);

// Suchbegriff als einfache GET-Zeichenkette prüfen und auf 100 Zeichen begrenzen.
$fahrzeugsuche = $_GET['suche'] ?? '';
if (!is_string($fahrzeugsuche)) {
    $fahrzeugsuche = '';
}
$fahrzeugsuche = mb_substr(trim($fahrzeugsuche), 0, 100, 'UTF-8');

// Der Kundenfilter akzeptiert nur eine positive ID, "ohne" oder den leeren Wert.
$kundenFilterWert = $_GET['kunde_id'] ?? '';
$kundenFilter = '';
$filterMeldung = null;
if (!is_string($kundenFilterWert)) {
    $filterMeldung = 'Der Kundenfilter ist ungültig.';
} elseif ($kundenFilterWert === 'ohne') {
    $kundenFilter = 'ohne';
} elseif ($kundenFilterWert !== '') {
    $kundenFilterId = filter_var($kundenFilterWert, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($kundenFilterId === false) {
        $filterMeldung = 'Der Kundenfilter ist ungültig.';
    } else {
        $kundenFilter = (string) $kundenFilterId;
    }
}

// Kunden für das Auswahlfeld laden.
$kunden = $pdo->query(
    'SELECT id, vorname, nachname FROM kunden ORDER BY nachname, vorname'
)->fetchAll();

if ($kundenFilter !== '' && $kundenFilter !== 'ohne') {
    $kundenFilterGefunden = false;
    foreach ($kunden as $kunde) {
        if ((string) $kunde['id'] === $kundenFilter) {
            $kundenFilterGefunden = true;
            break;
        }
    }
    if (!$kundenFilterGefunden) {
        $filterMeldung = 'Der ausgewählte Kunde wurde nicht gefunden.';
        $kundenFilter = '';
    }
}

if ($filterMeldung !== null) {
    $meldung = ['typ' => 'danger', 'text' => $filterMeldung];
}

// Fahrzeuge mit optionaler Textsuche und Kundenprüfung laden.
$fahrzeugBedingungen = [];
$fahrzeugParameter = [];
if ($fahrzeugsuche !== '') {
    // %, _ und ! werden escaped, damit LIKE nur wörtlich nach dem Suchtext sucht.
    $suchmuster = '%' . strtr($fahrzeugsuche, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
    $fahrzeugBedingungen[] =
        "(f.marke LIKE :suche_marke ESCAPE '!'\n"
        . " OR f.modell LIKE :suche_modell ESCAPE '!'\n"
        . " OR f.kennzeichen LIKE :suche_kennzeichen ESCAPE '!')";
    // Jedes Vorkommen erhält wegen deaktivierter PDO-Emulation einen eigenen Parameter.
    $fahrzeugParameter = [
        'suche_marke' => $suchmuster,
        'suche_modell' => $suchmuster,
        'suche_kennzeichen' => $suchmuster,
    ];
}

if ($kundenFilter === 'ohne') {
    // NOT EXISTS findet nur Fahrzeuge ohne irgendeine Kundenzuordnung.
    $fahrzeugBedingungen[] = 'NOT EXISTS (SELECT 1 FROM kunde_fahrzeug AS filter_kf WHERE filter_kf.fahrzeug_id = f.id)';
} elseif ($kundenFilter !== '') {
    // EXISTS filtert nach einem Kunden, ohne die vollständige Kundenliste der Maschine abzuschneiden.
    $fahrzeugBedingungen[] = 'EXISTS (SELECT 1 FROM kunde_fahrzeug AS filter_kf WHERE filter_kf.fahrzeug_id = f.id AND filter_kf.kunde_id = :kunden_filter_id)';
    $fahrzeugParameter['kunden_filter_id'] = (int) $kundenFilter;
}

$fahrzeugSql = 'SELECT f.id, f.marke, f.modell, f.kennzeichen, f.baujahr FROM fahrzeuge AS f';
if ($fahrzeugBedingungen !== []) {
    $fahrzeugSql .= ' WHERE ' . implode(' AND ', $fahrzeugBedingungen);
}
$fahrzeugSql .= ' ORDER BY f.marke, f.modell, f.id';
$stmt = $pdo->prepare($fahrzeugSql);
$stmt->execute($fahrzeugParameter);
$fahrzeuge = $stmt->fetchAll();
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
