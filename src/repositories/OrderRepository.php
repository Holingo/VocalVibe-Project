<?php

require_once 'Repository.php';

/**
 * Repozytorium odpowiedzialne za bezpieczne operacje bazodanowe na zamówieniach barowych (koszyku produktów).
 */
class OrderRepository extends Repository {

    /**
     * Pobiera szczegółowe podsumowanie pozycji rachunku barowego dla konkretnej rezerwacji.
     */
    public function getOrderSummary(int $bookingId): array {
        $stmt = $this->database->connect()->prepare('
            SELECT 
                p.id AS product_id, 
                p.name, 
                oi.quantity, 
                oi.unit_price, 
                (oi.quantity * oi.unit_price) AS total
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN products p ON oi.product_id = p.id
            WHERE o.booking_id = :bId
            ORDER BY p.name ASC
        ');

        $stmt->execute(['bId' => $bookingId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Dodaje produkt do zamówienia danej rezerwacji (tworzy koszyk lub zwiększa ilość pozycji).
     */
    public function addItemToOrder(int $bookingId, int $productId, int $quantity = 1): bool {
        $db = $this->database->connect();

        try {
            $db->beginTransaction();

            // 1. Sprawdzenie, czy istnieje już powiązany rekord w tabeli orders
            $stmt = $db->prepare('SELECT id FROM orders WHERE booking_id = :bId');
            $stmt->execute(['bId' => $bookingId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            $orderId = $order ? $order['id'] : null;

            // Jeśli zamówienie nie istnieje, następuje jego bezpieczna rejestracja
            if (!$orderId) {
                $stmt = $db->prepare('INSERT INTO orders (booking_id) VALUES (:bId) RETURNING id');
                $stmt->execute(['bId' => $bookingId]);
                $orderId = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
            }

            // 2. Pobranie aktualnej ceny katalogowej przypisanej do produktu
            $stmt = $db->prepare('SELECT price FROM products WHERE id = :pId');
            $stmt->execute(['pId' => $productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                $db->rollBack();
                return false;
            }
            $unitPrice = $product['price'];

            // 3. Weryfikacja, czy dany produkt znajduje się już na liście pozycji zamówienia
            $stmt = $db->prepare('SELECT id, quantity FROM order_items WHERE order_id = :oId AND product_id = :pId');
            $stmt->execute(['oId' => $orderId, 'pId' => $productId]);
            $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingItem) {
                $stmt = $db->prepare('UPDATE order_items SET quantity = quantity + :qty WHERE id = :id');
                $stmt->execute(['qty' => $quantity, 'id' => $existingItem['id']]);
            } else {
                $stmt = $db->prepare('
                    INSERT INTO order_items (order_id, product_id, quantity, unit_price) 
                    VALUES (:oId, :pId, :qty, :price)
                ');
                $stmt->execute([
                    'oId' => $orderId,
                    'pId' => $productId,
                    'qty' => $quantity,
                    'price' => $unitPrice
                ]);
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            error_log("OrderRepository Error (addItem): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Zmniejsza ilość wybranego produktu o 1 sztukę lub całkowicie usuwa go z rachunku rezerwacji.
     */
    public function removeItemFromOrder(int $bookingId, int $productId): bool {
        $db = $this->database->connect();

        try {
            $db->beginTransaction();

            // 1. Pobranie identyfikatora zamówienia głównego
            $stmt = $db->prepare('SELECT id FROM orders WHERE booking_id = :bId');
            $stmt->execute(['bId' => $bookingId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                $db->rollBack();
                return false;
            }
            $orderId = $order['id'];

            // 2. Pobranie powiązanej pozycji z tabeli elementów składowych zamówienia
            $stmt = $db->prepare('SELECT id, quantity FROM order_items WHERE order_id = :oId AND product_id = :pId');
            $stmt->execute(['oId' => $orderId, 'pId' => $productId]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                $db->rollBack();
                return false;
            }

            if ($item['quantity'] > 1) {
                $stmt = $db->prepare('UPDATE order_items SET quantity = quantity - 1 WHERE id = :id');
                $stmt->execute(['id' => $item['id']]);
            } else {
                $stmt = $db->prepare('DELETE FROM order_items WHERE id = :id');
                $stmt->execute(['id' => $item['id']]);
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            error_log("OrderRepository Error (removeItem): " . $e->getMessage());
            return false;
        }
    }
}