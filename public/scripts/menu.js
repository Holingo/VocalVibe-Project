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
                updateUI(result.orderSummary);
            } else {
                alert(result.error || 'Wystąpił błąd podczas aktualizacji zamówienia.');
            }
        } catch (error) {
            console.error('Błąd sieci:', error);
        }
    }

    function updateUI(orderSummary) {
        // 1. Resetujemy wszystkie liczniki na kafelkach produktów do 0
        document.querySelectorAll('.cart-item-qty').forEach(span => span.textContent = '0');

        // 2. Czyszczenie listy na paragonie bocznym
        if (!orderListContainer) return;
        orderListContainer.innerHTML = '';

        let totalSum = 0;

        if (orderSummary && orderSummary.length > 0) {
            orderSummary.forEach(item => {
                const qty = parseInt(item.quantity);
                const price = parseFloat(item.total);
                totalSum += price;

                // Aktualizacja cyfry na kafelku danego produktu
                const productQtySpan = document.getElementById(`product-qty-${item.product_id}`);
                if (productQtySpan) {
                    productQtySpan.textContent = qty;
                }

                // Dodawanie elementu do paragonu
                orderListContainer.innerHTML += `
                    <li id="receipt-item-${item.product_id}">
                        <span class="qty">${qty}x</span>
                        <span class="name">${escapeHtml(item.name)}</span>
                        <span class="price">PLN ${price.toFixed(2)}</span>
                    </li>
                `;
            });
        } else {
            orderListContainer.innerHTML = '<li class="empty-receipt-notice">Brak zamówień barowych.</li>';
        }

        // 3. Aktualizacja sumy końcowej
        if (totalAmountContainer) {
            totalAmountContainer.textContent = `PLN ${totalSum.toFixed(2)}`;
        }
    }

    function escapeHtml(string) {
        return String(string).replace(/[&<>\"']/g, function (s) {
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