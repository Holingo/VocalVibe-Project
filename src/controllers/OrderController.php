<?php
require_once 'AppController.php';
require_once __DIR__.'/../repositories/OrderRepository.php';

class OrderController extends AppController {

    public function add() {
        // Zabezpieczamy tylko przez POST
        if (!$this->isPost()) {
            http_response_code(405);
            return;
        }

        // Odczyt danych z JSON
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['product_id']) || !isset($input['booking_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing data']);
            return;
        }

        $orderRepository = new OrderRepository();
        $success = $orderRepository->addItemToOrder(
            (int)$input['booking_id'],
            (int)$input['product_id'],
            1
        );

        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
    }
}