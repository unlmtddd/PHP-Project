<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/functions.php';

// Meldungen für die Bestätigungsseite einmalig aus der Sitzung übernehmen.
$meldung = $_SESSION['wartung_delete_meldung'] ?? null;
unset($_SESSION['wartung_delete_meldung']);

function zurueckZurWartungslisteNachLoeschung($fahrzeugFilter, $typ, $text)
{
    $_SESSION['wartung_meldung'] = ['typ' => $typ, 'text' => $text];
    $query = $fahrzeugFilter !== '' ? '?fahrzeug_id=' . rawurlencode((string) $fahrzeugFilter) : '';
    header('Location: wartungen.php' . $query, true, 303);
    exit;
}

$methode = $_SERVER['REQUEST_METHOD'];
if ($methode !== 'GET' && $methode !== 'POST') {
    zurueckZurWartungslisteNachLoeschung('', 'danger', 'Nicht unterstützte Anfrage.');
}

$idQuelle = $methode === 'POST' ? ($_POST['id'] ?? '') : ($_GET['id'] ?? '');
$wartungId = is_string($idQuelle) ? filter_var($idQuelle, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
$filterQuelle = $methode === 'POST' ? ($_POST['fahrzeug_id'] ?? '') : ($_GET['fahrzeug_id'] ?? '');
$fahrzeugFilter = '';
if ($filterQuelle !== '') {
    $fahrzeugFilter = is_string($filterQuelle) ? filter_var($filterQuelle, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
    if ($fahrzeugFilter === false) {
        zurueckZurWartungslisteNachLoeschung('', 'danger', 'Der Fahrzeugfilter ist ungültig.');
    }
}

if ($wartungId === false) {
    zurueckZurWartungslisteNachLoeschung($fahrzeugFilter, 'danger', 'Die Anfrage enthält keine gültige Wartungs-ID.');
}

if ($methode === 'POST') {
    // Das Löschen ist ausschließlich per POST und mit einem gültigen Sitzungstoken erlaubt.
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        $_SESSION['wartung_delete_meldung'] = ['typ' => 'danger', 'text' => 'Die Formularprüfung ist fehlgeschlagen. Bitte erneut versuchen.'];
        $query = '?id=' . rawurlencode((string) $wartungId);
        if ($fahrzeugFilter !== '') {
            $query .= '&fahrzeug_id=' . rawurlencode((string) $fahrzeugFilter);
        }
        header('Location: delete_wartung.php' . $query, true, 303);
        exit;
    }

    try {
        // Nur der Wartungssatz mit dieser ID wird entfernt; Fahrzeug und Zuordnungen bleiben bestehen.
        $stmt = $pdo->prepare('DELETE FROM wartungen WHERE id = :id');
        $stmt->execute(['id' => $wartungId]);
        if ($stmt->rowCount() === 0) {
            zurueckZurWartungslisteNachLoeschung($fahrzeugFilter, 'danger', 'Wartung nicht gefunden oder bereits gelöscht.');
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $_SESSION['wartung_delete_meldung'] = ['typ' => 'danger', 'text' => 'Löschen fehlgeschlagen. Bitte später erneut versuchen.'];
        $query = '?id=' . rawurlencode((string) $wartungId);
        if ($fahrzeugFilter !== '') {
            $query .= '&fahrzeug_id=' . rawurlencode((string) $fahrzeugFilter);
        }
        header('Location: delete_wartung.php' . $query, true, 303);
        exit;
    }

    zurueckZurWartungslisteNachLoeschung($fahrzeugFilter, 'success', 'Wartung erfolgreich gelöscht.');
}

$wartung = null;
$ladefehler = false;
try {
    // JOIN liefert die Fahrzeugdaten für die verständliche Bestätigung, ohne eine Änderung auszuführen.
    $stmt = $pdo->prepare(
        'SELECT w.id, w.datum, w.beschreibung, w.kosten,
                f.marke, f.modell, f.kennzeichen
         FROM wartungen AS w
         INNER JOIN fahrzeuge AS f ON f.id = w.fahrzeug_id
         WHERE w.id = :id'
    );
    $stmt->execute(['id' => $wartungId]);
    $wartung = $stmt->fetch();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $ladefehler = true;
    $meldung = ['typ' => 'danger', 'text' => 'Die Löschbestätigung konnte nicht geladen werden. Bitte später erneut versuchen.'];
}

if (!$ladefehler && ($wartung === false || $wartung === null)) {
    zurueckZurWartungslisteNachLoeschung($fahrzeugFilter, 'danger', 'Wartung nicht gefunden.');
}

$filterSuffix = $fahrzeugFilter !== '' ? '?fahrzeug_id=' . rawurlencode((string) $fahrzeugFilter) : '';
$seitentitel = 'MotoLog – Wartung löschen';
$aktuelleSeite = 'wartungen';
require __DIR__ . '/../templates/header.php';
?>
<main class="container py-4">
    <?php require __DIR__ . '/../templates/message.php'; ?>
    <?php if (!$ladefehler && $wartung !== null): ?>
        <?php require __DIR__ . '/../templates/wartungen/delete_confirm.php'; ?>
    <?php else: ?>
        <p><a href="wartungen.php<?= $filterSuffix ?>" class="btn btn-outline-secondary">Zur Wartungshistorie</a></p>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../templates/footer.php'; ?>
