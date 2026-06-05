<div class="my-bookings-layout">
    <h1 class="page-title">Twoje Rezerwacje</h1>

    <div class="bookings-grid">
        <?php if (isset($bookings) && is_array($bookings) && count($bookings) > 0): ?>
            <?php foreach ($bookings as $booking): ?>
                <article class="booking-card">
                    <div class="booking-thumb" style="background-image: url('<?= $booking['image_url']; ?>');"></div>

                    <div class="booking-content">
                        <div class="booking-header">
                            <h2><?= htmlspecialchars($booking['room_name']) ?></h2>
                            <span class="status-badge <?= strtolower($booking['status']) ?>">
                                <?= htmlspecialchars($booking['status']) ?>
                            </span>
                        </div>

                        <div class="booking-details">
                            <p><i class="fa-regular fa-calendar"></i> <?= date('d.m.Y', strtotime($booking['start_time'])) ?></p>
                            <p><i class="fa-regular fa-clock"></i> <?= date('H:i', strtotime($booking['start_time'])) ?> - <?= date('H:i', strtotime($booking['end_time'])) ?></p>
                        </div>
                    </div>

                    <div class="booking-price">
                        <span class="price-label">Suma</span>
                        <strong class="price-value">$<?= number_format((float)$booking['total_price'], 2) ?></strong>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-regular fa-calendar-xmark"></i>
                <p>Nie masz jeszcze żadnych rezerwacji.</p>
                <a href="/dashboard?page=book-now" class="btn-proceed">Zarezerwuj pierwszą salę</a>
            </div>
        <?php endif; ?>
    </div>
</div>