document.addEventListener("DOMContentLoaded", () => {
    // === 1. ZMIENNE DOM - REZERWACJA SALI ===
    const roomSelect = document.getElementById("room-select");
    const bookButtons = document.querySelectorAll(".btn-book:not(.btn-add-cart)");

    //const displayDuration = document.getElementById("display-duration");
    const inputDuration = document.getElementById("input-duration");
    const inputDurationHidden = document.getElementById("input-duration-hidden");
    const btnMinusTime = document.getElementById("btn-minus-time");
    const btnPlusTime = document.getElementById("btn-plus-time");

    //const displayAttendees = document.getElementById("display-attendees");
    const inputAttendees = document.getElementById("input-attendees");
    const inputAttendeesHidden = document.getElementById("input-attendees-hidden");
    const btnMinusPeople = document.getElementById("btn-minus-people");
    const btnPlusPeople = document.getElementById("btn-plus-people");

    // === 2. ZMIENNE DOM - KALKULATOR ===
    const calcRoomRate = document.getElementById("calc-room-rate");
    const calcOrderTotal = document.getElementById("calc-order-total");
    const calcTax = document.getElementById("calc-tax");
    const calcTotal = document.getElementById("calc-total");

    // === 3. ZMIENNE DOM - KOSZYK I MODAL MENU ===
    const orderList = document.getElementById("current-order-list");
    const hiddenProductsContainer = document.getElementById("hidden-products");

    const btnOpenMenuModal = document.getElementById("btn-open-menu-modal");
    const btnCloseModal = document.getElementById("btn-close-modal");
    const menuModal = document.getElementById("menu-modal");

    const btnCartPlusAll = document.querySelectorAll(".btn-cart-plus");
    const btnCartMinusAll = document.querySelectorAll(".btn-cart-minus");

    // === 4. STAN APLIKACJI (STATE) ===
    let currentDuration = 2;
    let currentAttendees = 2;
    let currentMaxCapacity = 2;
    let orderTotal = 0.00;
    let cart = {}; // Przechowuje stan koszyka: { id: { name, price, qty } }

    if (!roomSelect) return;

    // === 5. GŁÓWNE FUNKCJE LOGICZNE ===

    function updateBookingState() {
        const selectedOption = roomSelect.options[roomSelect.selectedIndex];
        if (!selectedOption) return;

        const hourlyRate = parseFloat(selectedOption.getAttribute("data-price")) || 0;
        currentMaxCapacity = parseInt(selectedOption.getAttribute("data-capacity")) || 2;

        if (currentAttendees > currentMaxCapacity) {
            currentAttendees = currentMaxCapacity;
            updateAttendeesView();
        }

        const roomBaseRate = hourlyRate * currentDuration;
        const tax = (roomBaseRate + orderTotal) * 0.08;
        const total = roomBaseRate + orderTotal + tax;

        calcRoomRate.textContent = "PLN " + roomBaseRate.toFixed(2);
        calcOrderTotal.textContent = "PLN " + orderTotal.toFixed(2);
        calcTax.textContent = "PLN " + tax.toFixed(2);
        calcTotal.textContent = "PLN " + total.toFixed(2);
    }

    function renderCart() {
        orderList.innerHTML = "";
        hiddenProductsContainer.innerHTML = "";
        orderTotal = 0;

        let hasItems = false;

        Object.keys(cart).forEach(id => {
            const item = cart[id];

            const modalQtyLabel = document.getElementById(`modal-qty-${id}`);
            if (modalQtyLabel) {
                modalQtyLabel.textContent = item.qty;
            }

            if (item.qty > 0) {
                hasItems = true;
                const itemTotal = item.price * item.qty;
                orderTotal += itemTotal;

                orderList.innerHTML += `
                    <li>
                        <span class="qty">${item.qty}x</span> 
                        <span class="name">${item.name}</span> 
                        <span class="price">PLN ${itemTotal.toFixed(2)}</span>
                    </li>`;

                hiddenProductsContainer.innerHTML += `
                    <input type="hidden" name="products[${id}]" value="${item.qty}">
                `;
            }
        });

        if (!hasItems) {
            orderList.innerHTML = '<li class="empty-state" style="color: #9ca3af; font-size: 0.85rem; font-style: italic;">Brak zamówień barowych. Możesz dodać je później.</li>';
        }

        updateBookingState();
    }

    function updateAttendeesView() {
        inputAttendees.textContent = currentAttendees;
        displayAttendees.textContent = currentAttendees;
        inputAttendeesHidden.value = currentAttendees;
    }

    function updateDurationView() {
        inputDuration.textContent = currentDuration;
        displayDuration.textContent = currentDuration;
        inputDurationHidden.value = currentDuration;
    }

    // === 6. EVENT LISTENERY (OBSŁUGA INTERAKCJI) ===

    if (btnOpenMenuModal && menuModal) {
        btnOpenMenuModal.addEventListener("click", () => {
            menuModal.classList.remove("hidden");
        });

        btnCloseModal.addEventListener("click", () => {
            menuModal.classList.add("hidden");
        });

        menuModal.addEventListener("click", (e) => {
            if (e.target === menuModal) {
                menuModal.classList.add("hidden");
            }
        });
    }

    btnCartPlusAll.forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.getAttribute("data-id");
            const name = btn.getAttribute("data-name");
            const price = parseFloat(btn.getAttribute("data-price")) || 0;

            if (!cart[id]) {
                cart[id] = { name: name, price: price, qty: 0 };
            }

            cart[id].qty++;
            renderCart();
        });
    });

    btnCartMinusAll.forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.getAttribute("data-id");

            if (cart[id] && cart[id].qty > 0) {
                cart[id].qty--;
                renderCart();
            }
        });
    });

    bookButtons.forEach(button => {
        button.addEventListener("click", (e) => {
            const roomId = e.target.getAttribute("data-room-id");
            roomSelect.value = roomId;
            updateBookingState();

            if (window.innerWidth < 1100) {
                document.querySelector('.booking-sidebar').scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    roomSelect.addEventListener("change", updateBookingState);

    btnMinusTime.addEventListener("click", () => {
        if (currentDuration > 1) {
            currentDuration--;
            updateDurationView();
            updateBookingState();
        }
    });

    btnPlusTime.addEventListener("click", () => {
        if (currentDuration < 12) {
            currentDuration++;
            updateDurationView();
            updateBookingState();
        }
    });

    btnMinusPeople.addEventListener("click", () => {
        if (currentAttendees > 1) {
            currentAttendees--;
            updateAttendeesView();
            updateBookingState();
        }
    });

    btnPlusPeople.addEventListener("click", () => {
        if (currentAttendees < currentMaxCapacity) {
            currentAttendees++;
            updateAttendeesView();
            updateBookingState();
        }
    });

    updateBookingState();

    btnCartPlusAll.forEach(btn => {
        const id = btn.getAttribute("data-id");
        if (!cart[id]) {
            cart[id] = {
                name: btn.getAttribute("data-name"),
                price: parseFloat(btn.getAttribute("data-price")),
                qty: 0
            };
        }
    });
    renderCart();
});