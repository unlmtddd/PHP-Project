<?php
$seitentitel = $seitentitel ?? 'MotoLog';
$aktuelleSeite = $aktuelleSeite ?? '';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($seitentitel) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary">
<header class="navbar navbar-dark bg-dark">
    <div class="container py-2">
        <a class="navbar-brand fw-bold" href="index.php">MotoLog</a>
        <nav class="d-flex gap-3" aria-label="Hauptnavigation">
            <a class="<?= $aktuelleSeite === 'kunden' ? 'text-white' : 'text-white-50' ?> text-decoration-none"
               href="index.php"<?= $aktuelleSeite === 'kunden' ? ' aria-current="page"' : '' ?>>Kunden</a>
            <a class="<?= $aktuelleSeite === 'fahrzeuge' ? 'text-white' : 'text-white-50' ?> text-decoration-none"
               href="fahrzeuge.php"<?= $aktuelleSeite === 'fahrzeuge' ? ' aria-current="page"' : '' ?>>Fahrzeuge</a>
        </nav>
    </div>
</header>
