<?php

/**
 * Główny kontroler abstrakcyjny, dostarczający metody pomocnicze dla wszystkich kontrolerów w aplikacji.
 */
abstract class AppController {

    /**
     * Sprawdza, czy bieżące żądanie protokołu HTTP zostało wysłane metodą GET.
     */
    protected function isGet(): bool {
        return $_SERVER["REQUEST_METHOD"] === 'GET';
    }

    /**
     * Sprawdza, czy bieżące żądanie protokołu HTTP zostało wysłane metodą POST.
     */
    protected function isPost(): bool {
        return $_SERVER["REQUEST_METHOD"] === 'POST';
    }

    /**
     * Weryfikuje, czy użytkownik jest zalogowany. W przypadku braku sesji przekierowuje do ekranu logowania.
     */
    protected function ensureAuthenticated(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }
    }

    /**
     * Odpowiada za dynamiczne generowanie widoku strony oraz przekazywanie do niego zmiennych z kontrolera.
     */
    protected function render(string $template = null, array $variables = []) {
        $templatePath = 'public/views/' . $template . '.php';

        if (!file_exists($templatePath)) {
            http_response_code(404);
            $templatePath = 'public/views/404.php';
        }

        extract($variables); // Oskar ~ Ekstrakcja zmiennych oraz bezpieczne renderowanie z użyciem bufora wyjściowego

        ob_start();
        include $templatePath;
        $output = ob_get_clean();

        echo $output;
    }
}