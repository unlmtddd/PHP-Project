<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/functions.php';

// Fehlermeldungen und Eingaben für die Bearbeitungsseite einmalig aus der Sitzung übernehmen.
$meldung = $_SESSION['kunden_edit_meldung'] ?? null;
$eingaben = $_SESSION['kunden_edit_eingaben'] ?? [];
unset($_SESSION['kunden_edit_meldung'], $_SESSION['kunden_edit_eingaben']);

$id = $_GET['id'] ?? '';
$kundeId = is_string($id) ? filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;

if ($kundeId === false) {
    $_SESSION['kunden_meldung'] = ['typ' => 'danger', 'text' => 'Die Anfrage enthält keine gültige Kunden-ID.'];
    header('Location: index.php', true, 303);
    exit;
}

$kunde = null;
$kundeLadefehler = false;
try {
    // Den Datensatz ausschließlich über die positive Primärschlüssel-ID laden.
    $stmt = $pdo->prepare(
        'SELECT id, vorname, nachname, telefon, email
         FROM kunden WHERE id = :id'
    );
    $stmt->execute(['id' => $kundeId]);
    $kunde = $stmt->fetch();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $meldung = ['typ' => 'danger', 'text' => 'Die Kundendaten konnten nicht geladen werden. Bitte später erneut versuchen.'];
    $kundeLadefehler = true;
}

if (!$kundeLadefehler && ($kunde === false || $kunde === null)) {
    $meldung = ['typ' => 'danger', 'text' => 'Kunde nicht gefunden.'];
    $kunde = ['id' => $kundeId, 'vorname' => '', 'nachname' => '', 'telefon' => '', 'email' => ''];
    $eingaben = [];
}

// Bei einem Validierungsfehler werden die eingegebenen Werte statt der DB-Werte angezeigt.
if ($eingaben === []) {
    $eingaben = [
        'vorname' => $kunde['vorname'] ?? '',
        'nachname' => $kunde['nachname'] ?? '',
        'telefon' => $kunde['telefon'] ?? '',
        'email' => $kunde['email'] ?? '',
    ];
}

$seitentitel = 'MotoLog – Kunden bearbeiten';
$aktuelleSeite = 'kunden';
require __DIR__ . '/../templates/header.php';
?>
<main class="container py-4">
    <?php require __DIR__ . '/../templates/message.php'; ?>
    <?php if (!$kundeLadefehler && ($kunde['vorname'] !== '' || $kunde['nachname'] !== '' || $meldung === null)): ?>
        <?php require __DIR__ . '/../templates/kunden/edit_form.php'; ?>
    <?php else: ?>
        <p><a href="index.php" class="btn btn-outline-secondary">Zur Kundenliste</a></p>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../templates/footer.php'; ?>
