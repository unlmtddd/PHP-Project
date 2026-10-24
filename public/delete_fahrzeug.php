<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/functions.php';

// Meldungen für die Bestätigungsseite einmalig aus der Sitzung übernehmen.
$meldung = $_SESSION['fahrzeug_delete_meldung'] ?? null;
unset($_SESSION['fahrzeug_delete_meldung']);

function zurueckZurFahrzeuglisteNachLoeschung($typ, $text)
{
    $_SESSION['fahrzeug_meldung'] = ['typ' => $typ, 'text' => $text];
    header('Location: fahrzeuge.php', true, 303);
    exit;
}

$methode = $_SERVER['REQUEST_METHOD'];
if ($methode !== 'GET' && $methode !== 'POST') {
    zurueckZurFahrzeuglisteNachLoeschung('danger', 'Nicht unterstützte Anfrage.');
}

$idQuelle = $methode === 'POST' ? ($_POST['id'] ?? '') : ($_GET['id'] ?? '');
$fahrzeugId = is_string($idQuelle) ? filter_var($idQuelle, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
if ($fahrzeugId === false) {
    zurueckZurFahrzeuglisteNachLoeschung('danger', 'Die Anfrage enthält keine gültige Fahrzeug-ID.');
}

if ($methode === 'POST') {
    // Das Löschen ist ausschließlich per POST und mit einem gültigen Sitzungstoken erlaubt.
    $token = $_POST['csrf_token'] ?? '';
    if (
        !is_string($token)
        || !isset($_SESSION['csrf_token'])
        || !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        $_SESSION['fahrzeug_delete_meldung'] = [
            'typ' => 'danger',
            'text' => 'Die Formularprüfung ist fehlgeschlagen. Bitte erneut versuchen.',
        ];
        header('Location: delete_fahrzeug.php?id=' . rawurlencode((string) $fahrzeugId), true, 303);
        exit;
    }

    try {
        // ON DELETE CASCADE entfernt die Zuordnungen und Wartungen des Fahrzeugs atomar mit.
        $stmt = $pdo->prepare('DELETE FROM fahrzeuge WHERE id = :id');
        $stmt->execute(['id' => $fahrzeugId]);
        if ($stmt->rowCount() === 0) {
            zurueckZurFahrzeuglisteNachLoeschung('danger', 'Fahrzeug nicht gefunden oder bereits gelöscht.');
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $_SESSION['fahrzeug_delete_meldung'] = ['typ' => 'danger', 'text' => 'Löschen fehlgeschlagen. Bitte später erneut versuchen.'];
        header('Location: delete_fahrzeug.php?id=' . rawurlencode((string) $fahrzeugId), true, 303);
        exit;
    }

    zurueckZurFahrzeuglisteNachLoeschung('success', 'Fahrzeug erfolgreich gelöscht.');
}

$fahrzeug = null;
$wartungsAnzahl = 0;
$ladefehler = false;
try {
    // Fahrzeugdaten und Wartungsanzahl vor der Bestätigung laden, ohne etwas zu löschen.
    $stmt = $pdo->prepare(
        'SELECT id, marke, modell, kennzeichen
         FROM fahrzeuge WHERE id = :id'
    );
    $stmt->execute(['id' => $fahrzeugId]);
    $fahrzeug = $stmt->fetch();

    if ($fahrzeug !== false) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM wartungen WHERE fahrzeug_id = :fahrzeug_id');
        $stmt->execute(['fahrzeug_id' => $fahrzeugId]);
        $wartungsAnzahl = (int) $stmt->fetchColumn();
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $ladefehler = true;
    $meldung = ['typ' => 'danger', 'text' => 'Die Löschbestätigung konnte nicht geladen werden. Bitte später erneut versuchen.'];
}

if (!$ladefehler && ($fahrzeug === false || $fahrzeug === null)) {
    zurueckZurFahrzeuglisteNachLoeschung('danger', 'Fahrzeug nicht gefunden.');
}

$seitentitel = 'MotoLog – Fahrzeug löschen';
$aktuelleSeite = 'fahrzeuge';
require __DIR__ . '/../templates/header.php';
?>
<main class="container py-4">
    <?php require __DIR__ . '/../templates/message.php'; ?>
    <?php if (!$ladefehler && $fahrzeug !== null): ?>
        <?php require __DIR__ . '/../templates/fahrzeuge/delete_confirm.php'; ?>
    <?php else: ?>
        <p><a href="fahrzeuge.php" class="btn btn-outline-secondary">Zur Fahrzeugliste</a></p>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../templates/footer.php'; ?>
