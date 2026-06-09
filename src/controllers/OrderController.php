<?php

require_once 'AppController.php';
require_once __DIR__.'/../repositories/OrderRepository.php';
require_once __DIR__.'/../repositories/ProductsRepository.php';
require_once __DIR__.'/../repositories/BookingRepository.php';

class OrderController extends AppController {

    private $orderRepository;
    private $productsRepository;
    private $bookingRepository;

    public function __construct() {
        $this->orderRepository = new OrderRepository();
        $this->productsRepository = new ProductsRepository();
        $this->bookingRepository = new BookingRepository();
    }

    /**
     * Główna akcja wyświetlania menu baru
     */
    public function menu() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header("Location: /login");
            exit();
        }

        // Pobieramy rezerwacje użytkownika z bazy danych
        $bookings = $this->bookingRepository->getBookingsByUserId($userId);

        $activeBooking = null;
        if (!empty($bookings) && is_array($bookings)) {
            foreach ($bookings as $b) {
                $status = isset($b['status']) ? strtolower(trim($b['status'])) : '';

                if ($status === 'active' || $status === 'confirmed') {
                    $activeBooking = $b;
                    break; // Znaleziono pasującą rezerwację
                }
            }
        }

        // Jeśli po sprawdzeniu bazy danych nadal brak rezerwacji, wyświetlamy widok pusty (no_booking)
        if (!$activeBooking) {
            $this->render('menu', ['no_booking' => true]);
            return;
        }

        $bookingId = (int)$activeBooking['id'];
        $products = $this->productsRepository->getProducts();
        $orderSummary = $this->orderRepository->getOrderSummary($bookingId);

        // Przekazujemy komplet danych do widoku
        $this->render('menu', [
            'products' => $products,
            'orderSummary' => $orderSummary,
            'booking_id' => $bookingId
        ]);
    }

    /**
     * API Endpoint: Dodawanie produktu do zamówienia (POST /order/add)
     */
    public function add() {
        if (!$this->isPost()) {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }

        $userId = $this->getLoggedInUserIdOrRespond();
        if (!$userId) return;

        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['product_id']) || !isset($input['booking_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Brakujące dane żądania']);
            return;
        }

        $bookingId = (int)$input['booking_id'];
        $productId = (int)$input['product_id'];

        // Weryfikacja bezpieczeństwa: Czy rezerwacja należy do zalogowanego użytkownika
        if (!$this->validateBookingOwner($bookingId, $userId)) {
            http_response_code(403);
            echo json_encode(['error' => 'Brak uprawnień do modyfikacji tej rezerwacji']);
            return;
        }

        $success = $this->orderRepository->addItemToOrder($bookingId, $productId, 1);

        header('Content-Type: application/json');
        if ($success) {
            echo json_encode([
                'success' => true,
                'orderSummary' => $this->orderRepository->getOrderSummary($bookingId)
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd zapisu bazy danych']);
        }
    }

    /**
     * API Endpoint: Usuwanie/Zmniejszanie ilości produktu (POST /order/remove)
     */
    public function remove() {
        if (!$this->isPost()) {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }

        $userId = $this->getLoggedInUserIdOrRespond();
        if (!$userId) return;

        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['product_id']) || !isset($input['booking_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Brakujące dane żądania']);
            return;
        }

        $bookingId = (int)$input['booking_id'];
        $productId = (int)$input['product_id'];

        if (!$this->validateBookingOwner($bookingId, $userId)) {
            http_response_code(403);
            echo json_encode(['error' => 'Brak uprawnień do modyfikacji tej rezerwacji']);
            return;
        }

        $success = $this->orderRepository->removeItemFromOrder($bookingId, $productId);

        header('Content-Type: application/json');
        if ($success) {
            echo json_encode([
                'success' => true,
                'orderSummary' => $this->orderRepository->getOrderSummary($bookingId)
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Błąd usuwania z bazy danych']);
        }
    }

    private function getLoggedInUserIdOrRespond() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Użytkownik niezalogowany']);
            return null;
        }
        return (int)$_SESSION['user_id'];
    }

    private function validateBookingOwner(int $bookingId, int $userId): bool {
        $booking = $this->bookingRepository->getBookingById($bookingId);
        return ($booking && (int)$booking['user_id'] === $userId);
    }
}