<?php

require_once 'AppController.php';
require_once __DIR__.'/../repositories/RoomsRepository.php';
require_once __DIR__.'/../repositories/BookingRepository.php';
require_once __DIR__.'/../repositories/OrderRepository.php';
require_once __DIR__.'/../repositories/ProductsRepository.php';

/**
 * Kontroler zarządzający panelem klienta, procesem rezerwacji sal oraz zamówieniami barowymi.
 */
class DashboardController extends AppController {

    /**
     * Konstruktor kontrolera – automatycznie dba o to, aby nikt nieautoryzowany nie wszedł do panelu.
     */
    public function __construct() {
        $this->ensureAuthenticated();
    }

    /**
     * Główna akcja panelu klienta, obsługująca dynamiczne podstrony za pomocą parametru Query String.
     */
    public function index() {
        $page = $_GET['page'] ?? 'book-now';

        $data = [
            'title' => "VocalVibe — Panel Klienta",
            'page'  => $page,
            'available_hours' => [
                '18:00' => '6:00 PM', '19:00' => '7:00 PM', '20:00' => '8:00 PM',
                '21:00' => '9:00 PM', '22:00' => '10:00 PM', '23:00' => '11:00 PM',
                '00:00' => '12:00 AM', '01:00' => '1:00 AM'
            ]
        ];

        $roomsRepository = new RoomsRepository();
        $bookingRepository = new BookingRepository();
        $orderRepository = new OrderRepository();
        $productsRepository = new ProductsRepository();

        $data['rooms'] = $roomsRepository->getRooms();

        switch ($page) {
            case 'book-now':
                $data['products'] = $productsRepository->getProducts();
                break;

            case 'my-bookings':
                $bookings = $bookingRepository->getBookingsByUserId($_SESSION['user_id']);

                foreach ($bookings as &$booking) {
                    $booking['products'] = $orderRepository->getOrderSummary($booking['id']);
                }
                unset($booking);

                $data['bookings'] = $bookings;
                break;

            case 'menu':
                $activeBooking = $bookingRepository->getActiveBookingDetails($_SESSION['user_id']);

                if (!$activeBooking) {
                    $data['no_booking'] = true;
                } else {
                    $data['products'] = $productsRepository->getProducts();
                    $data['currentBooking'] = $activeBooking;
                    $data['booking_id'] = $activeBooking['id'];
                    $data['orderSummary'] = $orderRepository->getOrderSummary($activeBooking['id']);
                }
                break;
        }

        $this->render("dashboard", $data);
    }
}