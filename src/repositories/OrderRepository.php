<?php
require_once 'Repository.php';

class OrderRepository extends Repository {

    public function getOrderSummary(int $bookingId) {
        $stmt = $this->database->connect()->prepare('
            SELECT p.id as product_id, p.name, oi.quantity, oi.unit_price, (oi.quantity * oi.unit_price) as total
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN products p ON oi.product_id = p.id
            WHERE o.booking_id = :bId
            ORDER BY p.name ASC
        ');
        $stmt->execute(['bId' => $bookingId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addItemToOrder(int $bookingId, int $productId, int $quantity = 1) {
        $db = $this->database->connect();
        try {
            $db->beginTransaction();

            // 1. Sprawdź, czy zamówienie dla tej rezerwacji istnieje
            $stmt = $db->prepare('SELECT id FROM orders WHERE booking_id = :bId');
            $stmt->execute(['bId' => $bookingId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            $orderId = $order ? $order['id'] : null;

            if (!$orderId) {
                $stmt = $db->prepare('INSERT INTO orders (booking_id) VALUES (:bId) RETURNING id');
                $stmt->execute(['bId' => $bookingId]);
                $orderId = $stmt->fetchColumn();
            }

            // 2. SPRAWDZENIE: Czy produkt już jest w tym zamówieniu?
            $stmt = $db->prepare('SELECT id, quantity FROM order_items WHERE order_id = :oId AND product_id = :pId');
            $stmt->execute(['oId' => $orderId, 'pId' => $productId]);
            $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingItem) {
                // Jeśli istnieje, zwiększamy ilość
                $stmt = $db->prepare('UPDATE order_items SET quantity = quantity + :qty WHERE id = :id');
                $stmt->execute(['qty' => $quantity, 'id' => $existingItem['id']]);
            } else {
                // Jeśli nie istnieje, wstawiamy nowy rekord
                $stmt = $db->prepare('
                    INSERT INTO order_items (order_id, product_id, quantity, unit_price) 
                    VALUES (:oId, :pId, :qty, (SELECT price FROM products WHERE id = :pId))
                ');
                $stmt->execute(['oId' => $orderId, 'pId' => $productId, 'qty' => $quantity]);
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            return false;
        }
    }

    public function removeItemFromOrder(int $bookingId, int $productId) {
        $db = $this->database->connect();
        try {
            $db->beginTransaction();

            // 1. Znajdź ID zamówienia
            $stmt = $db->prepare('SELECT id FROM orders WHERE booking_id = :bId');
            $stmt->execute(['bId' => $bookingId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$order) return false;
            $orderId = $order['id'];

            // 2. Znajdź pozycję w koszyku
            $stmt = $db->prepare('SELECT id, quantity FROM order_items WHERE order_id = :oId AND product_id = :pId');
            $stmt->execute(['oId' => $orderId, 'pId' => $productId]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) return false;

            if ($item['quantity'] > 1) {
                // Zmniejsz o 1
                $stmt = $db->prepare('UPDATE order_items SET quantity = quantity - 1 WHERE id = :id');
                $stmt->execute(['id' => $item['id']]);
            } else {
                // Usuń całkowicie z bazy
                $stmt = $db->prepare('DELETE FROM order_items WHERE id = :id');
                $stmt->execute(['id' => $item['id']]);
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            return false;
        }
    }
}