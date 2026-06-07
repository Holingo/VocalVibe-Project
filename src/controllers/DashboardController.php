<?php

require_once 'AppController.php';
require_once __DIR__.'/../repositories/RoomsRepository.php';
require_once __DIR__.'/../repositories/BookingRepository.php';
require_once __DIR__.'/../repositories/OrderRepository.php';
require_once __DIR__ . '/../repositories/ProductsRepository.php';

class DashboardController extends AppController {

    public function __construct()
    {
        $this->ensureAuthenticated();
    }

    public function myBookings() {
        // Ta metoda nie była uruchamiana przez routing index.php?page=my-bookings
        // Zostawiamy ją jako zabezpieczenie strukturalne
        $this->ensureAuthenticated();

        $bookingRepository = new BookingRepository();
        $orderRepository = new OrderRepository();

        $bookings = $bookingRepository->getBookingsByUserId($_SESSION['user_id']);

        foreach ($bookings as &$booking) {
            $booking['products'] = $orderRepository->getOrderSummary($booking['id']);
        }

        $this->render('dashboard', [
            'page' => 'my-bookings',
            'bookings' => $bookings
        ]);
    }

    public function index() {
        $page = $_GET['page'] ?? 'book-now';

        $data = [
            'title' => "VocalVibe - Panel Klienta",
            'page' => $page
        ];

        $roomsRepository = new RoomsRepository();
        $data['rooms'] = $roomsRepository->getRooms();

        switch ($page) {
            case 'book-now':
                $data['available_hours'] = [
                    '18:00' => '6:00 PM', '19:00' => '7:00 PM', '20:00' => '8:00 PM',
                    '21:00' => '9:00 PM', '22:00' => '10:00 PM', '23:00' => '11:00 PM',
                    '00:00' => '12:00 AM', '01:00' => '1:00 AM'
                ];

                $data['products'] = (new ProductsRepository())->getProducts();
                break;

            case 'my-bookings':
                $bookingRepository = new BookingRepository();
                $orderRepository = new OrderRepository();

                // 1. Pobieramy bazowe rezerwacje zalogowanego użytkownika
                $bookings = $bookingRepository->getBookingsByUserId($_SESSION['user_id']);

                // 2. NAPRAWIONE: Pobieramy listę produktów barowych dla KAŻDEJ rezerwacji z osobna
                foreach ($bookings as &$booking) {
                    $booking['products'] = $orderRepository->getOrderSummary($booking['id']);
                }
                unset($booking); // bezpieczne zniszczenie referencji pętli

                $data['bookings'] = $bookings;
                break;

            case 'menu':
                $bookingRepository = new BookingRepository();
                $activeBooking = $bookingRepository->getActiveBookingByUserId($_SESSION['user_id']);

                if (!$activeBooking) {
                    $data['no_booking'] = true;
                } else {
                    $data['products'] = (new ProductsRepository())->getProducts();
                    $data['currentBookingId'] = $activeBooking['id'];
                    // Pobieramy podsumowanie paragonu dla aktywnej rezerwacji
                    $data['orderSummary'] = (new OrderRepository())->getOrderSummary($activeBooking['id']);
                }
                break;
        }

        return $this->render("dashboard", $data);
    }
}