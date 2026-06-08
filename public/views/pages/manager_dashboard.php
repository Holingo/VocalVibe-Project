<?php
/**
 * Panel Managera — VocalVibe
 * Umieść ten plik w: src/views/manager_dashboard.php
 *
 * Zmienne dostępne z kontrolera:
 *   $rooms          — tablica wszystkich pokoi
 *   $activeBookings — tablica aktywnych rezerwacji (z user_email, room_name, order_items JSON)
 *   $bookingsByRoom — tablica rezerwacji indeksowana room_id
 *   $stats          — ['total', 'occupied', 'free', 'revenue']
 */
$serverDate = date('l, d F Y');
$serverTime = date('H:i:s');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VocalVibe — Panel Managera</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Russo+One&family=IBM+Plex+Mono:wght@400;500;600&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/public/styles/pages/manager_dashboard.css">
</head>
<body>

<div class="grid-bg" aria-hidden="true"></div>

<!-- ─── TOP BAR ─────────────────────────────────────────────── -->
<header class="top-bar">
    <div class="top-bar__brand">
        <span class="brand-glyph" aria-hidden="true">◈</span>
        <div>
            <span class="brand-name">VOCALVIBE</span>
            <span class="brand-role">PANEL MANAGERA</span>
        </div>
    </div>

    <div class="top-bar__clock">
        <div class="clock-time" id="live-clock"><?= $serverTime ?></div>
        <div class="clock-date" id="live-date"><?= $serverDate ?></div>
    </div>

    <div class="top-bar__actions">
        <div class="refresh-pill" title="Automatyczne odświeżenie">
            <span class="refresh-dot"></span>
            <span>Odświeżenie za&nbsp;</span><span id="refresh-countdown">60</span><span>s</span>
        </div>
        <button class="btn-action btn-refresh" onclick="forceRefresh()">&#8635; Odśwież</button>
        <a href="/logout" class="btn-action btn-logout">Wyloguj</a>
    </div>
</header>

