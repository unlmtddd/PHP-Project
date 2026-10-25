<?php
require_once __DIR__ . '/../includes/icons.php';

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
    <link href="assets/css/style.css?v=6" rel="stylesheet">
</head>
<body class="app-body">
<header class="site-header">
    <div class="container site-header-inner">
        <a class="site-brand navbar-brand fw-bold mb-0" href="index.php">
            <?= motologIcon('motorcycle', 'icon icon-brand') ?>
            <span>MotoLog</span>
        </a>
        <nav class="site-nav" aria-label="Hauptnavigation">
            <a
                class="site-nav-link text-decoration-none <?= $aktuelleSeite === 'kunden' ? 'is-active' : '' ?>"
                href="index.php"<?= $aktuelleSeite === 'kunden' ? ' aria-current="page"' : '' ?>
            ><?= motologIcon('users', 'icon icon-nav') ?><span>Kunden</span></a>
            <a
                class="site-nav-link text-decoration-none <?= $aktuelleSeite === 'fahrzeuge' ? 'is-active' : '' ?>"
                href="fahrzeuge.php"<?= $aktuelleSeite === 'fahrzeuge' ? ' aria-current="page"' : '' ?>
            ><?= motologIcon('motorcycle', 'icon icon-nav icon-motorbike') ?><span>Fahrzeuge</span></a>
            <a
                class="site-nav-link text-decoration-none <?= $aktuelleSeite === 'wartungen' ? 'is-active' : '' ?>"
                href="wartungen.php"<?= $aktuelleSeite === 'wartungen' ? ' aria-current="page"' : '' ?>
            ><?= motologIcon('wrench', 'icon icon-nav') ?><span>Wartungen</span></a>
        </nav>
    </div>
</header>
