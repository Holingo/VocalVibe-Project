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
                                <h2><?= htmlspecialchars($room["name"]); ?></h2>
                                <span class="room-rating">
                                    <i class="fa-solid fa-star" style="color: #fbbf24; margin-right: 4px;"></i>
                                    <?= htmlspecialchars($room["rating"] ?? '4.5'); ?>
                                </span>
                            </div>

                            <p class="room-desc">
                                <i class="fa-solid fa-users" style="margin-right: 4px;"></i>
                                Do <?= (int)$room["capacity"]; ?> os.
                                <span class="dot-separator">•</span> <?= htmlspecialchars($room["description"]); ?>
                            </p>

                            <div class="room-footer">
                                <span class="room-price">PLN <?= number_format($room["hourly_rate"], 2); ?><span class="unit">/godz.</span></span>

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

    <aside class="booking-sidebar">
        <form id="booking-form" action="/booking-create" method="POST">

            <section class="sidebar-section">
                <h3><i class="fa-solid fa-sliders" style="color: #a855f7;"></i> Konfiguracja sesji</h3>

                <div class="form-group">
                    <label for="room-select">Sala</label>
                    <select class="custom-select" id="room-select" name="room_id">
                        <?php if (isset($rooms)): ?>
                            <?php foreach ($rooms as $room): ?>
                                <option value="<?= $room['id'] ?>"
                                        data-price="<?= $room['hourly_rate'] ?>"
                                        data-capacity="<?= $room['capacity'] ?>">
                                    <?= htmlspecialchars($room['name']) ?>
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
                                <option value="<?= htmlspecialchars($dbValue) ?>"><?= htmlspecialchars($displayValue) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group row-group">
                    <label>Liczba gości</label>
                    <div class="counter-widget">
                        <span class="attendees-text"></span>Osób</span>
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
                        <span class="duration-text"></span>Godzin</span>
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
                <h3><i class="fa-solid fa-receipt" style="color: #a855f7;"></i> Aktualne Zamówienie</h3>
                <ul class="order-list" id="current-order-list">
                    <li class="empty-state" style="color: #9ca3af; font-size: 0.85rem; font-style: italic;">
                        Brak zamówień barowych. Możesz dodać je później w panelu Menu.
                    </li>
                </ul>

                <button type="button" id="btn-open-menu-modal" class="btn-proceed" style="margin-top: 1.25rem; background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%); padding: 0.65rem; font-size: 0.9rem;">
                    <i class="fa-solid fa-martini-glass-citrus" style="margin-right: 8px;"></i> Dodaj z menu
                </button>
            </section>

            <hr class="sidebar-divider" />

            <section class="sidebar-section price-calculator">
                <h3>Kalkulator cen</h3>
                <div class="calc-row"><span>Cena za salę:</span> <span id="calc-room-rate">PLN 0.00</span></div>
                <div class="calc-row"><span>Zamówienia z baru:</span> <span id="calc-order-total">PLN 0.00</span></div>
                <div class="calc-row"><span>Podatek (8%):</span> <span id="calc-tax">PLN 0.00</span></div>

                <div class="calc-total">
                    <span>SUMA:</span>
                    <span class="total-amount" id="calc-total">PLN 0.00</span>
                </div>
            </section>

            <div id="hidden-products" style="display: none;"></div>

            <button type="submit" class="btn-proceed" id="btn-submit-booking">Przejdź do płatności</button>
        </form>
    </aside>
</div>

<div id="menu-modal" class="modal-overlay hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Dodaj przekąski i napoje do rezerwacji</h2>
            <button type="button" id="btn-close-modal" class="btn-close">&times;</button>
        </div>

        <div class="modal-body modal-products-grid">
            <?php if(isset($products) && is_array($products)): ?>
                <?php foreach($products as $product): ?>
                    <div class="room-card" style="padding: 1rem; background: rgba(19, 18, 26, 0.6);">
                        <?php if(!empty($product['image_url'])): ?>
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" style="width:100%; height:120px; object-fit:cover; border-radius: 10px; margin-bottom: 0.75rem;">
                        <?php endif; ?>
                        <h3 style="margin: 0 0 0.4rem 0; font-size: 1rem; color:#fff; text-align: center;"><?= htmlspecialchars($product['name']) ?></h3>
                        <p style="margin: 0 0 1rem 0; color: #9ca3af; font-size: 0.85rem; text-align: center; line-height: 1.3; flex-grow: 1;"><?= htmlspecialchars($product['description'] ?? '') ?></p>

                        <div class="modal-counter-widget">
                            <button type="button" class="btn-cart-minus modal-counter-btn"
                                    data-id="<?= $product['id'] ?>"
                                    data-name="<?= htmlspecialchars($product['name']) ?>"
                                    data-price="<?= $product['price'] ?>">-</button>

                            <span class="cart-item-qty modal-counter-qty" id="modal-qty-<?= $product['id'] ?>">0</span>

                            <button type="button" class="btn-cart-plus modal-counter-btn"
                                    data-id="<?= $product['id'] ?>"
                                    data-name="<?= htmlspecialchars($product['name']) ?>"
                                    data-price="<?= $product['price'] ?>">+</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="grid-column: 1/-1; text-align: center; color: var(--text-muted);">Brak dostępnych produktów w menu.</p>
            <?php endif; ?>
        </div>
    </div>
</div>