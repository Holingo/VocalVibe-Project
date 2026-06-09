<?php

require_once 'Repository.php';

/**
 * Repozytorium odpowiedzialne za operacje bazodanowe na salach/pokojach lokalu.
 */
class RoomsRepository extends Repository {

    /**
     * Pobiera pełną listę sal uporządkowaną rosnąco według identyfikatora.
     */
    public function getRooms(): array {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM rooms ORDER BY id ASC
        ');

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}