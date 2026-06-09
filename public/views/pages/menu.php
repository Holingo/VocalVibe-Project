<div class="menu-layout-container">
    <?php if (isset($no_booking) && $no_booking === true): ?>
        <div class="empty-state" style="width: 100%; grid-column: 1 / -1;">
            <i class="fa-solid fa-ban empty-icon-danger"></i>
            <h2>Brak aktywnej rezerwacji</h2>
            <p>Aby móc zamawiać z baru w trakcie imprezy, musisz najpierw posiadać aktywną (zatwierdzoną) rezerwację sali.</p>
            <a href="?page=book-now" class="btn-redirect">Rezerwuj teraz</a>
        </div>
    <?php else: ?>

        <div class="menu-main-content">
            <h1 class="page-title">Oferta Baru</h1>

            <?php
            $groupedProducts = [];
            if (isset($products) && is_array($products)) {
                foreach ($products as $product) {
                    $groupedProducts[$product['category']][] = $product;
                }
            }

            foreach ($groupedProducts as $category => $items): ?>
                <section class="menu-category-section">
                    <h2 class="category-header-title"><?= htmlspecialchars($category) ?></h2>
                    <div class="products-grid">
                        <?php foreach ($items as $product):
                            $currentQty = 0;
                            if (isset($orderSummary) && is_array($orderSummary)) {
                                foreach ($orderSummary as $summaryItem) {
                                    if ((int)$summaryItem['product_id'] === (int)$product['id']) {
                                        $currentQty = $summaryItem['quantity'];
                                        break;
                                    }
                                }
                            }
                            ?>
                            <article class="product-item-card">
                                <div class="product-img-wrapper" style="background-image: url('<?= htmlspecialchars($product['image_url'] ?? 'public/assets/default-food.jpg') ?>');"></div>
                                <div class="product-details-body">
                                    <div class="product-meta-row">
                                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                                        <span class="product-price-tag">PLN <?= number_format($product['price'], 2) ?></span>
                                    </div>
                                    <p class="product-description-text"><?= htmlspecialchars($product['description'] ?? '') ?></p>

                                    <div class="product-counter-controls">
                                        <button type="button" class="btn-cart-minus"
                                                data-product-id="<?= $product['id'] ?>"
                                                data-booking-id="<?= $booking_id ?? '' ?>">−</button>

                                        <span class="cart-item-qty" id="product-qty-<?= $product['id'] ?>"><?= $currentQty ?></span>

                                        <button type="button" class="btn-cart-plus"
                                                data-product-id="<?= $product['id'] ?>"
                                                data-booking-id="<?= $booking_id ?? '' ?>">+</button>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>

        <aside class="menu-summary-sidebar">
            <section class="bill-receipt-box">
                <div class="receipt-header">
                    <h3><i class="fa-solid fa-receipt"></i> Zamówiono do Sali</h3>
                    <span class="receipt-room-badge">Sesja #<?= htmlspecialchars($booking_id ?? 'Brak') ?></span>
                </div>

                <ul class="order-list">
                    <?php
                    $total = 0;
                    if (isset($orderSummary) && count($orderSummary) > 0):
                        foreach ($orderSummary as $item):
                            $total += $item['total'];
                            ?>
                            <li id="receipt-item-<?= $item['product_id'] ?>">
                                <span class="qty"><?= $item['quantity'] ?>x</span>
                                <span class="name"><?= htmlspecialchars($item['name']) ?></span>
                                <span class="price">PLN <?= number_format($item['total'], 2) ?></span>
                            </li>
                        <?php endforeach; else: ?>
                        <li class="empty-receipt-notice">Brak zamówień barowych.</li>
                    <?php endif; ?>
                </ul>

                <div class="receipt-footer-calc">
                    <span>SUMA BARU:</span>
                    <strong class="total-amount">PLN <?= number_format($total, 2) ?></strong>
                </div>

                <p class="receipt-info-disclaimer">
                    Należność zostanie automatycznie doliczona do Twojego rachunku końcowego w lokalu.
                </p>
            </section>
        </aside>

    <?php endif; ?>
</div>