<?php
// Datenbankverbindung laden; danach steht das PDO-Objekt $pdo bereit.
require_once __DIR__ . '/../config/db.php';

// Sitzung öffnen, damit der CSRF-Token zwischen Anfragen erhalten bleibt.
session_start();

// Pro Sitzung einen zufälligen Token für die Formularprüfung erstellen.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Kunden alphabetisch nach Nachname und Vorname laden.
// Die Abfrage enthält keine Benutzereingaben und benötigt keine Platzhalter.
$stmt = $pdo->query(
    'SELECT id, vorname, nachname, telefon, email
     FROM kunden
     ORDER BY nachname, vorname'
);

// Alle Ergebniszeilen als Array speichern.
$kunden = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">

    <!-- Sorgt dafür, dass das Layout auf mobilen Geräten korrekt skaliert. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>MotoLog – Kunden</title>

    <!-- Bootstrap stellt fertige CSS-Klassen bereit.
         Zum Laden dieser Datei wird eine Internetverbindung benötigt. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<!-- Heller Hintergrund für einen sichtbaren Kontrast zu den Karten. -->
<body class="bg-body-tertiary">

    <!-- Kopfbereich mit Projektname und Bezeichnung der aktuellen Seite. -->
    <header class="navbar navbar-dark bg-dark">
        <div class="container py-2">
            <a class="navbar-brand fw-bold" href="index.php">
                MotoLog
            </a>

            <span class="navbar-text">
                Kundenverwaltung
            </span>
        </div>
    </header>

    <!-- container zentriert den Inhalt und begrenzt seine Breite.
         py-4 erzeugt oben und unten Innenabstand. -->
    <main class="container py-4">
        <div class="mb-4">
            <h1 class="h2 mb-1">Kundenübersicht</h1>

            <p class="text-body-secondary mb-0">
                Kunden anlegen und Kontaktdaten im Blick behalten.
            </p>
        </div>

        <!-- Bootstrap verwendet ein Raster mit 12 Spalten.
             g-4 sorgt für Abstand zwischen Formular und Kundenliste. -->
        <div class="row g-4">

            <!-- Formular: volle Breite auf kleinen Bildschirmen,
                 ab lg (992 px) werden 4 von 12 Spalten verwendet.
                 aria-labelledby verbindet den Bereich mit seinem Titel. -->
            <section
                class="col-12 col-lg-4"
                aria-labelledby="kundenformular-titel"
            >
                <!-- Karte ohne Rahmen mit leichtem Schatten. -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h2 id="kundenformular-titel" class="h5 mb-2">
                            Neuen Kunden anlegen
                        </h2>

                        <p class="small text-body-secondary">
                            Mit * markierte Felder sind Pflichtfelder.
                        </p>

                        <!-- POST sendet die Eingaben an den PHP-Handler.
                             Das action-Ziel muss dem Dateinamen entsprechen. -->
                        <form action="add_kunde.php" method="post">

                            <!-- Der Handler vergleicht diesen versteckten Token
                                 mit dem Token aus der Sitzung. -->
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= htmlspecialchars(
                                    $_SESSION['csrf_token'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                            <!-- for und id verbinden Beschriftung und Feld.
                                 name bestimmt den Schlüssel in $_POST.
                                 form-control gestaltet das Eingabefeld. -->
                            <div class="mb-3">
                                <label for="vorname" class="form-label">
                                    Vorname *
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="vorname"
                                    name="vorname"
                                    autocomplete="given-name"
                                    maxlength="50"
                                    required
                                >
                            </div>

                            <!-- required und maxlength prüfen im Browser.
                                 Der Handler prüft die Werte zusätzlich auf dem Server. -->
                            <div class="mb-3">
                                <label for="nachname" class="form-label">
                                    Nachname *
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="nachname"
                                    name="nachname"
                                    autocomplete="family-name"
                                    maxlength="50"
                                    required
                                >
                            </div>

                            <!-- Telefonnummer ist optional.
                                 type="tel" bietet auf Mobilgeräten eine passende Tastatur,
                                 prüft aber nicht automatisch das Nummernformat. -->
                            <div class="mb-3">
                                <label for="telefon" class="form-label">
                                    Telefon
                                </label>

                                <input
                                    type="tel"
                                    class="form-control"
                                    id="telefon"
                                    name="telefon"
                                    autocomplete="tel"
                                    maxlength="30"
                                >
                            </div>

                            <!-- E-Mail ist optional. Bei einer Eingabe prüft
                                 der Browser das Format; PHP prüft es ebenfalls. -->
                            <div class="mb-4">
                                <label for="email" class="form-label">
                                    E-Mail
                                </label>

                                <input
                                    type="email"
                                    class="form-control"
                                    id="email"
                                    name="email"
                                    autocomplete="email"
                                    maxlength="100"
                                >
                            </div>

                            <!-- submit sendet die Formulardaten.
                                 w-100 setzt die Breite auf 100 Prozent. -->
                            <button
                                type="submit"
                                class="btn btn-primary w-100"
                            >
                                Kunden speichern
                            </button>
                        </form>
                    </div>
                </div>
            </section>

            <!-- Kundenliste: ab lg 8 von 12 Spalten.
                 Auf kleineren Bildschirmen steht sie unter dem Formular. -->
            <section
                class="col-12 col-lg-8"
                aria-labelledby="kundenliste-titel"
            >
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">

                        <!-- Flexbox ordnet Titel und Kundenanzahl nebeneinander an. -->
                        <div class="d-flex justify-content-between
                                    align-items-center gap-2 mb-3">
                            <h2 id="kundenliste-titel" class="h5 mb-0">
                                Kundenliste
                            </h2>

                            <!-- Anzahl der aktuell geladenen Datensätze. -->
                            <span class="badge text-bg-secondary">
                                <?= count($kunden) ?> Kunden
                            </span>
                        </div>

                        <!-- Bei einer leeren Liste einen hilfreichen Hinweis zeigen. -->
                        <?php if (empty($kunden)): ?>
                            <div class="text-center py-5">
                                <p class="fw-semibold mb-1">
                                    Noch keine Kunden vorhanden.
                                </p>

                                <p class="text-body-secondary mb-0">
                                    Legen Sie Ihren ersten Kunden
                                    über das Formular an.
                                </p>
                            </div>
                        <?php else: ?>

                            <!-- Ermöglicht horizontales Scrollen bei breiten Tabellen. -->
                            <div class="table-responsive">

                                <!-- table-hover hebt Zeilen beim Darüberfahren hervor.
                                     align-middle richtet Zellen vertikal mittig aus. -->
                                <table class="table table-hover align-middle">
                                    <caption>
                                        Gespeicherte Kunden mit Kontaktdaten
                                    </caption>

                                    <thead class="table-light">
                                        <tr>
                                            <!-- scope kennzeichnet Spaltenüberschriften. -->
                                            <th scope="col">Vorname</th>
                                            <th scope="col">Nachname</th>
                                            <th scope="col">Telefon</th>
                                            <th scope="col">E-Mail</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        // Pro Kunde eine Tabellenzeile ausgeben.
                                        // htmlspecialchars gibt Sonderzeichen als Text aus
                                        // und verhindert hier die Ausführung von HTML.
                                        // NULL-Kontaktdaten werden als Gedankenstrich angezeigt.
                                        foreach ($kunden as $kunde):
                                        ?>
                                            <tr>
                                                <td>
                                                    <?= htmlspecialchars(
                                                        $kunde['vorname'],
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>
                                                </td>

                                                <td>
                                                    <?= htmlspecialchars(
                                                        $kunde['nachname'],
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>
                                                </td>

                                                <td>
                                                    <?= htmlspecialchars(
                                                        $kunde['telefon'] ?? '—',
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>
                                                </td>

                                                <td>
                                                    <?= htmlspecialchars(
                                                        $kunde['email'] ?? '—',
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Fußbereich mit einer kurzen Beschreibung des Projekts. -->
    <footer class="container py-4 small text-body-secondary">
        MotoLog · Kunden, Fahrzeuge und Wartungen
    </footer>
</body>
</html>