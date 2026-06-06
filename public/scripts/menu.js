document.addEventListener('DOMContentLoaded', () => {
    // Znajdź wszystkie przyciski "Dodaj"
    const addButtons = document.querySelectorAll('.btn-add');

    addButtons.forEach(button => {
        button.addEventListener('click', async (e) => {
            // Pobierz ID produktu z atrybutu data-product-id
            const productId = button.dataset.productId;
            const bookingId = button.dataset.bookingId; // Jeśli jesteś w kontekście konkretnej rezerwacji

            if (!bookingId) {
                alert("Błąd: Nie znaleziono aktywnej rezerwacji.");
                return;
            }

            try {
                // Wizualna informacja (np. zmiana koloru przycisku na chwilę)
                button.style.background = '#10b981'; // Zielony sukces

                const response = await fetch('/order/add', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        product_id: productId,
                        booking_id: bookingId
                    })
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    alert('Wystąpił błąd przy dodawaniu.');
                }
            } catch (error) {
                console.error('Błąd:', error);
            } finally {
                // Powrót do oryginalnego koloru po chwili
                setTimeout(() => button.style.background = '', 500);
            }
        });
    });
});