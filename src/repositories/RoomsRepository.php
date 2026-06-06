<?php

require_once 'Repository.php';

class RoomsRepository extends Repository {
    public function getRooms(): array {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM rooms ORDER BY id ASC
        ');

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}