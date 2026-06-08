<?php

require_once 'Repository.php';

class BookingRepository extends Repository {

    public function getBookingsByUserId(int $userId): array {
        $stmt = $this->database->connect()->prepare('
            SELECT 
                b.id,
                b.room_id,
                b.user_id,
                b.start_time,
                b.end_time,
                b.total_price,
                b.status,
                b.attendees, -- Pobiera liczbę gości z tabeli bookings
                b.start_time::date AS booking_date, 
                r.name as room_name, 
                r.image_url 
            FROM bookings b
            JOIN rooms r ON b.room_id = r.id
            WHERE b.user_id = :user_id
            ORDER BY b.start_time DESC
        ');

        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBookingById(int $id) {
        $stmt = $this->database->connect()->prepare('
            SELECT 
                *,
                start_time::date AS booking_date 
            FROM bookings 
            WHERE id = :id
        ');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getActiveBookingDetails(int $userId) {
        $stmt = $this->database->connect()->prepare('
            SELECT b.id, b.start_time, b.end_time, r.name as room_name 
            FROM bookings b
            JOIN rooms r ON b.room_id = r.id
            WHERE b.user_id = :user_id AND b.status = \'Active\'
            ORDER BY b.start_time ASC LIMIT 1
        ');
        $stmt->execute([':user_id' => $userId]);
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

    public function getAllActiveBookings(): array {
        $stmt = $this->database->connect()->prepare('
        SELECT b.*, u.email as user_email, r.name as room_name,
        (SELECT json_agg(items) FROM (
            SELECT p.name, oi.quantity FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id IN (SELECT id FROM orders WHERE booking_id = b.id)
        ) items) as order_items
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN rooms r ON b.room_id = r.id
        WHERE b.status = \'Active\'
    ');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createBooking(int $userId, int $roomId, string $startTime, string $endTime, float $totalPrice, int $attendees = 2) {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO bookings (user_id, room_id, start_time, end_time, total_price, attendees, status)
            VALUES (:user_id, :room_id, :start_time, :end_time, :total_price, :attendees, \'Active\')
            RETURNING id
        ');

        $stmt->execute([
            ':user_id' => $userId,
            ':room_id' => $roomId,
            ':start_time' => $startTime,
            ':end_time' => $endTime,
            ':total_price' => $totalPrice,
            ':attendees' => $attendees
        ]);

        return $stmt->fetchColumn();
    }

    public function deleteBooking(int $id): bool {
        $db = $this->database->connect();
        try {
            $db->beginTransaction();

            $stmt = $db->prepare('SELECT id FROM orders WHERE booking_id = :bId');
            $stmt->execute(['bId' => $id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($order) {
                $stmt = $db->prepare('DELETE FROM order_items WHERE order_id = :oId');
                $stmt->execute(['oId' => $order['id']]);

                $stmt = $db->prepare('DELETE FROM orders WHERE id = :oId');
                $stmt->execute(['oId' => $order['id']]);
            }

            $stmt = $db->prepare('DELETE FROM bookings WHERE id = :id');
            $success = $stmt->execute(['id' => $id]);

            $db->commit();
            return $success;
        } catch (\Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return false;
        }
    }
}