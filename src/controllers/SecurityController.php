<?php

require_once 'AppController.php';
require_once __DIR__.'/../repositories/UsersRepository.php';

/**
 * Kontroler odpowiedzialny za uwierzytelnianie użytkowników, rejestrację nowych kont oraz sesje.
 */
class SecurityController extends AppController {

    /**
     * Konfiguruje i uruchamia bezpieczną sesję z flagami chroniącymi przed XSS i CSRF.
     */
    private function startSecureSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'secure'   => isset($_SERVER['HTTPS']), // Włączone, jeśli używasz SSL
                'httponly' => true,                     // Blokuje dostęp skryptom JS do ciasteczka
                'samesite' => 'Strict'                  // Blokuje przesyłanie ciasteczka w żądaniach zewnętrznych
            ]);
            session_start();
        }
    }

    /**
     * Obsługuje proces logowania użytkownika do aplikacji.
     */
    public function login() {
        if (!$this->isPost()) {
            return $this->render('login');
        }

        $email = strtolower(trim($_POST["email"] ?? ''));
        $password = $_POST["password"] ?? '';

        if (empty($email) || empty($password)) {
            return $this->render('login', ['messages' => 'Wypełnij wszystkie pola']);
        }

        $userRepository = new UsersRepository();
        $user = $userRepository->getUserByEmail($email);

        if (!$user) {
            return $this->render('login', ['messages' => 'Nieprawidłowy adres e-mail lub hasło']);
        }

        if (password_verify($password, $user['password'])) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $this->startSecureSession();

            session_regenerate_id(true);

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

        return $this->render('login', ['messages' => 'Błędne hasło']);
    }

    /**
     * Obsługuje proces rejestracji nowego konta klienckiego.
     */
    public function register() {
        if (!$this->isPost()) {
            return $this->render("register");
        }

        $userRepository = new UsersRepository();

        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['confirmedPassword'] ?? '';
        $firstName = trim($_POST['name'] ?? '');
        $lastName = trim($_POST['surname'] ?? '');

        if (empty($email) || empty($password) || empty($password2) || empty($firstName) || empty($lastName)) {
            return $this->render('register', ['messages' => 'Wypełnij wszystkie wymagane pola']);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->render('register', ['messages' => 'Podany adres e-mail jest nieprawidłowy']);
        }

        if ($password !== $password2) {
            return $this->render('register', ['messages' => 'Podane hasła nie są identyczne']);
        }

        $user = $userRepository->getUserByEmail($email);
        if ($user) {
            return $this->render('register', ['messages' => 'Użytkownik o takim adresie e-mail już istnieje']);
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT); // Oskar ~ Użycie algorytmu haszującego
        $username = $firstName;
        $fullName = $firstName . ' ' . $lastName;

        $userRepository->createUser($username, $email, $hashedPassword, $fullName);

        header("Location: /login");
        exit();
    }

    /**
     * Kończy bieżącą sesję użytkownika i wylogowuje go z systemu.
     */
    public function logout() {
        $this->startSecureSession();

        $_SESSION = [];

        // Usunięcie ciasteczka sesyjnego z przeglądarki użytkownika
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();

        header("Location: /login");
        exit();
    }
}