<?php
require_once __DIR__ . '/../config/db.php';

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


$stmt = $pdo->query(
    'SELECT id, vorname, nachname, telefon, email
     FROM kunden
     ORDER BY nachname, vorname'
);

$kunden = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MotoLog – Kunden</title>
</head>
<body>
    <h1>Kundenübersicht</h1>
    <h2>Neuen Kunden anlegen</h2>

<form action="add_kunde.php" method="post">
    <input type="hidden" name="csrf_token"
           value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

    <p>
        <label for="vorname">Vorname *</label><br>
        <input type="text" id="vorname" name="vorname"
               maxlength="50" required>
    </p>

    <p>
        <label for="nachname">Nachname *</label><br>
        <input type="text" id="nachname" name="nachname"
               maxlength="50" required>
    </p>

    <p>
        <label for="telefon">Telefon</label><br>
        <input type="tel" id="telefon" name="telefon"
               maxlength="30">
    </p>

    <p>
        <label for="email">E-Mail</label><br>
        <input type="email" id="email" name="email"
               maxlength="100">
    </p>

    <button type="submit">Kunden speichern</button>
</form>

<hr>

    <?php if (empty($kunden)): ?>
        <p>Noch keine Kunden vorhanden.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Vorname</th>
                    <th>Nachname</th>
                    <th>Telefon</th>
                    <th>E-Mail</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kunden as $kunde): ?>
                    <tr>
                        <td><?= htmlspecialchars($kunde['vorname'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($kunde['nachname'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($kunde['telefon'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($kunde['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>