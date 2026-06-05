<?php
    if (session_status() === PHP_SESSION_NONE) session_start();
    $userName = $_SESSION['user_name'] ?? 'Użytkownik';
?>

<nav class="dashboard-topbar">
    <div class="nav-brand">
        <div class="brand-logo">
            <i class="fa-solid fa-microphone-lines"></i>
        </div>
        <span class="brand-name">VocalVibe</span>
    </div>

    <div class="nav-user-actions">
        <div class="user-profile">
            <img src="public/img/avatars/default.jpg" alt="User Avatar" class="avatar-img" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($userName) ?>&background=2a2836&color=fff'">
            <span class="user-name"><?= htmlspecialchars($userName) ?></span>
        </div>

        <div class="nav-divider"></div>

        <a href="/logout" class="icon-btn logout-btn" title="Wyloguj się">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
        </a>
    </div>
</nav>