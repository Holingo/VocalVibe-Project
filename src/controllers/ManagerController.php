<?php

require_once 'AppController.php';
require_once __DIR__.'/../repositories/BookingRepository.php';
require_once __DIR__.'/../repositories/RoomsRepository.php';

/**
 * Kontroler zarządzający panelem managera lokalu, statystykami obłożenia sal oraz aktywnymi rezerwacjami.
 */
class ManagerController extends AppController {

    private BookingRepository $bookingRepository;
    private RoomsRepository $roomsRepository;

    public function __construct() {
        $this->bookingRepository = new BookingRepository();
        $this->roomsRepository   = new RoomsRepository();
    }

    /**
     * Główna akcja panelu managera – pobiera dane sal, oblicza przychód i weryfikuje uprawnienia administratora.
     */
    public function index() {
        $this->ensureAuthenticated();

        if (!isset($_SESSION['role_name']) || $_SESSION['role_name'] !== 'Manager') {
            header("Location: /dashboard");
            exit();
        }

        $allRooms       = $this->roomsRepository->getRooms();
        $activeBookings = $this->bookingRepository->getAllActiveBookings();
        $bookingsByRoom = [];
        foreach ($activeBookings as $booking) {
            $bookingsByRoom[(int)$booking['room_id']] = $booking;
        }

        $totalRooms    = count($allRooms);
        $occupiedCount = count($activeBookings);
        $totalRevenue  = array_sum(array_column($activeBookings, 'total_price'));

        // Wywołanie renderowania z dokładnym zachowaniem Twoich kluczy i nazw zmiennych
        $this->render('manager_dashboard', [
            'rooms'          => $allRooms,
            'activeBookings' => $activeBookings,
            'bookingsByRoom' => $bookingsByRoom,
            'stats' => [
                'total'    => $totalRooms,
                'occupied' => $occupiedCount,
                'free'     => $totalRooms - $occupiedCount,
                'revenue'  => $totalRevenue,
            ],
        ]);
    }
}