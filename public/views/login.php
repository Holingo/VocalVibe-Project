<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VocalVibe — Logowanie</title>
  <link rel="stylesheet" type="text/css" href="/public/styles/global.css" />
  <link rel="stylesheet" type="text/css" href="/public/styles/auth.css" />
  <script type="text/javascript" src="/public/scripts/validate.js" defer></script>
</head>
<body class="auth-page">

<main class="auth-shell">
  <div class="auth-form-container">

    <header class="auth-heading">
      <h1>Witaj ponownie</h1>
      <p class="auth-subtitle">Zaloguj się, aby zarządzać swoimi rezerwacjami</p>
    </header>

    <form class="login-form" action="login" method="POST">

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

      <div class="input-group">
        <label for="email">Adres E-mail</label>
        <input name="email" id="email" type="email" placeholder="twoj@email.com" required>
      </div>

      <div class="input-group">
        <label for="password">Hasło</label>
        <input name="password" id="password" type="password" placeholder="Wpisz hasło" required>
      </div>

      <button type="submit" class="btn-submit">Zaloguj się</button>

    </form>

    <footer class="auth-footer">
      Nie masz jeszcze konta? <a href="/register" style="color: var(--color-primary); font-weight: 700;">Zarejestruj się</a>
    </footer>

  </div>
</main>

</body>
</html>