<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/DashboardController.php';
require_once 'src/controllers/UsersController.php';
require_once __DIR__.'/src/controllers/BookingController.php';
require_once __DIR__.'/src/controllers/OrderController.php';
require_once __DIR__.'/src/controllers/ManagerController.php';

class Routing {
    private static $instances = [];
    public static $routes = [
        "login" => [
            "controller" => "SecurityController",
            "action" => "login"
        ],
        "logout" => [
            "controller" => "SecurityController",
            "action" => "logout"
        ],
        "dashboard" => [
            "controller" => "DashboardController",
            "action" => "index"
        ],
        "" => [
            "controller" => "SecurityController",
            "action" => "login"
        ],
        "register" => [
            "controller" => "SecurityController",
            "action" => "register"
        ],
        "search" => [
            "controller" => "UsersController",
            "action" => "search"
        ],
        "delete-user" => [
            "controller" => "UsersController",
            "action" => "delete"
        ],
        "available-hours" => [
            "controller" => "BookingController",
            "action" => "getAvailableHours"
        ],
        "booking-create" => [
            "controller" => "BookingController",
            "action" => "create"
        ],
        "booking-cancel" => [
            "controller" => "BookingController",
            "action" => "cancel"
        ],
        "order/add" => [
            "controller" => "OrderController",
            "action" => "add"
        ],
        "order/remove" => [
            "controller" => "OrderController",
            "action" => "remove"
        ],
        "dashboard_manager" => [
            "controller" => "ManagerController",
            "action" => "index"
        ],
    ];

    private static function getControllerInstance(string $className) {
        if (!array_key_exists($className, self::$instances)) {
            self::$instances[$className] = new $className();
        }
        return self::$instances[$className];
    }

    public static function run(string $path) {
        // Logika pobierania ścieżki
        $path = trim($path, '/');

        // UWAGA: Twój router dzieli ścieżkę po ukośnikach:
        $pathParts = explode('/', $path);

        // Zbudujmy pełny actionKey dla tras wielopoziomowych (np. order/add)
        $actionKey = $pathParts[0];
        if (isset($pathParts[1]) && !preg_match('/^[0-9]+$/', $pathParts[1])) {
            $actionKey = $pathParts[0] . '/' . $pathParts[1];
        }

        $id = null;
        // Jeśli trzeci lub drugi element jest liczbą, traktujemy go jako ID (np. order/add/5)
        if (isset($pathParts[1]) && preg_match('/^[0-9]+$/', $pathParts[1])) {
            $id = (int)$pathParts[1];
        } elseif (isset($pathParts[2]) && preg_match('/^[0-9]+$/', $pathParts[2])) {
            $id = (int)$pathParts[2];
        }

        if (array_key_exists($actionKey, self::$routes)) {
            $controllerName = self::$routes[$actionKey]["controller"];
            $actionName = self::$routes[$actionKey]["action"];

            $controllerObj = self::getControllerInstance($controllerName);

            $controllerObj->$actionName($id);
        } else {
            include 'public/views/404.html';
        }
    }
}