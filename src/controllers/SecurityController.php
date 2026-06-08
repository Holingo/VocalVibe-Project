<?php

require_once 'AppController.php';
require_once __DIR__.'/../repositories/UsersRepository.php';

class SecurityController extends AppController {

    public function login() {
        if (!$this->isPost()) {
            return $this->render('login');
        }

        $email = trim($_POST["email"] ?? '');
        $password = $_POST["password"] ?? '';

        if (empty($email) || empty($password)) {
            return $this->render('login', ['messages' => 'Fill all fields']);
        }

        $userRepository = new UsersRepository();
        $user = $userRepository->getUserByEmail($email);

        if (!$user) {
            return $this->render('login', ['messages' => 'User not found']);
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (password_verify($password, $user['password'])) {
            if (session_status() === PHP_SESSION_NONE) session_start();

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['role_name'] = $user['role_name'];

            if ($user['role_name'] === 'Manager') {
                header("Location: /dashboard_manager");
            } else {
                header("Location: /dashboard");
            }
            exit();
        }
    }

    public function register() {
        $userRepository = new UsersRepository();

        if ($this->isPost()) {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $password2 = $_POST['password2'] ?? '';
            $firstName = trim($_POST['firstName'] ?? '');
            $lastName = trim($_POST['lastName'] ?? '');

            if (empty($email) || empty($password) || empty($password2) || empty($firstName) || empty($lastName)) {
                return $this->render('register', ['messages' => 'Fill all fields']);
            }

            if ($password !== $password2) {
                return $this->render('register', ['messages' => 'Passwords are not the same']);
            }

            $user = $userRepository->getUserByEmail($email);
            if ($user) {
                return $this->render('register', ['messages' => 'User exists']);
            }

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $username = $firstName;
            $fullName = $firstName . ' ' . $lastName;

            $userRepository->createUser($username, $email, $hashedPassword, $fullName);

            $url = "http://$_SERVER[HTTP_HOST]";
            header("Location: {$url}/login");
            return;
        }

        return $this->render("register");
    }

    public function logout() {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_unset();

        session_destroy();

        header("Location: /login");
        exit();
    }
}
