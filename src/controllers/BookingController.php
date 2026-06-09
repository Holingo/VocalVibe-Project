<?php

require_once 'AppController.php';
require_once __DIR__.'/../repositories/BookingRepository.php';
require_once __DIR__.'/../repositories/RoomsRepository.php';

/**
 * Kontroler obsługujący operacje związane z rezerwacjami sal (sprawdzanie godzin, tworzenie oraz anulowanie sesji).
 */
class BookingController extends AppController {

    private $bookingRepository;
    private $roomsRepository;

    public function __construct() {
        $this->bookingRepository = new BookingRepository();
        $this->roomsRepository = new RoomsRepository();
    }

    /**
     * Zwraca dostępne sloty godzinowe dla wybranej sali i daty (obsługuje żądania fetch w formacie JSON).
     */
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

    /**
     * Obsługuje proces zapisu nowej rezerwacji sali wraz z ewentualnymi produktami barowymi.
     */
    public function create() {
        if (!$this->isPost()) {
            header("Location: /dashboard");
            return;
        }

        $this->ensureAuthenticated();
        $userId = (int)$_SESSION['user_id'];

        $roomId = (int)($_POST['room_id'] ?? 0);
        $bookingDate = $_POST['booking_date'] ?? null;
        $bookingTime = $_POST['booking_time'] ?? null;
        $duration = (int)($_POST['duration'] ?? 2);
        $attendees = (int)($_POST['attendees'] ?? 2);

        if (!$roomId || !$bookingDate || !$bookingTime || $duration < 1 || $attendees < 1) {
            header("Location: /dashboard?page=book-now&error=missing_data");
            return;
        }

        $startTimestamp = strtotime("$bookingDate $bookingTime");
        $endTimestamp = $startTimestamp + ($duration * 3600);
        $startTimeFormatted = date('Y-m-d H:i:s', $startTimestamp);
        $endTimeFormatted = date('Y-m-d H:i:s', $endTimestamp);

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

        $bookedHours = $this->bookingRepository->getBookedHours($roomId, $bookingDate);
        for ($i = 0; $i < $duration; $i++) {
            $checkTime = date('H:00', $startTimestamp + ($i * 3600));
            if (in_array($checkTime, $bookedHours)) {
                header("Location: /dashboard?page=book-now&error=slot_taken");
                return;
            }
        }

        $totalPrice = ((float)$selectedRoom['hourly_rate'] * $duration) * 1.08;

        $bookingId = $this->bookingRepository->createBooking(
            $userId,
            $roomId,
            $startTimeFormatted,
            $endTimeFormatted,
            $totalPrice,
            $attendees
        );

        if ($bookingId) {
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

    /**
     * Anuluje wybraną rezerwację użytkownika w oparciu o weryfikację uprawnień i czasu sesji (Endpoint API).
     */
    public function cancel() {
        if (!$this->isPost()) {
            http_response_code(405);
            return;
        }

        $this->ensureAuthenticated();

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