<?php
require_once 'AppController.php';
require_once __DIR__.'/../repositories/OrderRepository.php';

class OrderController extends AppController {

    public function add() {
        if (!$this->isPost()) { http_response_code(405); return; }
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['product_id']) || !isset($input['booking_id'])) {
            http_response_code(400); echo json_encode(['error' => 'Missing data']); return;
        }

        $orderRepository = new OrderRepository();
        $success = $orderRepository->addItemToOrder((int)$input['booking_id'], (int)$input['product_id'], 1);

        header('Content-Type: application/json');
        if ($success) {
            // Zwracamy aktualne podsumowanie zamówienia do dynamicznego przetworzenia w JS
            echo json_encode(['success' => true, 'orderSummary' => $orderRepository->getOrderSummary((int)$input['booking_id'])]);
        } else {
            http_response_code(500); echo json_encode(['error' => 'Database failed']);
        }
    }

    public function remove() {
        if (!$this->isPost()) { http_response_code(405); return; }
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['product_id']) || !isset($input['booking_id'])) {
            http_response_code(400); echo json_encode(['error' => 'Missing data']); return;
        }

        $orderRepository = new OrderRepository();
        $success = $orderRepository->removeItemFromOrder((int)$input['booking_id'], (int)$input['product_id']);

        header('Content-Type: application/json');
        if ($success) {
            echo json_encode(['success' => true, 'orderSummary' => $orderRepository->getOrderSummary((int)$input['booking_id'])]);
        } else {
            http_response_code(500); echo json_encode(['error' => 'Database failed']);
        }
    }
}