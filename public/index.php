<?php
require_once __DIR__ . '/../config/db.php';

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