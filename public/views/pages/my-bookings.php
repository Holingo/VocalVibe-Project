<div class="my-bookings-container">
    <h1 class="page-title">Moje Rezerwacje Karaoke</h1>

    <?php if (isset($bookings) && is_array($bookings) && count($bookings) > 0): ?>
        <div class="bookings-list">
            <?php foreach ($bookings as $booking):
                // Bezpieczne parsowanie czasu rozpoczęcia sesji
                $bookingStartStr = ($booking['booking_date'] ?? date('Y-m-d')) . ' ' . $booking['start_time'];
                $bookingTimestamp = strtotime($bookingStartStr);
                $currentTimestamp = time();
                $canCancel = ($bookingTimestamp > $currentTimestamp);
                ?>
                <div class="booking-card" id="booking-card-<?= $booking['id'] ?>">
                    <div class="booking-main-info">
                        <div class="booking-header-details">
                            <h2>Sala: <?= htmlspecialchars($booking['room_name']) ?></h2>
                            <p class="booking-date-time">
                                <i class="fa-solid fa-calendar-days"></i> <?= htmlspecialchars($booking['booking_date'] ?? '') ?>
                                <span class="dot-separator">•</span>
                                <i class="fa-solid fa-clock"></i> <?= date('H:i', strtotime($booking['start_time'])) ?> - <?= date('H:i', strtotime($booking['end_time'])) ?>
                            </p>
                        </div>

                        <div class="booking-status-badge <?= $canCancel ? 'status-upcoming' : 'status-past' ?>">
                            <?= $canCancel ? 'Nadchodząca' : 'Zakończona / W trakcie' ?>
                        </div>
                    </div>

                    <div class="booking-extra-details">
                        <div class="details-grid">
                            <div class="detail-item">
                                <span class="detail-label">Liczba gości:</span>
                                <span class="detail-value"><?= htmlspecialchars($booking['attendees'] ?? '2') ?> os.</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Koszt sali:</span>
                                <span class="detail-value">PLN <?= number_format($booking['total_price'] ?? 0, 2) ?></span>
                            </div>
                        </div>

                        <div class="booking-products-section">
                            <h3><i class="fa-solid fa-martini-glass-citrus"></i> Zamówienia barowe do tej sesji:</h3>
                            <?php if (isset($booking['products']) && is_array($booking['products']) && count($booking['products']) > 0): ?>
                                <ul class="booking-products-list">
                                    <?php
                                    $barTotal = 0;
                                    foreach ($booking['products'] as $product):
                                        // NAPRAWIONE: Używamy klucza 'total' wygenerowanego przez SQL oraz 'unit_price' zamiast 'price'
                                        $productTotal = $product['total'] ?? (($product['unit_price'] ?? 0) * ($product['quantity'] ?? 1));
                                        $barTotal += $productTotal;
                                        ?>
                                        <li>
                                            <span class="p-qty"><?= $product['quantity'] ?>x</span>
                                            <span class="p-name"><?= htmlspecialchars($product['name']) ?></span>
                                            <span class="p-price">PLN <?= number_format($productTotal, 2) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <div class="bar-summary">
                                    <span>Suma bar:</span>
                                    <strong>PLN <?= number_format($barTotal, 2) ?></strong>
                                </div>
                            <?php else: ?>
                                <p class="no-products-text">Brak zamówionych produktów z baru do tej rezerwacji.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($canCancel): ?>
                        <div class="booking-actions">
                            <button type="button" class="btn-cancel-booking" data-booking-id="<?= $booking['id'] ?>">
                                <i class="fa-solid fa-trash-can"></i> Anuluj Rezerwację
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fa-solid fa-microphone-lines" style="font-size: 3rem; margin-bottom: 1rem; color: #a855f7;"></i>
            <h2>Nie masz jeszcze żadnych rezerwacji</h2>
            <p>Zarezerwuj swoją pierwszą salę karaoke już teraz i rozkręć imprezę!</p>
            <a href="/book-now" class="btn-book" style="display: inline-block; text-decoration: none; margin-top: 1rem;">Zarezerwuj Salę</a>
        </div>
    <?php endif; ?>
</div>

<script src="public/scripts/my-bookings.js"></script>