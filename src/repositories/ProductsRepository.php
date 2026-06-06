<?php
require_once 'Repository.php';

class ProductsRepository extends Repository {
    public function getProducts(): array {
        $stmt = $this->database->connect()->prepare('SELECT * FROM products ORDER BY category, name');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}