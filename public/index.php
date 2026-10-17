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

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-body-tertiary">

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

    <main class="container py-4">
        <div class="mb-4">
            <h1 class="h2 mb-1">Kundenübersicht</h1>
            <p class="text-body-secondary mb-0">
                Kunden anlegen und Kontaktdaten im Blick behalten.
            </p>
        </div>

        <div class="row g-4">
            <section class="col-12 col-lg-4"
                     aria-labelledby="kundenformular-titel">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h2 id="kundenformular-titel" class="h5 mb-2">
                            Neuen Kunden anlegen
                        </h2>
                        <p class="small text-body-secondary">
                            Mit * markierte Felder sind Pflichtfelder.
                        </p>

                        <form action="add_kunde.php" method="post">
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= htmlspecialchars(
                                    $_SESSION['csrf_token'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

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

                            <button type="submit"
                                    class="btn btn-primary w-100">
                                Kunden speichern
                            </button>
                        </form>
                    </div>
                </div>
            </section>

            <section class="col-12 col-lg-8"
                     aria-labelledby="kundenliste-titel">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between
                                    align-items-center gap-2 mb-3">
                            <h2 id="kundenliste-titel" class="h5 mb-0">
                                Kundenliste
                            </h2>
                            <span class="badge text-bg-secondary">
                                <?= count($kunden) ?> Kunden
                            </span>
                        </div>

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
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <caption>
                                        Gespeicherte Kunden mit Kontaktdaten
                                    </caption>
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">Vorname</th>
                                            <th scope="col">Nachname</th>
                                            <th scope="col">Telefon</th>
                                            <th scope="col">E-Mail</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($kunden as $kunde): ?>
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

    <footer class="container py-4 small text-body-secondary">
        MotoLog · Kunden, Fahrzeuge und Wartungen
    </footer>
</body>
</html>