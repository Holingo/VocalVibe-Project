<?php
$allowedPages = ['book-now', 'my-bookings', 'menu', 'loyalty', 'support'];
$page = $_GET['page'] ?? 'book-now';

if (!in_array($page, $allowedPages)) {
    $page = 'book-now';
}
?>
<!doctype html>
<html lang="pl">
<head>
    <?php include 'public/views/partials/head.html'; ?>
    <link rel="stylesheet" type="text/css" href="public/styles/dashboard.css" />
    <link rel="stylesheet" type="text/css" href="public/styles/pages/book-now.css" />
    <link rel="stylesheet" type="text/css" href="public/styles/pages/my-bookings.css" />
    <link rel="stylesheet" type="text/css" href="public/styles/pages/menu.css" />

    <?php if ($page === 'menu'): ?>
        <script src="public/scripts/menu.js" defer></script>
    <?php elseif ($page === 'book-now'): ?>
        <script src="public/scripts/book-now.js" defer></script>
    <?php elseif ($page === 'my-bookings'): ?>
        <script src="public/scripts/my-bookings.js" defer></script>
    <?php endif; ?>

    <title>VocalVibe - Panel Klienta</title>
</head>
<body class="dashboard-page">

<div class="dashboard-shell">

    <?php include 'public/views/partials/sidebar.php'; ?>

    <div class="dashboard-viewport">

        <?php include 'public/views/partials/nav.php'; ?>

        <main class="dashboard-content">
            <?php include 'public/views/pages/' . $page . '.php'; ?>
        </main>

    </div>
</div>

</body>
</html>