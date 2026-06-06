document.addEventListener("DOMContentLoaded", () => {
    // === 1. ZMIENNE DOM - REZERWACJA SALI ===
    const roomSelect = document.getElementById("room-select");
    const bookButtons = document.querySelectorAll(".btn-book:not(.btn-add-cart)"); // Wykluczamy przyciski z menu

    const displayDuration = document.getElementById("display-duration");
    const inputDuration = document.getElementById("input-duration");
    const inputDurationHidden = document.getElementById("input-duration-hidden");
    const btnMinusTime = document.getElementById("btn-minus-time");
    const btnPlusTime = document.getElementById("btn-plus-time");

    const displayAttendees = document.getElementById("display-attendees");
    const inputAttendees = document.getElementById("input-attendees");
    const inputAttendeesHidden = document.getElementById("input-attendees-hidden");
    const btnMinusPeople = document.getElementById("btn-minus-people");
    const btnPlusPeople = document.getElementById("btn-plus-people");

    // === 2. ZMIENNE DOM - KALKULATOR ===
    const calcRoomRate = document.getElementById("calc-room-rate");
    const calcOrderTotal = document.getElementById("calc-order-total");
    const calcTax = document.getElementById("calc-tax");
    const calcTotal = document.getElementById("calc-total");

    // === 3. ZMIENNE DOM - KOSZYK MENU ===
    const addCartButtons = document.querySelectorAll(".btn-add-cart");
    const orderList = document.getElementById("current-order-list");
    const hiddenProductsContainer = document.getElementById("hidden-products");

    // === 4. STAN APLIKACJI (STATE) ===
    let currentDuration = 2; // Domyślnie 2 godziny
    let currentAttendees = 2; // Domyślnie 2 osoby
    let currentMaxCapacity = 2; // Domyślny limit
    let orderTotal = 0.00; // Suma z koszyka
    let cart = {}; // Przechowuje produkty w formacie { "id": { name: "...", price: 15, qty: 2 } }

    // Przerywamy działanie skryptu, jeśli jesteśmy na innej podstronie (brak formularza)
    if (!roomSelect) return;

    // === 5. GŁÓWNE FUNKCJE LOGICZNE ===

    // Przelicza cały rachunek (Sala + Koszyk + Podatek)
    function updateBookingState() {
        const selectedOption = roomSelect.options[roomSelect.selectedIndex];
        if (!selectedOption) return;

        const hourlyRate = parseFloat(selectedOption.getAttribute("data-price"));
        currentMaxCapacity = parseInt(selectedOption.getAttribute("data-capacity"));

        // Walidacja: Jeśli zmieniliśmy salę na mniejszą, obcinamy liczbę gości
        if (currentAttendees > currentMaxCapacity) {
            currentAttendees = currentMaxCapacity;
            updateAttendeesView();
        }

        // Kalkulacja cen
        const roomBaseRate = hourlyRate * currentDuration;
        const tax = (roomBaseRate + orderTotal) * 0.08;
        const total = roomBaseRate + orderTotal + tax;

        // Aktualizacja widoku kalkulatora
        calcRoomRate.textContent = "PLN " + roomBaseRate.toFixed(2);
        calcOrderTotal.textContent = "PLN " + orderTotal.toFixed(2);
        calcTax.textContent = "PLN " + tax.toFixed(2);
        calcTotal.textContent = "PLN " + total.toFixed(2);
    }

    // Renderuje listę zamówień w sidebarze i tworzy ukryte inputy dla PHP
    function renderCart() {
        orderList.innerHTML = "";
        hiddenProductsContainer.innerHTML = "";
        orderTotal = 0; // Zerujemy przed ponownym przeliczeniem pętli

        let hasItems = false;

        Object.keys(cart).forEach(id => {
            if (cart[id].qty > 0) {
                hasItems = true;
                const item = cart[id];
                const itemTotal = item.price * item.qty;
                orderTotal += itemTotal; // Dodajemy do łącznej sumy koszyka

                // 1. Wizualna lista dla klienta
                orderList.innerHTML += `
                    <li>
                        <span class="qty">${item.qty}x</span> 
                        <span class="name">${item.name}</span> 
                        <span class="price">PLN ${itemTotal.toFixed(2)}</span>
                    </li>`;

                // 2. Niewidzialne dane dla kontrolera PHP (POST)
                hiddenProductsContainer.innerHTML += `
                    <input type="hidden" name="products[${id}]" value="${item.qty}">
                `;
            }
        });

        // Jeśli koszyk jest pusty, pokaż ładny komunikat
        if (!hasItems) {
            orderList.innerHTML = '<li class="empty-state" style="color: #9ca3af; font-size: 0.85rem; font-style: italic;">Brak zamówień barowych. Możesz dodać je później w panelu Menu.</li>';
        }

        // Na koniec uruchamiamy główny kalkulator, żeby doliczył podatek od nowej sumy
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

    // === 6. EVENT LISTENERY (INTERAKCJE UŻYTKOWNIKA) ===

    // Dodawanie produktów do koszyka
    addCartButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.getAttribute("data-id");
            const name = btn.getAttribute("data-name");
            const price = parseFloat(btn.getAttribute("data-price"));

            // Jeśli produktu nie ma w koszyku, utwórz go z ilością 0
            if (!cart[id]) {
                cart[id] = { name: name, price: price, qty: 0 };
            }

            // Zwiększ ilość
            cart[id].qty++;

            // Odśwież widok
            renderCart();

            // UX: Efekt wizualny dla przycisku, żeby klient wiedział, że "weszło"
            const originalText = btn.textContent;
            btn.textContent = "Dodano!";
            btn.style.background = "#10b981"; // Zielony na znak sukcesu
            setTimeout(() => {
                btn.textContent = originalText;
                btn.style.background = "";
            }, 600);
        });
    });

    // Kiedy klient klika "Book Now" na karcie konkretnej sali
    bookButtons.forEach(button => {
        button.addEventListener("click", (e) => {
            const roomId = e.target.getAttribute("data-room-id");
            roomSelect.value = roomId;
            updateBookingState();

            // Scrollowanie w dół do formularza na ekranach mobilnych
            if (window.innerWidth < 1100) {
                document.querySelector('.booking-sidebar').scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // Ręczna zmiana Sali z listy
    roomSelect.addEventListener("change", updateBookingState);

    // Obsługa czasu trwania
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

    // Obsługa liczby gości
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

    // Inicjalne uruchomienie przy ładowaniu strony
    updateBookingState();
    renderCart(); // Czyści ukryte inputy
});