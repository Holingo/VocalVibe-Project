document.addEventListener("DOMContentLoaded", () => {
    const cancelButtons = document.querySelectorAll(".btn-cancel-booking");

    cancelButtons.forEach(btn => {
        btn.addEventListener("click", async () => {
            const bookingId = btn.getAttribute("data-booking-id");

            if (!bookingId) return;

            // Okienko potwierdzające decyzję klienta
            const confirmed = confirm("Czy na pewno chcesz anulować tę rezerwację? Ta operacja jest nieodwracalna.");
            if (!confirmed) return;

            try {
                const response = await fetch("/booking/cancel", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ booking_id: bookingId })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    // Pobieramy kartę rezerwacji z DOM
                    const card = document.getElementById(`booking-card-${bookingId}`);
                    if (card) {
                        // Efekt płynnego znikania karty
                        card.style.transition = "all 0.4s ease";
                        card.style.opacity = "0";
                        card.style.transform = "scale(0.9)";

                        setTimeout(() => {
                            card.remove();

                            // Jeśli usunęliśmy ostatnią rezerwację, przeładuj stronę, aby pokazać pusty stan (empty state)
                            const remainingCards = document.querySelectorAll(".booking-card");
                            if (remainingCards.length === 0) {
                                window.location.reload();
                            }
                        }, 400);
                    }
                } else {
                    alert(result.error || "Nie udało się anulować rezerwacji. Możliwe, że sesja już się rozpoczęła.");
                }
            } catch (error) {
                console.error("Błąd sieci:", error);
                alert("Wystąpił błąd komunikacji z serwerem.");
            }
        });
    });
});