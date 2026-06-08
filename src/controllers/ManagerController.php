<?php

require_once 'AppController.php';
require_once __DIR__.'/../repositories/BookingRepository.php';
require_once __DIR__.'/../repositories/RoomsRepository.php';

class ManagerController extends AppController {

    private BookingRepository $bookingRepository;
    private RoomsRepository $roomsRepository;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->bookingRepository = new BookingRepository();
        $this->roomsRepository   = new RoomsRepository();
    }

    public function index() {
        if (!isset($_SESSION['role_name']) || $_SESSION['role_name'] !== 'Manager') {
            header("Location: /dashboard");
            die();
        }

        $allRooms       = $this->roomsRepository->getRooms();
        $activeBookings = $this->bookingRepository->getAllActiveBookings();

        // Index bookings by room_id for O(1) lookup in the view
        $bookingsByRoom = [];
        foreach ($activeBookings as $booking) {
            $bookingsByRoom[(int)$booking['room_id']] = $booking;
        }

        // Quick stats for the header bar
        $totalRooms   = count($allRooms);
        $occupiedCount = count($activeBookings);
        $totalRevenue  = array_sum(array_column($activeBookings, 'total_price'));

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