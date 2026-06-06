<?php
require_once 'Repository.php';

class OrderRepository extends Repository {

    public function getOrderSummary(int $bookingId) {
        $stmt = $this->database->connect()->prepare('
            SELECT p.name, oi.quantity, oi.unit_price, (oi.quantity * oi.unit_price) as total
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN products p ON oi.product_id = p.id
            WHERE o.booking_id = :bId
        ');
        $stmt->execute(['bId' => $bookingId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addItemToOrder(int $bookingId, int $productId, int $quantity) {
        $db = $this->database->connect();

        try {
            $db->beginTransaction();

            // 1. Sprawdź, czy zamówienie dla tej rezerwacji istnieje
            $stmt = $db->prepare('SELECT id FROM orders WHERE booking_id = :bId');
            $stmt->execute(['bId' => $bookingId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            $orderId = $order ? $order['id'] : null;

            // 2. Jeśli nie ma zamówienia - utwórz je
            if (!$orderId) {
                $stmt = $db->prepare('INSERT INTO orders (booking_id) VALUES (:bId) RETURNING id');
                $stmt->execute(['bId' => $bookingId]);
                $orderId = $stmt->fetchColumn();
            }

            // 3. Dodaj przedmiot do zamówienia (order_items)
            // Pobieramy cenę z tabeli products, żeby zawsze była aktualna
            $stmt = $db->prepare('
                INSERT INTO order_items (order_id, product_id, quantity, unit_price) 
                VALUES (:oId, :pId, :qty, (SELECT price FROM products WHERE id = :pId))
            ');

            $result = $stmt->execute([
                'oId' => $orderId,
                'pId' => $productId,
                'qty' => $quantity
            ]);

            $db->commit();
            return $result;
        } catch (Exception $e) {
            $db->rollBack();
            return false;
        }
    }
}