<main class="mgr-main">

    <!-- ─── STATS BAR ──────────────────────────────────────── -->
    <section class="stats-bar" aria-label="Statystyki">
        <div class="stat-card">
            <span class="stat-val"><?= (int)$stats['total'] ?></span>
            <span class="stat-lbl">Wszystkie pokoje</span>
        </div>
        <div class="stat-card stat-card--occ">
            <span class="stat-val"><?= (int)$stats['occupied'] ?></span>
            <span class="stat-lbl">Zajęte</span>
        </div>
        <div class="stat-card stat-card--free">
            <span class="stat-val"><?= (int)$stats['free'] ?></span>
            <span class="stat-lbl">Wolne</span>
        </div>
        <div class="stat-card stat-card--rev">
            <span class="stat-val"><?= number_format((float)$stats['revenue'], 2, ',', '&nbsp;') ?>&nbsp;zł</span>
            <span class="stat-lbl">Aktywne rezerwacje łącznie</span>
        </div>
    </section>

    <!-- ─── CONTROLS ───────────────────────────────────────── -->
    <div class="controls-bar">
        <div class="filter-tabs" role="tablist" aria-label="Filtruj pokoje">
            <button class="ftab ftab--active" data-filter="all"      role="tab" aria-selected="true">Wszystkie</button>
            <button class="ftab"               data-filter="occupied" role="tab" aria-selected="false">Zajęte</button>
            <button class="ftab"               data-filter="free"     role="tab" aria-selected="false">Wolne</button>
        </div>
        <label class="search-wrap" aria-label="Szukaj pokoju lub gościa">
            <span class="search-icon" aria-hidden="true">&#9906;</span>
            <input type="search" id="search-input" placeholder="Szukaj pokoju lub adresu e-mail…" autocomplete="off">
        </label>
    </div>

    <!-- ─── ROOMS GRID ─────────────────────────────────────── -->
    <section class="rooms-grid" id="rooms-grid" aria-label="Pokoje">

        <?php foreach ($rooms as $room): ?>
            <?php
            $rid        = (int)$room['id'];
            $booking    = $bookingsByRoom[$rid] ?? null;
            $isOccupied = ($booking !== null);
            $statusKey  = $isOccupied ? 'occupied' : 'free';

            // Parse products JSON from json_agg()
            $products = [];
            if ($booking && !empty($booking['order_items'])) {
                $decoded = json_decode($booking['order_items'], true);
                if (is_array($decoded)) {
                    $products = array_filter($decoded, fn($p) => isset($p['name']));
                }
            }

            // Formatted session times
            $tStart = $booking ? date('H:i', strtotime($booking['start_time'])) : null;
            $tEnd   = $booking ? date('H:i', strtotime($booking['end_time']))   : null;
            $tDate  = $booking ? date('d.m.Y', strtotime($booking['start_time'])) : null;

            // Duration in hours
            $durH = null;
            if ($booking) {
                $durH = round((strtotime($booking['end_time']) - strtotime($booking['start_time'])) / 3600);
            }
            ?>
            <article
                class="room-card room-card--<?= $statusKey ?>"
                data-status="<?= $statusKey ?>"
                data-room-name="<?= strtolower(htmlspecialchars($room['name'])) ?>"
                data-guest="<?= $booking ? strtolower(htmlspecialchars($booking['user_email'])) : '' ?>"
                aria-label="Pokój <?= htmlspecialchars($room['name']) ?>, <?= $isOccupied ? 'zajęty' : 'wolny' ?>"
            >
                <div class="room-card__stripe" aria-hidden="true"></div>

                <header class="room-card__head">
                    <div class="room-ident">
                        <span class="room-num">SALA <?= str_pad($rid, 2, '0', STR_PAD_LEFT) ?></span>
                        <h2 class="room-title"><?= htmlspecialchars($room['name']) ?></h2>
                    </div>
                    <span class="status-badge status-badge--<?= $statusKey ?>">
                        <?= $isOccupied ? '● ZAJĘTE' : '○ WOLNE' ?>
                    </span>
                </header>

                <div class="room-meta">
                    <span class="room-meta__tag">&#128101; <?= (int)$room['capacity'] ?> os.</span>
                    <span class="room-meta__tag">&#128176; <?= number_format((float)$room['hourly_rate'], 2, ',', '.') ?> zł/h</span>
                </div>

                <?php if ($isOccupied && $booking): ?>
                    <!-- BOOKING DETAILS -->
                    <div class="booking-block">
                        <div class="binfo-grid">
                            <div class="bfield">
                                <span class="bfield__label">Gość</span>
                                <span class="bfield__val bfield__val--email" title="<?= htmlspecialchars($booking['user_email']) ?>">
                                <?= htmlspecialchars($booking['user_email']) ?>
                            </span>
                            </div>
                            <div class="bfield">
                                <span class="bfield__label">Sesja</span>
                                <span class="bfield__val">
                                <?= $tStart ?>&nbsp;&#8594;&nbsp;<?= $tEnd ?>
                                <span class="dur-chip"><?= $durH ?>h</span>
                                <br><small class="bdate"><?= $tDate ?></small>
                            </span>
                            </div>
                            <div class="bfield">
                                <span class="bfield__label">Liczba gości</span>
                                <span class="bfield__val"><?= isset($booking['attendees']) ? (int)$booking['attendees'] : '—' ?></span>
                            </div>
                            <div class="bfield">
                                <span class="bfield__label">Kwota (z VAT)</span>
                                <span class="bfield__val bfield__val--price">
                                <?= number_format((float)$booking['total_price'], 2, ',', '&nbsp;') ?>&nbsp;zł
                            </span>
                            </div>
                        </div>

                        <?php if (!empty($products)): ?>
                            <div class="bar-order">
                                <span class="bar-order__label">&#127867; Zamówienie barowe</span>
                                <ul class="bar-order__list">
                                    <?php foreach ($products as $p): ?>
                                        <li class="bar-item">
                                            <span class="bar-item__name"><?= htmlspecialchars($p['name']) ?></span>
                                            <span class="bar-item__qty">&#215;<?= (int)$p['quantity'] ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php else: ?>
                            <div class="bar-order bar-order--empty">
                                <span>Brak zamówień barowych</span>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <!-- FREE STATE -->
                    <div class="free-state">
                        <span class="free-pulse" aria-hidden="true"></span>
                        Pokój dostępny do rezerwacji
                    </div>
                <?php endif; ?>

            </article>
        <?php endforeach; ?>

    </section>

    <!-- ─── BOOKINGS TABLE ─────────────────────────────────── -->
    <section class="table-section" aria-label="Wszystkie aktywne rezerwacje">
        <div class="table-section__head">
            <h2 class="section-title">
                Aktywne rezerwacje
                <span class="count-badge"><?= (int)$stats['occupied'] ?></span>
            </h2>
        </div>

        <?php if (empty($activeBookings)): ?>
            <div class="empty-state">
                <span class="empty-glyph" aria-hidden="true">&#9678;</span>
                <p>Brak aktywnych rezerwacji</p>
            </div>

        <?php else: ?>
            <div class="table-scroll">
                <table class="bookings-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pokój</th>
                        <th>Gość</th>
                        <th>Czas sesji</th>
                        <th>Goście</th>
                        <th>Bar</th>
                        <th>Kwota</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($activeBookings as $b): ?>
                        <?php
                        $bp = [];
                        if (!empty($b['order_items'])) {
                            $bDec = json_decode($b['order_items'], true);
                            if (is_array($bDec)) {
                                $bp = array_filter($bDec, fn($p) => isset($p['name']));
                            }
                        }
                        ?>
                        <tr>
                            <td class="td-id">#<?= str_pad((int)$b['id'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td class="td-room"><?= htmlspecialchars($b['room_name']) ?></td>
                            <td class="td-email" title="<?= htmlspecialchars($b['user_email']) ?>">
                                <?= htmlspecialchars($b['user_email']) ?>
                            </td>
                            <td class="td-time">
                            <span class="t-range">
                                <?= date('H:i', strtotime($b['start_time'])) ?>
                                &#8594;
                                <?= date('H:i', strtotime($b['end_time'])) ?>
                            </span>
                                <span class="t-date"><?= date('d.m.Y', strtotime($b['start_time'])) ?></span>
                            </td>
                            <td class="td-att"><?= isset($b['attendees']) ? (int)$b['attendees'] : '—' ?></td>
                            <td class="td-products">
                                <?php if (!empty($bp)): ?>
                                    <div class="ppills">
                                        <?php foreach ($bp as $p): ?>
                                            <span class="ppill"><?= htmlspecialchars($p['name']) ?>&nbsp;&#215;<?= (int)$p['quantity'] ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="td-none">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="td-price"><?= number_format((float)$b['total_price'], 2, ',', '&nbsp;') ?>&nbsp;zł</td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

</main>

<script src="/public/scripts/manager_dashboard.js"></script>
</body>
</html>