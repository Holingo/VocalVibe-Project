document.addEventListener('DOMContentLoaded', () => {
    const plusButtons = document.querySelectorAll('.btn-cart-plus');
    const minusButtons = document.querySelectorAll('.btn-cart-minus');
    const orderListContainer = document.querySelector('.order-list');
    const totalAmountContainer = document.querySelector('.total-amount');

    async function handleQuantityChange(productId, bookingId, action) {
        const endpoint = action === 'plus' ? '/order/add' : '/order/remove';

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId, booking_id: bookingId })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                // AKTUALIZACJA INTERFEJSU BEZ PRZEŁADOWANIA STRONY:
                updateUI(result.orderSummary);
            } else {
                alert(result.error || 'Wystąpił błąd podczas aktualizacji zamówienia.');
            }
        } catch (error) {
            console.error('Błąd sieci:', error);
        }
    }

    function updateUI(orderSummary) {
        // 1. Zresetuj najpierw wszystkie cyfry na kafelkach produktów do 0
        document.querySelectorAll('.counter-controls span').forEach(span => span.textContent = '0');

        // 2. Wyczyść boczny paragon rezerwacji
        if (orderListContainer) orderListContainer.innerHTML = '';
        let totalSum = 0;

        // 3. Przejdź przez zaktualizowaną listę przedmiotów z bazy danych
        if (orderSummary && orderSummary.length > 0) {
            orderSummary.forEach(item => {
                const qty = parseInt(item.quantity);
                const price = parseFloat(item.total);
                totalSum += price;

                // Zaktualizuj liczbę sztuk na właściwym kafelku w menu
                const qtySpan = document.querySelector(`.btn-cart-plus[data-product-id="${item.product_id}"]`)
                    ?.parentElement.querySelector('span');
                if (qtySpan) {
                    qtySpan.textContent = qty;
                }

                // Dodaj nowy element na boczny paragon HTML
                if (orderListContainer) {
                    orderListContainer.innerHTML += `
                        <li>
                            <span class="qty">${qty}x</span>
                            <span class="name">${escapeHtml(item.name)}</span>
                            <span class="price">PLN ${price.toFixed(2)}</span>
                        </li>
                    `;
                }
            });
        } else {
            if (orderListContainer) {
                orderListContainer.innerHTML = '<li class="empty-state" style="color: #9ca3af; font-size: 0.85rem; font-style: italic;">Brak zamówień barowych.</li>';
            }
        }

        // 4. Zaktualizuj całkowitą sumę w PLN na dole paragonu
        if (totalAmountContainer) {
            totalAmountContainer.textContent = `PLN ${totalSum.toFixed(2)}`;
        }
    }

    function escapeHtml(string) {
        return String(string).replace(/[&<>"']/g, function (s) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[s];
        });
    }

    plusButtons.forEach(button => {
        button.addEventListener('click', () => {
            handleQuantityChange(button.getAttribute('data-product-id'), button.getAttribute('data-booking-id'), 'plus');
        });
    });

    minusButtons.forEach(button => {
        button.addEventListener('click', () => {
            handleQuantityChange(button.getAttribute('data-product-id'), button.getAttribute('data-booking-id'), 'minus');
        });
    });
});