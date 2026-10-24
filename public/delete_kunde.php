<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/functions.php';

// Meldungen für die Bestätigungsseite einmalig aus der Sitzung übernehmen.
$meldung = $_SESSION['kunden_delete_meldung'] ?? null;
unset($_SESSION['kunden_delete_meldung']);

function zurueckZurKundenlisteNachLoeschung($typ, $text)
{
    $_SESSION['kunden_meldung'] = ['typ' => $typ, 'text' => $text];
    header('Location: index.php', true, 303);
    exit;
}

$methode = $_SERVER['REQUEST_METHOD'];
if ($methode !== 'GET' && $methode !== 'POST') {
    zurueckZurKundenlisteNachLoeschung('danger', 'Nicht unterstützte Anfrage.');
}

$idQuelle = $methode === 'POST' ? ($_POST['id'] ?? '') : ($_GET['id'] ?? '');
$kundeId = is_string($idQuelle) ? filter_var($idQuelle, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;

if ($kundeId === false) {
    zurueckZurKundenlisteNachLoeschung('danger', 'Die Anfrage enthält keine gültige Kunden-ID.');
}

if ($methode === 'POST') {
    // Das Löschen ist ausschließlich per POST und mit einem gültigen Sitzungstoken erlaubt.
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        $_SESSION['kunden_delete_meldung'] = ['typ' => 'danger', 'text' => 'Die Formularprüfung ist fehlgeschlagen. Bitte erneut versuchen.'];
        header('Location: delete_kunde.php?id=' . rawurlencode((string) $kundeId), true, 303);
        exit;
    }

    try {
        // DELETE entfernt den Kunden; ON DELETE CASCADE entfernt nur seine Zuordnungszeilen.
        $stmt = $pdo->prepare('DELETE FROM kunden WHERE id = :id');
        $stmt->execute(['id' => $kundeId]);
        if ($stmt->rowCount() === 0) {
            zurueckZurKundenlisteNachLoeschung('danger', 'Kunde nicht gefunden oder bereits gelöscht.');
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $_SESSION['kunden_delete_meldung'] = ['typ' => 'danger', 'text' => 'Löschen fehlgeschlagen. Bitte später erneut versuchen.'];
        header('Location: delete_kunde.php?id=' . rawurlencode((string) $kundeId), true, 303);
        exit;
    }

    zurueckZurKundenlisteNachLoeschung('success', 'Kunde erfolgreich gelöscht.');
}

$kunde = null;
$fahrzeugZuordnungen = 0;
try {
    // Die Zuordnungsanzahl wird vor dem Löschen für die verständliche Bestätigung abgefragt.
    $stmt = $pdo->prepare('SELECT id, vorname, nachname FROM kunden WHERE id = :id');
    $stmt->execute(['id' => $kundeId]);
    $kunde = $stmt->fetch();

    if ($kunde !== false) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM kunde_fahrzeug WHERE kunde_id = :kunde_id');
        $stmt->execute(['kunde_id' => $kundeId]);
        $fahrzeugZuordnungen = (int) $stmt->fetchColumn();
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $meldung = ['typ' => 'danger', 'text' => 'Die Löschbestätigung konnte nicht geladen werden. Bitte später erneut versuchen.'];
}

if ($kunde === false || $kunde === null) {
    zurueckZurKundenlisteNachLoeschung('danger', 'Kunde nicht gefunden.');
}

$seitentitel = 'MotoLog – Kunden löschen';
$aktuelleSeite = 'kunden';
require __DIR__ . '/../templates/header.php';
?>
<main class="container py-4">
    <?php require __DIR__ . '/../templates/message.php'; ?>
    <section class="col-12 col-lg-7" aria-labelledby="kunde-loeschen-titel">
        <div class="card border-0 shadow-sm"><div class="card-body p-4">
            <h1 id="kunde-loeschen-titel" class="h2">Kunde löschen</h1>
            <p>Möchten Sie den Kunden <strong><?= e($kunde['vorname'] . ' ' . $kunde['nachname']) ?></strong> wirklich löschen?</p>
            <p>Dieser Kunde hat <?= e($fahrzeugZuordnungen) ?> Fahrzeugzuordnung<?= $fahrzeugZuordnungen === 1 ? '' : 'en' ?>.</p>
            <div class="alert alert-warning">Der Kunde und seine Fahrzeugzuordnungen werden gelöscht.<br>Fahrzeuge und Wartungseinträge bleiben erhalten.</div>
            <form method="post" class="d-flex flex-wrap gap-2">
                <input type="hidden" name="id" value="<?= e($kunde['id']) ?>">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <button type="submit" class="btn btn-danger">Endgültig löschen</button>
                <a href="index.php" class="btn btn-outline-secondary">Abbrechen</a>
            </form>
        </div></div>
    </section>
</main>
<?php require __DIR__ . '/../templates/footer.php'; ?>
