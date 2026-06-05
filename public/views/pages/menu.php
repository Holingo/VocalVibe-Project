<div class="book-now-layout"> <div class="rooms-container">
        <?php if (isset($no_booking)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-ban" style="font-size: 3rem; margin-bottom: 1rem; color: #f43f5e;"></i>
                <h2>Brak aktywnej rezerwacji</h2>
                <p>Aby móc zamawiać z baru w trakcie imprezy, musisz najpierw posiadać salę.</p>
                <a href="?page=book-now" class="btn-book" style="display: inline-block; text-decoration: none; margin-top: 1rem;">Rezerwuj teraz</a>
            </div>
        <?php else: ?>
            <h1 class="page-title">Oferta Baru</h1>

            <?php
            $groupedProducts = [];
            foreach ($products as $product) {
                $groupedProducts[$product['category']][] = $product;
            }
            foreach ($groupedProducts as $category => $items): ?>
                <section class="menu-category" style="margin-bottom: 2rem;">
                    <h2 style="color: #fff; margin-bottom: 1rem; border-bottom: 1px solid #2a2836; padding-bottom: 0.5rem;"><?= htmlspecialchars($category) ?></h2>
                    <div class="rooms-grid" style="grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));">
                        <?php foreach ($items as $product): ?>
                            <div class="room-card">
                                <img src="<?= htmlspecialchars($product['image_url']) ?>" style="width:100%; height:120px; object-fit:cover;" onerror="this.src='public/img/products/default.jpg'">
                                <div class="room-info" style="padding: 1rem;">
                                    <h3 style="margin: 0 0 0.5rem 0; font-size: 1rem; color:#fff;"><?= htmlspecialchars($product['name']) ?></h3>
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                                        <span class="room-price" style="font-size: 1rem;">PLN <?= number_format($product['price'], 2) ?></span>
                                        <button class="btn-book btn-add"
                                                data-product-id="<?= $product['id'] ?>"
                                                data-booking-id="<?= htmlspecialchars($currentBooking['id']) ?>"
                                                style="padding: 0.4rem 0.8rem;">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if (!isset($no_booking)): ?>
        <aside class="booking-sidebar">
            <section class="sidebar-section">
                <h3><i class="fa-solid fa-door-open" style="color: #d946ef;"></i> Twoja Rezerwacja</h3>
                <p style="color: #9ca3af; font-size: 0.9rem; margin-top: 0.5rem;">
                    Sala: <strong style="color: #fff;"><?= htmlspecialchars($currentBooking['room_name']) ?></strong><br>
                    Czas: <?= date('H:i', strtotime($currentBooking['start_time'])) ?> - <?= date('H:i', strtotime($currentBooking['end_time'])) ?>
                </p>
            </section>

            <hr class="sidebar-divider" />

            <section class="sidebar-section">
                <h3><i class="fa-solid fa-receipt" style="color: #d946ef;"></i> Zamówiono do Sali</h3>
                <ul class="order-list">
                    <?php
                    $total = 0;
                    if(isset($orderSummary) && count($orderSummary) > 0):
                        foreach($orderSummary as $item):
                            $total += $item['total'];
                            ?>
                            <li>
                                <span class="qty"><?= $item['quantity'] ?>x</span>
                                <span class="name"><?= htmlspecialchars($item['name']) ?></span>
                                <span class="price">PLN <?= number_format($item['total'], 2) ?></span>
                            </li>
                        <?php endforeach; else: ?>
                        <li class="empty-state" style="color: #9ca3af; font-size: 0.85rem; font-style: italic;">Brak zamówień barowych.</li>
                    <?php endif; ?>
                </ul>
            </section>

            <hr class="sidebar-divider" />

            <div class="calc-total">
                <span>SUMA DO ZAPŁATY:</span>
                <span class="total-amount">PLN <?= number_format($total, 2) ?></span>
            </div>

            <p style="color: #6b7280; font-size: 0.75rem; text-align: center; margin-top: 1rem;">
                Należność doliczona do rachunku końcowego.
            </p>
        </aside>
    <?php endif; ?>

</div>