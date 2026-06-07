<?php

require_once 'AppController.php';
require_once __DIR__.'/../repositories/BookingRepository.php';
require_once __DIR__.'/../repositories/RoomsRepository.php';

class BookingController extends AppController {

    private $bookingRepository;
    private $roomsRepository;

    public function __construct() {
        $this->bookingRepository = new BookingRepository();
        $this->roomsRepository = new RoomsRepository();
    }

    public function getAvailableHours() {
        header('Content-type: application/json');

        if (!$this->isPost()) {
            http_response_code(405);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $roomId = (int)($data['room_id'] ?? 0);
        $date = $data['date'] ?? null;

        if (!$roomId || !$date) {
            http_response_code(400);
            echo json_encode(["error" => "Brak wymaganych parametrów"]);
            return;
        }

        $bookedHours = $this->bookingRepository->getBookedHours($roomId, $date);

        $operatingHours = [
            '18:00' => '6:00 PM', '19:00' => '7:00 PM', '20:00' => '8:00 PM',
            '21:00' => '9:00 PM', '22:00' => '10:00 PM', '23:00' => '11:00 PM',
            '00:00' => '12:00 AM', '01:00' => '1:00 AM'
        ];

        $availableHours = [];
        foreach ($operatingHours as $dbValue => $displayValue) {
            if (!in_array($dbValue, $bookedHours)) {
                $availableHours[] = ["value" => $dbValue, "label" => $displayValue];
            }
        }

        echo json_encode($availableHours);
    }

    public function create() {
        if (!$this->isPost()) {
            header("Location: /dashboard");
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            return;
        }
        $userId = (int)$_SESSION['user_id'];

        $roomId = (int)($_POST['room_id'] ?? 0);
        $bookingDate = $_POST['booking_date'] ?? null;
        $bookingTime = $_POST['booking_time'] ?? null;
        $duration = (int)($_POST['duration'] ?? 2);
        $attendees = (int)($_POST['attendees'] ?? 2); // Zmieniono domyślną na 2 (zgodnie ze stanem skryptu)

        if (!$roomId || !$bookingDate || !$bookingTime || $duration < 1 || $attendees < 1) {
            header("Location: /dashboard?page=book-now&error=missing_data");
            return;
        }

        // Obliczanie czasu sesji
        $startTimestamp = strtotime("$bookingDate $bookingTime");
        $endTimestamp = $startTimestamp + ($duration * 3600);
        $startTimeFormatted = date('Y-m-d H:i:s', $startTimestamp);
        $endTimeFormatted = date('Y-m-d H:i:s', $endTimestamp);

        // Walidacja pokoju i jego pojemności
        $selectedRoom = null;
        foreach ($this->roomsRepository->getRooms() as $r) {
            if ((int)$r['id'] === $roomId) {
                $selectedRoom = $r;
                break;
            }
        }

        if (!$selectedRoom || $attendees > (int)$selectedRoom['capacity']) {
            header("Location: /dashboard?page=book-now&error=invalid_room_data");
            return;
        }

        // Walidacja dostępności slotów
        $bookedHours = $this->bookingRepository->getBookedHours($roomId, $bookingDate);
        for ($i = 0; $i < $duration; $i++) {
            $checkTime = date('H:00', $startTimestamp + ($i * 3600));
            if (in_array($checkTime, $bookedHours)) {
                header("Location: /dashboard?page=book-now&error=slot_taken");
                return;
            }
        }

        // Cena bazowa doliczająca 8% VAT
        $totalPrice = ((float)$selectedRoom['hourly_rate'] * $duration) * 1.08;

        // POPRAWKA: Przekazujemy $attendees jako szósty parametr do bazy
        $bookingId = $this->bookingRepository->createBooking(
            $userId,
            $roomId,
            $startTimeFormatted,
            $endTimeFormatted,
            $totalPrice,
            $attendees
        );

        if ($bookingId) {
            // Zapis produktów barowych zamówionych przy rezerwacji
            $products = $_POST['products'] ?? [];
            if (!empty($products)) {
                require_once __DIR__.'/../repositories/OrderRepository.php';
                $orderRepo = new OrderRepository();

                foreach ($products as $pId => $qty) {
                    if ((int)$qty > 0) {
                        $orderRepo->addItemToOrder($bookingId, (int)$pId, (int)$qty);
                    }
                }
            }
            header("Location: /dashboard?page=my-bookings&success=1");
        } else {
            header("Location: /dashboard?page=book-now&error=db_failed");
        }
    }

    public function cancel() {
        if (!$this->isPost()) {
            http_response_code(405);
            return;
        }

        // POPRAWKA: Inicjalizacja sesji, aby sprawdzić zalogowanego użytkownika
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Użytkownik niezalogowany']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['booking_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Brak identyfikatora rezerwacji']);
            return;
        }

        $bookingId = (int)$input['booking_id'];
        $bookingRepository = new BookingRepository();

        $booking = $bookingRepository->getBookingById($bookingId);

        if (!$booking || (int)$booking['user_id'] !== (int)$_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode(['error' => 'Brak uprawnień do tej rezerwacji']);
            return;
        }

        // Walidacja czasu (blokujemy anulowanie rezerwacji, które trwają lub są z przeszłości)
        $bookingStart = strtotime($booking['start_time']);
        if ($bookingStart <= time()) {
            http_response_code(400);
            echo json_encode(['error' => 'Nie można anulować rezerwacji, która już się rozpoczęła lub zakończyła']);
            return;
        }

        $success = $bookingRepository->deleteBooking($bookingId);

        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
    }
}