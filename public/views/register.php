<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VocalVibe — Rejestracja</title>
    <link rel="stylesheet" type="text/css" href="/public/styles/global.css" />
    <link rel="stylesheet" type="text/css" href="/public/styles/auth.css" />
    <script type="text/javascript" src="/public/scripts/validate.js" defer></script>
</head>
<body class="auth-page">

<main class="auth-shell">
    <div class="auth-form-container">

        <header class="auth-heading">
            <h1>Dołącz do nas</h1>
            <p class="auth-subtitle">Zarejestruj się i rezerwuj sale w kilka sekund</p>
        </header>

        <form class="register-form" action="register" method="POST">

            <?php if (isset($messages) && !empty($messages)): ?>
                <div class="messages">
                    <?php
                    $messageList = is_array($messages) ? $messages : [$messages];
                    foreach ($messageList as $msg):
                        ?>
                        <span><?= htmlspecialchars($msg) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="form-grid">
                <div class="input-group">
                    <label for="name">Imię</label>
                    <input name="name" id="name" type="text" placeholder="Jan" required>
                </div>
                <div class="input-group">
                    <label for="surname">Nazwisko</label>
                    <input name="surname" id="surname" type="text" placeholder="Kowalski" required>
                </div>
            </div>

            <!-- <div class="input-group">
                <label for="phone">Numer telefonu</label>
                <input name="phone" id="phone" type="tel" placeholder="+48 000 000 000">
            </div> -->

            <div class="input-group">
                <label for="email">Adres E-mail</label>
                <input name="email" id="email" type="email" placeholder="twoj@email.com" required>
            </div>

            <div class="input-group">
                <label for="password">Hasło</label>
                <input name="password" id="password" type="password" placeholder="Min. 8 znaków" required>
            </div>

            <div class="input-group">
                <label for="confirmedPassword">Powtórz hasło</label>
                <input name="confirmedPassword" id="confirmedPassword" type="password" placeholder="Potwierdź hasło" required>
            </div>

            <button type="submit" class="btn-submit">Utwórz konto</button>

        </form>

        <footer class="auth-footer">
            Masz już konto? <a href="/login" style="color: var(--color-primary); font-weight: 700;">Zaloguj się</a>
        </footer>

    </div>
</main>

</body>
</html>