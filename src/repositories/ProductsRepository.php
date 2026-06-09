<?php

require_once 'Repository.php';

/**
 * Repozytorium odpowiedzialne za bezpieczne operacje bazodanowe na produktach barowych i gastronomicznych.
 */
class ProductsRepository extends Repository {

    /**
     * Pobiera pełną listę produktów posortowaną według kategorii oraz nazw.
     */
    public function getProducts(): array {
        $stmt = $this->database->connect()->prepare('
            SELECT id, name, description, price, image_url, category 
            FROM products 
            ORDER BY category ASC, name ASC
        ');

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}