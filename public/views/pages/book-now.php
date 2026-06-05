<div class="book-now-layout">

    <div class="rooms-container">
        <h1 class="page-title">Wybór Sali Karaoke</h1>

        <div class="rooms-grid">
            <?php if (isset($rooms) && is_array($rooms) && count($rooms) > 0): ?>
                <?php foreach ($rooms as $room): ?>
                    <article class="room-card" data-room-id="<?= $room["id"]; ?>">
                        <div class="room-image" style="background-image: url('<?= $room['image_url']; ?>');"></div>

                        <div class="room-info">
                            <div class="room-header">
                                <h2><?= $room["name"]; ?></h2>
                                <span class="room-rating">
                                    <i class="fa-solid fa-star" style="color: #fbbf24; margin-right: 4px;"></i>
                                    <?= $room["rating"] ?? '4.5'; ?>
                                </span>
                            </div>

                            <p class="room-desc">
                                <i class="fa-solid fa-users" style="margin-right: 4px;"></i>
                                Do <?= $room["capacity"]; ?> os.
                                <span class="dot-separator">•</span> <?= $room["description"]; ?>
                            </p>

                            <div class="room-footer">
                                <span class="room-price">PLN <?= $room["hourly_rate"]; ?><span class="unit">/hr</span></span>

                                <button class="btn-book" type="button" data-room-id="<?= $room["id"]; ?>">
                                    Rezerwuj Teraz
                                </button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Brak dostępnych sal. Sprawdź bazę danych.</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="booking-menu-section" style="margin-top: 2rem;">
        <h2 class="page-title">Zestaw Startowy (Opcjonalnie)</h2>
        <div class="rooms-grid" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));">
            <?php if(isset($products)): foreach($products as $product): ?>
                <div class="room-card" style="padding: 1rem;">
                    <h3 style="margin: 0 0 0.5rem 0; font-size: 1rem; color:#fff;"><?= htmlspecialchars($product['name']) ?></h3>
                    <p style="margin: 0 0 1rem 0; color: #9ca3af; font-size: 0.8rem;">PLN <?= number_format($product['price'], 2) ?></p>
                    <button type="button" class="btn-book btn-add-cart"
                            data-id="<?= $product['id'] ?>"
                            data-name="<?= htmlspecialchars($product['name']) ?>"
                            data-price="<?= $product['price'] ?>"
                            style="width: 100%; padding: 0.4rem;">Dodaj</button>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
    <aside class="booking-sidebar">
        <form id="booking-form" action="/booking-create" method="POST">

            <section class="sidebar-section">
                <h3>Sesja Rezerwacji</h3>

                <div class="form-group">
                    <label for="room-select">Sala</label>
                    <select class="custom-select" id="room-select" name="room_id">
                        <?php if (isset($rooms)): ?>
                            <?php foreach ($rooms as $room): ?>
                                <option value="<?= $room['id'] ?>"
                                        data-price="<?= $room['hourly_rate'] ?>"
                                        data-capacity="<?= $room['capacity'] ?>">
                                    <?= $room['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="booking-date">Data</label>
                    <input type="date" class="custom-input" id="booking-date" name="booking_date"
                           min="<?= date('Y-m-d'); ?>"
                           value="<?= date('Y-m-d'); ?>" required />
                </div>

                <div class="form-group">
                    <label for="booking-time">Godzina</label>
                    <select class="custom-select" id="booking-time" name="booking_time" required>
                        <?php if (isset($available_hours)): ?>
                            <?php foreach ($available_hours as $dbValue => $displayValue): ?>
                                <option value="<?= $dbValue ?>"><?= $displayValue ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group row-group">
                    <label>Liczba gości</label>
                    <div class="counter-widget">
                        <span class="attendees-text"><span id="display-attendees">2</span> Osób</span>
                        <div class="counter-controls">
                            <button type="button" id="btn-minus-people">−</button>
                            <input type="hidden" id="input-attendees-hidden" name="attendees" value="2">
                            <span id="input-attendees">2</span>
                            <button type="button" id="btn-plus-people">+</button>
                        </div>
                    </div>
                </div>

                <div class="form-group row-group">
                    <label>Czas trwania</label>
                    <div class="counter-widget">
                        <span class="duration-text"><span id="display-duration">2</span> Godzin</span>
                        <div class="counter-controls">
                            <button type="button" id="btn-minus-time">−</button>
                            <input type="hidden" id="input-duration-hidden" name="duration" value="2">
                            <span id="input-duration">2</span>
                            <button type="button" id="btn-plus-time">+</button>
                        </div>
                    </div>
                </div>
            </section>

            <hr class="sidebar-divider" />

            <section class="sidebar-section">
                <h3>Aktualne Zamówienie</h3>
                <ul class="order-list" id="current-order-list">
                    <li class="empty-state" style="color: #9ca3af; font-size: 0.85rem; font-style: italic;">
                        Brak zamówień barowych. Możesz dodać je później w panelu Menu.
                    </li>
                </ul>
            </section>

            <hr class="sidebar-divider" />

            <section class="sidebar-section price-calculator">
                <h3>Kalkulator na żywo</h3>
                <div class="calc-row"><span>Cena za salę:</span> <span id="calc-room-rate">PLN 0.00</span></div>
                <div class="calc-row"><span>Zamówienia z baru:</span> <span id="calc-order-total">PLN 0.00</span></div>
                <div class="calc-row"><span>Podatek (8%):</span> <span id="calc-tax">PLN 0.00</span></div>

                <div class="calc-total">
                    <span>SUMA:</span>
                    <span class="total-amount" id="calc-total">PLN 0.00</span>
                </div>
            </section>

            <button type="submit" class="btn-proceed" id="btn-submit-booking">Przejdź do płatności</button>
        </form>
    </aside>
</div>

<script src="public/scripts/booking.js"></script>