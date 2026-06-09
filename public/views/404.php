<!DOCTYPE html>
    <html lang="pl">
    <head>
        <?php include __DIR__ . '/partials/head.html'; ?>
        <link rel="stylesheet" type="text/css" href="/public/styles/global.css" />
        <link rel="stylesheet" type="text/css" href="/public/styles/404.css" />
        <title><?= $title ?? 'VocalVibe — 404 Nie Znaleziono'; ?></title>
    </head>
    <body class="error-page">
        <main class="error-container">
            <h1 class="error-code">404</h1>
            <p class="error-text">Ups! Ta scena nie istnieje.</p>
            <a href="/dashboard" class="btn-back">
                <i class="fa-solid fa-house" style="margin-right: 6px;"></i> Wróć do panelu
            </a>
        </main>
    </body>
</html>