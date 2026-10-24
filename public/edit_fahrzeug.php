<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/functions.php';

// Meldung und Eingaben aus dem Bearbeitungs-Redirect einmalig übernehmen.
$meldung = $_SESSION['fahrzeug_edit_meldung'] ?? null;
$eingabenVorhanden = array_key_exists('fahrzeug_edit_eingaben', $_SESSION);
$eingaben = $_SESSION['fahrzeug_edit_eingaben'] ?? [];
$kundenIdsVorhanden = array_key_exists('fahrzeug_edit_kunden_ids', $_SESSION);
$ausgewaehlteKundenIds = $_SESSION['fahrzeug_edit_kunden_ids'] ?? [];
unset($_SESSION['fahrzeug_edit_meldung'], $_SESSION['fahrzeug_edit_eingaben'], $_SESSION['fahrzeug_edit_kunden_ids']);

$id = $_GET['id'] ?? '';
$fahrzeugId = is_string($id) ? filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;

if ($fahrzeugId === false) {
    $_SESSION['fahrzeug_meldung'] = ['typ' => 'danger', 'text' => 'Die Anfrage enthält keine gültige Fahrzeug-ID.'];
    header('Location: fahrzeuge.php', true, 303);
    exit;
}

$fahrzeug = null;
$fahrzeugLadefehler = false;
$kunden = [];
try {
    // Fahrzeug und mögliche Kunden werden mit vorbereiteten Abfragen geladen.
    $stmt = $pdo->prepare(
        'SELECT id, marke, modell, kennzeichen, baujahr
         FROM fahrzeuge WHERE id = :id'
    );
    $stmt->execute(['id' => $fahrzeugId]);
    $fahrzeug = $stmt->fetch();

    if ($fahrzeug !== false) {
        $kunden = $pdo->query(
            'SELECT id, vorname, nachname FROM kunden ORDER BY nachname, vorname, id'
        )->fetchAll();

        if (!$kundenIdsVorhanden) {
            $stmt = $pdo->prepare('SELECT kunde_id FROM kunde_fahrzeug WHERE fahrzeug_id = :fahrzeug_id');
            $stmt->execute(['fahrzeug_id' => $fahrzeugId]);
            $ausgewaehlteKundenIds = array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        }
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $fahrzeugLadefehler = true;
    $meldung = ['typ' => 'danger', 'text' => 'Die Fahrzeugdaten konnten nicht geladen werden. Bitte später erneut versuchen.'];
}

if (!$fahrzeugLadefehler && ($fahrzeug === false || $fahrzeug === null)) {
    $meldung = ['typ' => 'danger', 'text' => 'Fahrzeug nicht gefunden.'];
    $fahrzeug = null;
}

if ($fahrzeug !== null && !$eingabenVorhanden) {
    $eingaben = [
        'marke' => $fahrzeug['marke'],
        'modell' => $fahrzeug['modell'],
        'kennzeichen' => $fahrzeug['kennzeichen'],
        'baujahr' => $fahrzeug['baujahr'],
    ];
}

$seitentitel = 'MotoLog – Fahrzeug bearbeiten';
$aktuelleSeite = 'fahrzeuge';
require __DIR__ . '/../templates/header.php';
?>
<main class="container py-4">
    <?php require __DIR__ . '/../templates/message.php'; ?>
    <?php if (!$fahrzeugLadefehler && $fahrzeug !== null): ?>
        <?php require __DIR__ . '/../templates/fahrzeuge/edit_form.php'; ?>
    <?php else: ?>
        <p><a href="fahrzeuge.php" class="btn btn-outline-secondary">Zur Fahrzeugliste</a></p>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../templates/footer.php'; ?>
