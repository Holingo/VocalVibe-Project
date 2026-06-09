<?php

require_once 'Repository.php';

/**
 * Repozytorium obsługujące operacje bazodanowe na tabeli użytkowników.
 */
class UsersRepository extends Repository {

    /**
     * Pobiera wszystkich zarejestrowanych użytkowników.
     */
    public function getUsers(): ?array {
        $query = $this->database->connect()->prepare("SELECT * FROM users;");
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Wyszukuje użytkownika po adresie e-mail ignorując wielkość liter (funkcja LOWER).
     */
    public function getUserByEmail(string $email) {
        $query = $this->database->connect()->prepare(
            "SELECT u.*, r.name as role_name 
             FROM users u
             LEFT JOIN user_roles ur ON u.id = ur.user_id
             LEFT JOIN roles r ON ur.role_id = r.id
             WHERE LOWER(u.email) = LOWER(:email)"
        );
        $query->bindParam(':email', $email);
        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Tworzy i zapisuje nowe konto użytkownika w bazie danych.
     */
    public function createUser(string $username, string $email, string $hashedPassword, string $fullName) {
        $query = $this->database->connect()->prepare(
            "INSERT INTO users (username, email, full_name, password, is_active)
             VALUES (?, ?, ?, ?, true);"
        );
        $query->execute([
            $username,
            $email,
            $fullName,
            $hashedPassword
        ]);
    }
}