<?php

require_once 'Repository.php';

class BookingRepository extends Repository {

    public function getBookingsByUserId(int $userId): array {
        $stmt = $this->database->connect()->prepare('
            SELECT b.*, r.name as room_name, r.image_url 
            FROM bookings b
            JOIN rooms r ON b.room_id = r.id
            WHERE b.user_id = :user_id
            ORDER BY b.start_time DESC
        ');

        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveBookingDetails(int $userId) {
        $stmt = $this->database->connect()->prepare('
            SELECT b.id, b.start_time, b.end_time, r.name as room_name 
            FROM bookings b
            JOIN rooms r ON b.room_id = r.id
            WHERE b.user_id = :user_id AND b.status = :status
            ORDER BY b.start_time ASC LIMIT 1
        ');
        $stmt->execute([':user_id' => $userId, ':status' => 'Active']);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getBookedHours(int $roomId, string $date): array {
        $stmt = $this->database->connect()->prepare('
            SELECT start_time, end_time 
            FROM bookings 
            WHERE room_id = :room_id 
              AND status = \'Active\' 
              AND (start_time::date = :date OR end_time::date = :date)
        ');

        $stmt->execute([
            ':room_id' => $roomId,
            ':date' => $date
        ]);

        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $bookedHours = [];

        foreach ($bookings as $booking) {
            $start = new DateTime($booking['start_time']);
            $end = new DateTime($booking['end_time']);

            $interval = new DateInterval('PT1H');
            $period = new DatePeriod($start, $interval, $end);

            foreach ($period as $dt) {
                if ($dt->format('Y-m-d') === $date) {
                    $bookedHours[] = $dt->format('H:00');
                }
            }
        }

        return array_unique($bookedHours);
    }

    public function createBooking(int $userId, int $roomId, string $startTime, string $endTime, float $totalPrice) {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO bookings (user_id, room_id, start_time, end_time, total_price, status)
            VALUES (:user_id, :room_id, :start_time, :end_time, :total_price, \'Active\')
            RETURNING id
        ');

        $stmt->execute([
            ':user_id' => $userId,
            ':room_id' => $roomId,
            ':start_time' => $startTime,
            ':end_time' => $endTime,
            ':total_price' => $totalPrice
        ]);

        return $stmt->fetchColumn(); // Zwraca nowe ID rezerwacji
    }
}