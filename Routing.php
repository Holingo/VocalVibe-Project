<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/DashboardController.php';
require_once 'src/controllers/UsersController.php';
require_once __DIR__.'/src/controllers/BookingController.php';

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
        "order-add" => [
          "controller" => "OrderController",
          "action" => "add"
        ],
    ];

    private static function getControllerInstance(string $className) {
        if (!array_key_exists($className, self::$instances)) {
            self::$instances[$className] = new $className();
        }
        return self::$instances[$className];
    }

    public static function run(string $path) {
        $path = trim($path, '/');

        $pathParts = explode('/', $path);
        $actionKey = $pathParts[0];
        $id = null;

        if (isset($pathParts[1]) && preg_match('/^[0-9]+$/', $pathParts[1])) {
            $id = (int)$pathParts[1];
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
