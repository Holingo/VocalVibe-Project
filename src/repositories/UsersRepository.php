<?php

require_once 'Repository.php';

class UsersRepository extends Repository {

    public function getUsers(): ?array 
    {
        $query = $this->database->connect()->prepare(
            "
            SELECT * FROM users;
            "
        );
        $query->execute();

        $users = $query->fetchAll(PDO::FETCH_ASSOC);
        return $users;
    }

  public function getUserByEmail(string $email) {
        $query = $this->database->connect()->prepare(
        "SELECT u.*, r.name as role_name 
            FROM users u
            LEFT JOIN user_roles ur ON u.id = ur.user_id
            LEFT JOIN roles r ON ur.role_id = r.id
            WHERE u.email = :email"
        );
        $query->bindParam(':email', $email);
        $query->execute();

        $user = $query->fetch(PDO::FETCH_ASSOC);
        return $user;
    }

    public function createUser(
        string $username,
        string $email,
        string $hashedPassword,
        string $fullName
    ) {
        $query = $this->database->connect()->prepare(
            "
            INSERT INTO users (username, email, full_name, password, is_active)
            VALUES (?, ?, ?, ?, true);
            "
        );
        $query->execute([
            $username,
            $email,
            $fullName,
            $hashedPassword
        ]);
    }


    public function searchUsers(string $searchTerm): array {
        $query = $this->database->connect()->prepare(
            "
            SELECT * FROM users 
            WHERE username LIKE :search OR email LIKE :search OR full_name LIKE :search
            "
        );
        $likeTerm = '%' . $searchTerm . '%';
        $query->bindParam(':search', $likeTerm);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteUser(int $id): bool {
        $query = $this->database->connect()->prepare(
            "
            DELETE FROM users
            WHERE id = :id
            "
        );
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        return $query->rowCount() > 0;
    }
}
